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

$message->user_from = $_SESSION['user_id'];
$message->user_to = $_POST['user_to'];
$message->message = $_POST['message'];

if ($message->sendMessage()) {
    echo "Message sent.";
} else {
    echo "Unable to send message.";
}
?>