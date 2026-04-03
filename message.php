<?php
class Message {
    private $conn;
    private $table_name = "messages";

    public $user_from;
    public $user_to;
    public $message;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function sendMessage() {
        $query = "INSERT INTO " . $this->table_name . " SET user_from=:user_from, user_to=:user_to, message=:message, date_sent=NOW()";
        $stmt = $this->conn->prepare($query);

        $this->user_from = htmlspecialchars(strip_tags($this->user_from));
        $this->user_to = htmlspecialchars(strip_tags($this->user_to));
        $this->message = htmlspecialchars(strip_tags($this->message));

        $stmt->bindParam(":user_from", $this->user_from);
        $stmt->bindParam(":user_to", $this->user_to);
        $stmt->bindParam(":message", $this->message);

        return $stmt->execute();
    }

    public function getMessagesWithUsernames($user_from, $user_to) {
        $query = "SELECT m.*, u1.user_name AS sender_name, u2.user_name AS receiver_name 
                  FROM messages m
                  JOIN users u1 ON m.user_from = u1.user_id
                  JOIN users u2 ON m.user_to = u2.user_id
                  WHERE (m.user_from=:user_from AND m.user_to=:user_to) 
                     OR (m.user_from=:user_to AND m.user_to=:user_from)
                  ORDER BY m.date_sent ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_from", $user_from);
        $stmt->bindParam(":user_to", $user_to);
        $stmt->execute();

        return $stmt;
    }
}
?>