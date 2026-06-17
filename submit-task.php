<?php
include_once 'config/database.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_POST['nim'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $course = $_POST['course'];
    
    // Upload file ke Azure Storage
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = $_FILES['file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'docx', 'zip'];
        
        if (in_array($file_ext, $allowed_ext)) {
            $file_name = $nim . "_" . str_replace(' ', '_', strtolower($name)) . "_" . $file['name'];
            $upload_path = "uploads/" . $file_name;
            
            // Simpan file sementara
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // TODO: Upload ke Azure Blob Storage di sini
                $file_url = "https://praktikumsubmitstorage.blob.core.windows.net/tugas-praktikum/" . $file_name;
                
                // Simpan ke database
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
                    $message = "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>✅ Tugas berhasil dikumpulkan!</div>";
                } else {
                    $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>❌ Gagal menyimpan ke database.</div>";
                }
            } else {
                $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>❌ Gagal upload file.</div>";
            }
        } else {
            $message = "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;'>❌ Ekstensi file tidak diperbolehkan. Gunakan PDF, DOCX, atau ZIP.</div>";
        }
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
                <label for="file">File Tugas (PDF/DOCX/ZIP):</label>
                <input type="file" id="file" name="file" required>
            </div>
            
            <button type="submit">📤 Submit Tugas</button>
        </form>
        
        <a href="index.php" class="back">← Kembali ke Beranda</a>
    </div>
</body>
</html>