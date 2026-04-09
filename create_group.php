<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die('Not authenticated');
}

include_once __DIR__ . '/database.php';
include_once __DIR__ . '/message.php';

$database = new Database();
$db = $database->getConnection();
$message = new Message($db);

$group_name = trim($_POST['group_name'] ?? '');
$members = $_POST['members'] ?? [];

if ($group_name === '') {
    die('Please enter a group name');
}

if (!is_array($members) || count($members) === 0) {
    die('Please select at least one member');
}

$newGroupId = $message->createGroup($group_name, $members, (int)$_SESSION['user_id']);

if ($newGroupId === false) {
    http_response_code(500);
    die('Could not create group');
}

echo 'OK';
?>
