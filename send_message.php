<?php
session_start();
include_once __DIR__ . '/database.php';
include_once 'encryption.php';

//upload limits
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');

//db connection
$database = new Database();
$db = $database->getConnection();

//if db fails, stop
if (!$db) {
    die("Database connection failed");
}

//ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Not authenticated");
}

$user_from = (int)$_SESSION['user_id']; //current user
$chat_type = $_POST['chat_type'] ?? 'user'; //user or group chat?

$message = trim($_POST['message'] ?? ''); //trim whitespace

//if not empty, encrypt
if ($message !== '') {
    $message = encryptMessage($message);
}

//variables
$file_path = null;
$user_to = null;
$group_id = null;

//determine chat type
if ($chat_type === 'group') {
    //ensure group id exist
    if (!isset($_POST['group_id'])) {
        die("Missing group");
    }
    $group_id = (int)$_POST['group_id'];
} else {
    //ensure user exists
    if (!isset($_POST['user_to'])) {
        die("Missing recipient");
    }
    $user_to = (int)$_POST['user_to'];
}

// message must have either text or a file 
if ($message === '' && (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0)) {
    die("No message or file sent");
}

//file upload handle
if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    $uploadDir = __DIR__ . "/uploads/";

    //if uploads folder is missing, make it
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    //file details
    $originalName = $_FILES['file']['name'];
    $tmpName = $_FILES['file']['tmp_name'];

    //file extension
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg','jpeg','png','gif','webp','pdf','txt','zip','doc','docx']; //allowed types

    //reject invalid file type
    if (!in_array($ext, $allowed_ext)) {
        die("File type not allowed");
    }

    //remove unsafe chars
    $safeName = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $originalName);

    //timestamp to make name unique
    $fileName = time() . "_" . $safeName;

    $targetFile = $uploadDir . $fileName;
    if (move_uploaded_file($tmpName, $targetFile)) {
        $file_path = 'uploads/' . $fileName;
    } else {
        die("File upload failed");
    }
}

//save msg to db
if ($chat_type === 'group') {

    //check user is part of group
    $check = $db->prepare("SELECT COUNT(*) FROM group_chat_members WHERE group_id = :group_id AND user_id = :user_id");
    $check->execute([
        ':group_id' => $group_id,
        ':user_id' => $user_from
    ]);

    if ($check->fetchColumn() == 0) {
        die("You are not in this group");
    }

    //insert group message to db
    $query = "INSERT INTO messages (user_from, user_to, group_id, message, file_path, date_sent)
              VALUES (:from, NULL, :group_id, :message, :file_path, NOW())";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':from' => $user_from,
        ':group_id' => $group_id,
        ':message' => $message,
        ':file_path' => $file_path
    ]);
} else {
    //insert private msg
    $query = "INSERT INTO messages (user_from, user_to, message, file_path, date_sent)
              VALUES (:from, :to, :message, :file_path, NOW())";

    $stmt = $db->prepare($query);
    $stmt->execute([
        ':from' => $user_from,
        ':to' => $user_to,
        ':message' => $message,
        ':file_path' => $file_path
    ]);
}

//success msg
echo "OK";
?>