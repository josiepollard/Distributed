<?php
session_start();

//ensure user logged in
if (!isset($_SESSION['user_id'])) {
    die('Not authenticated');
}

include_once __DIR__ . '/database.php';
include_once __DIR__ . '/message.php';

//db connection
$database = new Database();
$db = $database->getConnection();

$message = new Message($db);

//get group name, remove whitespace
$group_name = trim($_POST['group_name'] ?? '');

//get members
$members = $_POST['members'] ?? [];

//check not empty
if ($group_name === '') {
    die('Please enter a group name');
}

//ensure at least one member
if (!is_array($members) || count($members) === 0) {
    die('Please select at least one member');
}

$newGroupId = $message->createGroup($group_name, $members, (int)$_SESSION['user_id']);

//handle failure
if ($newGroupId === false) {
    http_response_code(500);
    die('Could not create group');
}

echo 'OK';
?>
