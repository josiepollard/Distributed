<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

include_once 'database.php';
include_once 'message.php';
include_once 'encryption.php';

//db connection
$database = new Database();
$db = $database->getConnection();

$message = new Message($db);
$user_id = (int)$_SESSION['user_id']; //current user id
$chat_type = $_GET['chat_type'] ?? 'user'; //group or private chat?

//fetch messages
if ($chat_type === 'group') {

    //get group id
    $group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

    //get group msgs
    $stmt = $message->getGroupMessages($group_id, $user_id);
} else {

    //get user id
    $user_to = isset($_GET['user_to']) ? (int)$_GET['user_to'] : 0;

    //get msgs
    $stmt = $message->getMessagesWithUsernames($user_id, $user_to);
}

//count number of msgs
$num = $stmt->rowCount();

//if no msgs, show message
if ($num === 0) {
    echo "<div class='empty-chat'>No messages yet.</div>";
    exit;
}

//loop through msgs
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    //check if msg was sent by current user
    $isSent = ((int)$row['user_from'] === $user_id);

    //assign css depending on if it was sent or recived
    $class = $isSent ? 'sent' : 'received';

    //date format
    $date = !empty($row['date_sent']) ? date('d M Y H:i', strtotime($row['date_sent'])) : '';

    //default profile photo if no user photo added
    $profilePic = !empty($row['sender_pic']) 
        ? htmlspecialchars($row['sender_pic']) 
        : 'uploads/default.png';

    //if user has photo
    if (!empty($row['sender_pic']) && file_exists($row['sender_pic'])) {
        $profilePic = $row['sender_pic'];
    } else {
        $profilePic = 'uploads/default.png';
    }
        //build message bubble html
        echo "<div class='message-row $class'>";

        // Profile picture
        echo "<img src='$profilePic' class='message-avatar'>";

        // Message bubble
        echo "<div class='message $class'>";

    //if a group chat, show senders name
    if ($chat_type === 'group') {
        echo "<div class='message-name'>" . htmlspecialchars($row['sender_name']) . "</div>";
    }

    //decrypt message and display it
    if (!empty($row['message'])) {
        $decrypted = decryptMessage($row['message']);
        echo "<div>" . nl2br(htmlspecialchars($decrypted)) . "</div>";
    }

    //display file
    if (!empty($row['file_path'])) {

        //safe file path
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
        //show timestamp
        echo "<div class='message-date'>" . htmlspecialchars($date) . "</div>";
    }

    echo "</div>"; 
echo "</div>"; 
}
?>
