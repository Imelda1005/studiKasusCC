<?php
// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'config/database.php';

$message = "";

// Auto-create uploads folder jika belum ada
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
            ❌ Gagal membuat folder uploads. Cek permission server.
        </div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $nim = isset($_POST['nim']) ? trim($_POST['nim']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $class = isset($_POST['class']) ? trim($_POST['class']) : '';
    $course = isset($_POST['course']) ? trim($_POST['course']) : '';
    
    // Cek apakah ada file yang diupload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        
        // Cek ukuran file (max 20MB)
        $max_file_size = 20 * 1024 * 1024; // 20MB
        if ($file['size'] > $max_file_size) {
            $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
                ❌ File terlalu besar! Maksimal 20MB. Ukuran file kamu: " . round($file['size'] / 1024 / 1024, 2) . " MB
            </div>";
        } else {
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['pdf', 'docx', 'zip'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $file_name = $nim . "_" . str_replace(' ', '_', strtolower($name)) . "_" . time() . "." . $file_ext;
                $upload_path = $upload_dir . $file_name;
                
                // Simpan file ke folder uploads
                if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                    // URL file (dummy untuk studi kasus)
                    $file_url = "https://praktikumsubmitstorage.blob.core.windows.net/tugas-praktikum/" . $file_name;
                    
                    // Simpan ke database
                    try {
                        $database = new Database();
                        $conn = $database->getConnection();
                        
                        if ($conn) {
                            $query = "INSERT INTO submissions (nim, name, class, course, file_url, status) 
                                      VALUES (:nim, :name, :class, :course, :file_url, 'Submitted')";
                            $stmt = $conn->prepare($query);
                            
                            $stmt->bindParam(':nim', $nim);
                            $stmt->bindParam(':name', $name);
                            $stmt->bindParam(':class', $class);
                            $stmt->bindParam(':course', $course);
                            $stmt->bindParam(':file_url', $file_url);
                            
                            if ($stmt->execute()) {
                                $message = "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
                                    ✅ Tugas berhasil dikumpulkan!<br>
                                    <small>File: " . htmlspecialchars($file_name) . "</small>
                                </div>";
                            } else {
                                $error_info = $stmt->errorInfo();
                                $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
                                    ❌ Gagal menyimpan ke database.<br>
                                    <small>Error: " . htmlspecialchars($error_info[2]) . "</small>
                                </div>";
                            }
                        } else {
                            $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
                                ❌ Koneksi database gagal. Cek config/database.php
                            </div>";
                        }
                    } catch (Exception $e) {
                        $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
                            ❌ Database error: " . htmlspecialchars($e->getMessage()) . "
                        </div>";
                    }
                } else {
                    $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
                        ❌ Gagal upload file. Error code: " . $file['error'] . "<br>
                        <small>Upload path: " . htmlspecialchars($upload_path) . "</small>
                    </div>";
                }
            } else {
                $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
                    ❌ Ekstensi file tidak diperbolehkan. Gunakan PDF, DOCX, atau ZIP.
                </div>";
            }
        }
    } else {
        $error_msg = "Tidak ada file yang diupload.";
        if (isset($_FILES['file'])) {
            $error_code = $_FILES['file']['error'];
            $error_messages = [
                UPLOAD_ERR_INI_SIZE => "File terlalu besar (php.ini)",
                UPLOAD_ERR_FORM_SIZE => "File terlalu besar (form)",
                UPLOAD_ERR_PARTIAL => "File hanya ter-upload sebagian",
                UPLOAD_ERR_NO_FILE => "Tidak ada file yang diupload",
                UPLOAD_ERR_NO_TMP_DIR => "Folder temporary tidak ada",
                UPLOAD_ERR_CANT_WRITE => "Gagal menulis file",
                UPLOAD_ERR_EXTENSION => "Upload diblokir oleh extension"
            ];
            $error_msg = isset($error_messages[$error_code]) ? $error_messages[$error_code] : "Unknown error code: " . $error_code;
        }
        $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
            ❌ Error upload: " . htmlspecialchars($error_msg) . "
        </div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Tugas - PraktikumSubmit</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #0078d4; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input[type="text"], input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #0078d4; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #005a9e; }
        .back { display: inline-block; margin-top: 20px; color: #0078d4; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📤 Pengumpulan Tugas</h1>
        
        <?php echo $message; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nim">NIM:</label>
                <input type="text" id="nim" name="nim" required>
            </div>
            
            <div class="form-group">
                <label for="name">Nama:</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="class">Kelas:</label>
                <input type="text" id="class" name="class" required>
            </div>
            
            <div class="form-group">
                <label for="course">Mata Kuliah:</label>
                <input type="text" id="course" name="course" required>
            </div>
            
            <div class="form-group">
                <label for="file">File Tugas (PDF/DOCX/ZIP, max 20MB):</label>
                <input type="file" id="file" name="file" accept=".pdf,.docx,.zip" required>
            </div>
            
            <button type="submit">📤 Submit Tugas</button>
        </form>
        
        <a href="index.php" class="back">← Kembali ke Beranda</a>
    </div>
</body>
</html>