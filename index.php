<?php
/**
 * index.php
 * ---------
 * Main chat page
 * Only logged-in users can access
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include_once __DIR__ . '/database.php';

$database = new Database();
$db = $database->getConnection();
if (!$db) { exit; }

// Load list of users except current user (so you can select who to chat with)
$query = "SELECT user_id, user_name FROM users WHERE user_id != :current_user";
$stmt = $db->prepare($query);
$stmt->bindParam(":current_user", $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();

$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instant Messenger</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<p>
    Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
    <a href="logout.php">Logout</a>
</p>

<!-- Select recipient -->
<label for="recipient">Chat with:</label>
<select id="recipient">
    <option value="">-- Select a user --</option>
    <?php foreach ($users as $user): ?>
        <option value="<?php echo (int)$user['user_id']; ?>">
            <?php echo htmlspecialchars($user['user_name']); ?>
        </option>
    <?php endforeach; ?>
</select>

<div id="chat-container">
    <div id="message-container">Select a user to start chat...</div>

    <form id="message-form" enctype="multipart/form-data" onsubmit="return false;">
        <input type="text" id="message-input" placeholder="Type your message...">

        <label for="file-upload" id="file-upload-label">📎 Upload</label>
        <input type="file" id="file-upload" style="display:none;">

        <button type="button" id="send-button" onclick="sendMessage()">Send</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    /**
     * selectedUser = user_id of the recipient chosen from dropdown
     */
    let selectedUser = "";

    // When user selects a recipient, load messages
    $("#recipient").change(function () {
        selectedUser = $(this).val();

        if (selectedUser) {
            loadMessages();
        } else {
            $("#message-container").html("Select a user to start chat...");
        }
    });

    function sendMessage() {
        const message = $('#message-input').val();
        const file = $('#file-upload')[0].files[0];

        // Must select recipient
        if (!selectedUser) {
            alert("Please select a recipient first.");
            return;
        }

        // Must type message or upload file
        if (message.trim() === "" && !file) {
            alert("Please enter a message or upload a file!");
            return;
        }

        const formData = new FormData();
        formData.append('message', message);
        formData.append('user_to', selectedUser);

        // Attach file if chosen
        if (file) formData.append('file', file);

        $.ajax({
            url: 'send_message.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function () {
                $('#message-input').val('');
                $('#file-upload').val('');
                loadMessages();
            },
            error: function(xhr){
                alert("Send failed: " + xhr.responseText);
            }
        });
    }

    function loadMessages() {
        if (!selectedUser) return;

        $.ajax({
            url: 'get_messages.php',
            type: 'GET',
            data: { user_to: selectedUser },
            success: function (response) {
                $('#message-container').html(response);

                // Auto-scroll chat
                const el = document.getElementById('message-container');
                el.scrollTop = el.scrollHeight;
            }
        });
    }

    // Poll every 1 second (simple demo technique)
    setInterval(loadMessages, 1000);
</script>

</body>
</html>