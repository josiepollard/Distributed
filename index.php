<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include_once __DIR__ . '/database.php';

$database = new Database();
$db = $database->getConnection();
if (!$db) { exit; }

$query = "SELECT user_id, user_name FROM users WHERE user_id != :current_user ORDER BY user_name ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(":current_user", $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

$groupQuery = "SELECT g.group_id, g.group_name
               FROM group_chats g
               JOIN group_chat_members gm ON gm.group_id = g.group_id
               WHERE gm.user_id = :current_user
               ORDER BY g.group_name ASC";
$groupStmt = $db->prepare($groupQuery);
$groupStmt->bindParam(':current_user', $_SESSION['user_id'], PDO::PARAM_INT);
$groupStmt->execute();
$groups = $groupStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instant Messenger</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
<div id="layout">
    <div id="top-bar">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <a href="logout.php">Logout</a>
    </div>

    <div id="app">
        <div id="sidebar">
            <h3>Private Chats</h3>
            <select id="recipient">
                <option value="">-- Select a user --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo (int)$user['user_id']; ?>"><?php echo htmlspecialchars($user['user_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <h3>Group Chats</h3>
            <select id="group-select">
                <option value="">-- Select a group --</option>
                <?php foreach ($groups as $group): ?>
                    <option value="<?php echo (int)$group['group_id']; ?>"><?php echo htmlspecialchars($group['group_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <div class="create-group-box">
                <h4>Create Group</h4>
                <input type="text" id="group-name" placeholder="Group name">
                <div class="member-list">
                    <?php foreach ($users as $user): ?>
                        <label>
                            <input type="checkbox" class="group-member" value="<?php echo (int)$user['user_id']; ?>">
                            <?php echo htmlspecialchars($user['user_name']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="small-btn" onclick="createGroup()">Create Group</button>
            </div>
        </div>

        <div id="chat-container">
            <div id="chat-header">
                <div>
                    <span id="chat-username">Select a chat</span>
                    <div id="chat-subtitle">You can send messages, pictures and files.</div>
                </div>
                
            </div>

            <div id="message-container">Select a user or group to start chat...</div>

            <form id="message-form" enctype="multipart/form-data" onsubmit="return false;">
                <input type="text" id="message-input" placeholder="Type a message...">
                <label for="file-upload" id="file-upload-label">📎</label>
                <input type="file" id="file-upload" hidden>
                <span id="selected-file-name"></span>
                <button type="button" id="send-button" onclick="sendMessage()">➤</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let selectedUser = "";
let selectedGroup = "";
let chatType = "";

$("#recipient").change(function () {
    selectedUser = $(this).val();
    selectedGroup = "";
    chatType = selectedUser ? "user" : "";
    $("#group-select").val("");

    const name = $("#recipient option:selected").text();
    $("#chat-username").text(selectedUser ? name : "Select a chat");
    $("#chat-subtitle").text(selectedUser ? "Private chat" : "You can send messages, pictures and files.");

    if (selectedUser) {
        loadMessages();
    } else {
        $("#message-container").html("Select a user or group to start chat...");
    }
});

$("#group-select").change(function () {
    selectedGroup = $(this).val();
    selectedUser = "";
    chatType = selectedGroup ? "group" : "";
    $("#recipient").val("");

    const name = $("#group-select option:selected").text();
    $("#chat-username").text(selectedGroup ? name : "Select a chat");
    $("#chat-subtitle").text(selectedGroup ? "Group chat" : "You can send messages, pictures and files.");

    if (selectedGroup) {
        loadMessages();
    } else {
        $("#message-container").html("Select a user or group to start chat...");
    }
});

$("#history-limit").change(function () {
    if (chatType) {
        loadMessages();
    }
});

function sendMessage() {
    const message = $('#message-input').val();
    const file = $('#file-upload')[0].files[0];

    if (!chatType) {
        alert("Please select a chat first.");
        return;
    }

    if (message.trim() === "" && !file) {
        alert("Please enter a message or upload a file.");
        return;
    }

    const formData = new FormData();
    formData.append('message', message);
    formData.append('chat_type', chatType);

    if (chatType === 'group') {
        formData.append('group_id', selectedGroup);
    } else {
        formData.append('user_to', selectedUser);
    }

    if (file) {
        formData.append('file', file);
    }

    $.ajax({
        url: 'send_message.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function () {
            $('#message-input').val('');
            $('#file-upload').val('');
            $('#selected-file-name').text('');
            loadMessages();
        },
        error: function(xhr){
            alert("Send failed: " + xhr.responseText);
        }
    });
}

function loadMessages() {
    if (!chatType) return;

    $('#message-container').html("Loading...");

    const data = {
        chat_type: chatType,
        history_limit: $('#history-limit').val()
    };

    if (chatType === 'group') {
        data.group_id = selectedGroup;
    } else {
        data.user_to = selectedUser;
    }

    $.ajax({
        url: 'get_messages.php',
        type: 'GET',
        data: data,
        success: function (response) {
            $('#message-container').html(response);
            const el = document.getElementById('message-container');
            el.scrollTop = el.scrollHeight;
        }
    });
}

function createGroup() {
    const groupName = $('#group-name').val().trim();
    const members = [];

    $('.group-member:checked').each(function () {
        members.push($(this).val());
    });

    if (groupName === '') {
        alert('Please enter a group name');
        return;
    }

    if (members.length === 0) {
        alert('Please choose at least one member');
        return;
    }

    $.ajax({
        url: 'create_group.php',
        type: 'POST',
        data: {
            group_name: groupName,
            members: members
        },
        success: function () {
            alert('Group created. Reload the page to see it in the list.');
            $('#group-name').val('');
            $('.group-member').prop('checked', false);
        },
        error: function(xhr) {
            alert('Could not create group: ' + xhr.responseText);
        }
    });
}

$('#message-input').keypress(function(e) {
    if (e.which === 13) {
        sendMessage();
    }
});

$('#file-upload').change(function() {
    const fileName = this.files[0]?.name || '';
    $('#selected-file-name').text(fileName);
});

setInterval(() => {
    if (chatType) {
        loadMessages();
    }
}, 2000);
</script>
</body>
</html>
