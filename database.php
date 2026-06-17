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
            // PDO options dengan SSL
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                // SSL Configuration untuk Azure MySQL
                PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];
            
            // DSN connection string
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name;
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            // Tampilkan error detail
            die("Connection error: " . $exception->getMessage() . "<br><br>
                 <b>Debug Info:</b><br>
                 Host: " . $this->host . "<br>
                 Database: " . $this->db_name . "<br>
                 Username: " . $this->username);
            return null;
        }

        return $this->conn;
    }
}
?>