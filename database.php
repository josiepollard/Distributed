<?php
class Database {

//db details
    private string $host = "localhost";     
    private string $db_name = "distributed";
    private string $username = "root"; 
    private string $password = "";      

    public ?PDO $conn = null;

    //create connection
    public function getConnection(): ?PDO {
        try {
            //data source name
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";

            //pdo connection
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            //return connection
            return $this->conn;

        } catch (PDOException $e) {
            // handle connection error
            http_response_code(500);
            echo "DB error: " . $e->getMessage();  // show error
            return null; //if connection fails
        }
    }
}

