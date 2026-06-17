<?php
include_once 'config/database.php';

$message = "";

// Auto-create uploads folder jika belum ada
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_POST['nim'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $course = $_POST['course'];
    
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
                    // URL file di Azure Storage (untuk studi kasus, ini dummy URL)
                    $file_url = "https://praktikumsubmitstorage.blob.core.windows.net/tugas-praktikum/" . $file_name;
                    
                    // Simpan ke database
                    try {
                        $database = new Database();
                        $conn = $database->getConnection();
                        
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
                            $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>❌ Gagal menyimpan ke database.</div>";
                        }
                    } catch (Exception $e) {
                        $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
                            ❌ Database error: " . $e->getMessage() . "
                        </div>";
                    }
                } else {
                    $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
                        ❌ Gagal upload file. Error code: " . $file['error'] . "<br>
                        <small>Upload path: " . $upload_path . "</small>
                    </div>";
                }
            } else {
                $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>❌ Ekstensi file tidak diperbolehkan. Gunakan PDF, DOCX, atau ZIP.</div>";
            }
        }
    } else {
        $error_msg = "";
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
            $error_msg = isset($error_messages[$error_code]) ? $error_messages[$error_code] : "Unknown error";
        }
        $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>
            ❌ Error upload: " . $error_msg . "
        </div>";
    }
}
?>