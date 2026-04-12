<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

include_once 'database.php';
include_once 'message.php';
include_once 'encryption.php';

$database = new Database();
$db = $database->getConnection();
$message = new Message($db);

$user_id = (int)$_SESSION['user_id'];
$chat_type = $_GET['chat_type'] ?? 'user';


if ($chat_type === 'group') {
    $group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;
   $stmt = $message->getGroupMessages($group_id, $user_id);
} else {
    $user_to = isset($_GET['user_to']) ? (int)$_GET['user_to'] : 0;
    $stmt = $message->getMessagesWithUsernames($user_id, $user_to);
}

$num = $stmt->rowCount();

if ($num === 0) {
    echo "<div class='empty-chat'>No messages yet.</div>";
    exit;
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $isSent = ((int)$row['user_from'] === $user_id);
    $class = $isSent ? 'sent' : 'received';
    $date = !empty($row['date_sent']) ? date('d M Y H:i', strtotime($row['date_sent'])) : '';

   $profilePic = !empty($row['sender_pic']) 
    ? htmlspecialchars($row['sender_pic']) 
    : 'uploads/default.png';

    if (!empty($row['sender_pic']) && file_exists($row['sender_pic'])) {
        $profilePic = $row['sender_pic'];
    } else {
        $profilePic = 'uploads/default.png';
    }

        echo "<div class='message-row $class'>";

        // Profile picture
        echo "<img src='$profilePic' class='message-avatar'>";

        // Message bubble
        echo "<div class='message $class'>";

    if ($chat_type === 'group') {
        echo "<div class='message-name'>" . htmlspecialchars($row['sender_name']) . "</div>";
    }

    if (!empty($row['message'])) {
        $decrypted = decryptMessage($row['message']);

echo "<div>" . nl2br(htmlspecialchars($decrypted)) . "</div>";
    }

    if (!empty($row['file_path'])) {
        $file = 'uploads/' . basename($row['file_path']);
        $safeFile = htmlspecialchars($file);
        $fileName = htmlspecialchars(basename($row['file_path']));

        if (preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file)) {
            echo "<div class='message-file'><a href='$safeFile' target='_blank'><img src='$safeFile' alt='sent image' class='chat-image'></a></div>";
        } else {
            echo "<div class='message-file'><a href='$safeFile' target='_blank' download>$fileName</a></div>";
        }
    }

    if ($date !== '') {
        echo "<div class='message-date'>" . htmlspecialchars($date) . "</div>";
    }

    echo "</div>"; // message
echo "</div>"; // row
}
?>
