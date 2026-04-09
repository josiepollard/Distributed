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
    $query = "SELECT m.*, 
                     u1.user_name AS sender_name, 
                     u2.user_name AS receiver_name 
              FROM messages m
              JOIN users u1 ON m.user_from = u1.user_id
              JOIN users u2 ON m.user_to = u2.user_id
              WHERE m.group_id IS NULL
                AND ((m.user_from=:user_from AND m.user_to=:user_to) 
                 OR (m.user_from=:user_to AND m.user_to=:user_from))
              ORDER BY m.date_sent ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_from", $user_from);
    $stmt->bindParam(":user_to", $user_to);
    $stmt->execute();

    return $stmt;
}

    public function getGroupMessages($group_id, $user_id) {
    $query = "SELECT m.*, 
                     u.user_name AS sender_name, 
                     g.group_name
              FROM messages m
              JOIN users u ON m.user_from = u.user_id
              JOIN group_chats g ON m.group_id = g.group_id
              JOIN group_chat_members gm ON gm.group_id = g.group_id
              WHERE m.group_id = :group_id
                AND gm.user_id = :user_id
              ORDER BY m.date_sent ASC";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":group_id", $group_id, PDO::PARAM_INT);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt;
}

    public function createGroup($group_name, $member_ids, $created_by) {
        $group_name = trim($group_name);
        if ($group_name === '') {
            return false;
        }

        $this->conn->beginTransaction();

        try {
            $query = "INSERT INTO group_chats (group_name, created_by) VALUES (:group_name, :created_by)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':group_name' => $group_name,
                ':created_by' => $created_by
            ]);

            $group_id = (int)$this->conn->lastInsertId();

            $member_ids[] = $created_by;
            $member_ids = array_unique(array_map('intval', $member_ids));

            $memberQuery = "INSERT INTO group_chat_members (group_id, user_id) VALUES (:group_id, :user_id)";
            $memberStmt = $this->conn->prepare($memberQuery);

            foreach ($member_ids as $member_id) {
                $memberStmt->execute([
                    ':group_id' => $group_id,
                    ':user_id' => $member_id
                ]);
            }

            $this->conn->commit();
            return $group_id;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
