<?php
session_start();
include_once __DIR__ . '/database.php';

ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("Database connection failed");
}

if (!isset($_SESSION['user_id'])) {
    die("Not authenticated");
}

$user_from = (int)$_SESSION['user_id'];
$chat_type = $_POST['chat_type'] ?? 'user';
$message = trim($_POST['message'] ?? '');
$file_path = null;
$user_to = null;
$group_id = null;

if ($chat_type === 'group') {
    if (!isset($_POST['group_id'])) {
        die("Missing group");
    }
    $group_id = (int)$_POST['group_id'];
} else {
    if (!isset($_POST['user_to'])) {
        die("Missing recipient");
    }
    $user_to = (int)$_POST['user_to'];
}

if ($message === '' && (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0)) {
    die("No message or file sent");
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
    $uploadDir = __DIR__ . "/uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = $_FILES['file']['name'];
    $tmpName = $_FILES['file']['tmp_name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowed_ext = ['jpg','jpeg','png','gif','webp','pdf','txt','zip','doc','docx'];
    if (!in_array($ext, $allowed_ext)) {
        die("File type not allowed");
    }

    $safeName = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $originalName);
    $fileName = time() . "_" . $safeName;
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($tmpName, $targetFile)) {
        $file_path = 'uploads/' . $fileName;
    } else {
        die("File upload failed");
    }
}

if ($chat_type === 'group') {
    $check = $db->prepare("SELECT COUNT(*) FROM group_chat_members WHERE group_id = :group_id AND user_id = :user_id");
    $check->execute([
        ':group_id' => $group_id,
        ':user_id' => $user_from
    ]);

    if ($check->fetchColumn() == 0) {
        die("You are not in this group");
    }

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

echo "OK";
?>
