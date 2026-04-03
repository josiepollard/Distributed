<?php
class Database {
    private string $host = "172.16.11.22:3306";     // Plesk: use the host shown in Databases
    private string $db_name = "seac1_23_message";
    private string $username = "seac1_23_message"; // Plesk DB user
    private string $password = "data-mining-02";      // Plesk DB password

    public ?PDO $conn = null;

    public function getConnection(): ?PDO {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return $this->conn;
        } catch (PDOException $e) {
            // In production, avoid echoing raw errors; log them instead.
            http_response_code(500);
            echo "DB error: " . $e->getMessage();  // TEMP: show actual reason
            return null;
        }
    }
}

