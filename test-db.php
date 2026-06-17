<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

if ($conn) {
    echo "✅ Koneksi database berhasil!<br>";
    
    // Cek apakah database praktikum_db ada
    try {
        $stmt = $conn->query("SHOW DATABASES LIKE 'praktikum_db'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Database praktikum_db ditemukan<br>";
            
            // Cek tabel submissions
            $stmt = $conn->query("SHOW TABLES LIKE 'submissions'");
            if ($stmt->rowCount() > 0) {
                echo "✅ Tabel submissions ditemukan<br>";
            } else {
                echo "❌ Tabel submissions tidak ditemukan<br>";
                echo "Buat tabel dengan query:<br>";
                echo "<pre>CREATE TABLE submissions (...);</pre>";
            }
        } else {
            echo "❌ Database praktikum_db tidak ditemukan<br>";
            echo "Jalankan: CREATE DATABASE praktikum_db;";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
} else {
    echo "❌ Koneksi database gagal!<br>";
    echo "Periksa:<br>";
    echo "- Host: mysql-praktikumsubmit.mysql.database.azure.com<br>";
    echo "- Username: dbadmin<br>";
    echo "- Password: Imel131005<br>";
    echo "- Firewall settings di Azure Portal";
}
?>