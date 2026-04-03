<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

include_once 'database.php';
include_once 'message.php';

$database = new Database();
$db = $database->getConnection();
$message = new Message($db);

$user_id = $_SESSION['user_id'];
$user_to = isset($_GET['user_to']) ? $_GET['user_to'] : 0;

$stmt = $message->getMessagesWithUsernames($user_id, $user_to);
$num = $stmt->rowCount();

if ($num > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<div><strong>" . htmlspecialchars($row['sender_name']) . ":</strong> " . htmlspecialchars($row['message']);
        echo " <span style='font-size: 0.8em; color: gray;'>(" . $row['date_sent'] . ")</span></div>";
    }
} else {
    echo "<div>No messages yet.</div>";
}
?>