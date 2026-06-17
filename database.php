<?php
class Database {
    private $host = "mysql-praktikumsubmit.mysql.database.azure.com";
    private $db_name = "praktikum_db";
    private $username = "dbadmin";
    private $password = "Imel131005";
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Tambah SSL dan timeout options
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                PDO::ATTR_TIMEOUT => 10
            ];
            
            // Coba dengan username format "dbadmin@mysql-praktikumsubmit"
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            // Tampilkan error detail (untuk debugging)
            die("Connection error: " . $exception->getMessage() . "<br><br>
                 <b>Debug Info:</b><br>
                 Host: " . $this->host . "<br>
                 Database: " . $this->db_name . "<br>
                 Username: " . $this->username . "<br>
                 Error Code: " . $exception->getCode());
            return null;
        }

        return $this->conn;
    }
}
?>