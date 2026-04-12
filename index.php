<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include_once __DIR__ . '/database.php';

$database = new Database();
$db = $database->getConnection();
if (isset($_GET['fetch']) && $_GET['fetch'] === 'recent') {

    $recentQuery = "
        (
            SELECT 
                u.user_id AS id,
                u.user_name AS name,
                u.profile_pic,
                'user' AS type,
                MAX(m.date_sent) AS last_msg
            FROM messages m
            JOIN users u ON (
                (m.user_from = :uid AND u.user_id = m.user_to)
                OR
                (m.user_to = :uid AND u.user_id = m.user_from)
            )
            WHERE m.group_id IS NULL
            GROUP BY u.user_id, u.user_name, u.profile_pic
        )

        UNION

        (
            SELECT 
                g.group_id AS id,
                g.group_name AS name,
                NULL AS profile_pic,
                'group' AS type,
                MAX(m.date_sent) AS last_msg
            FROM messages m
            JOIN group_chats g ON m.group_id = g.group_id
            JOIN group_chat_members gm ON gm.group_id = g.group_id
            WHERE gm.user_id = :uid
            GROUP BY g.group_id, g.group_name
        )

        ORDER BY last_msg DESC
        LIMIT 5
    ";

    $stmt = $db->prepare($recentQuery);
    $stmt->execute([':uid' => $_SESSION['user_id']]);
    $recentChats = $stmt->fetchAll();

    if (!$recentChats) {
        echo "<div class='empty-recent'>No recent chats</div>";
        exit;
    }

    echo "<ul class='recent-list'>";
    foreach ($recentChats as $chat) {

        echo "<li onclick=\"selectRecent('{$chat['type']}', {$chat['id']}, '" . htmlspecialchars($chat['name']) . "')\">";

        if ($chat['type'] === 'user') {
            $pic = !empty($chat['profile_pic']) ? $chat['profile_pic'] : 'uploads/default.png';
            echo "<img src='$pic' class='recent-avatar'>";
        } else {
            echo "<div class='group-icon'>🙂</div>";
        }

        echo htmlspecialchars($chat['name']);
        echo "</li>";
    }
    echo "</ul>";

    exit; // IMPORTANT
}
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

// RECENT CHATS (last 5 private conversations)
$recentQuery = "
    (
        -- PRIVATE CHATS
        SELECT 
            u.user_id AS id,
            u.user_name AS name,
            u.profile_pic,
            'user' AS type,
            MAX(m.date_sent) AS last_msg
        FROM messages m
        JOIN users u ON (
            (m.user_from = :uid AND u.user_id = m.user_to)
            OR
            (m.user_to = :uid AND u.user_id = m.user_from)
        )
        WHERE m.group_id IS NULL
        GROUP BY u.user_id, u.user_name, u.profile_pic
    )

    UNION

    (
        -- GROUP CHATS
        SELECT 
            g.group_id AS id,
            g.group_name AS name,
            NULL AS profile_pic,
            'group' AS type,
            MAX(m.date_sent) AS last_msg
        FROM messages m
        JOIN group_chats g ON m.group_id = g.group_id
        JOIN group_chat_members gm ON gm.group_id = g.group_id
        WHERE gm.user_id = :uid
        GROUP BY g.group_id, g.group_name
    )

    ORDER BY last_msg DESC
    LIMIT 5
";
$recentStmt = $db->prepare($recentQuery);
$recentStmt->execute([':uid' => $_SESSION['user_id']]);
$recentChats = $recentStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Instant Messenger</title>
    <link rel="stylesheet" href="styles/styles.css">

<script>
// Apply saved theme BEFORE page renders
if (localStorage.getItem('theme') === 'dark') {
    document.documentElement.classList.add('dark-mode');
}
</script>

</head>
<body>
<div id="layout">
    <div id="top-bar">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
        <div>
            <a href="user_settings.php" class="logout-btn">Settings</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        
        </div>
        
    </div>

    <div id="app">
        <div id="sidebar">
            <h3>Recent Chats</h3>

            <div id="recent-chats"></div>


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
            <button type="button" class="small-btn" onclick="openGroupModal()">Create New Group</button>


            
        </div>

        <div id="chat-container">
            <div id="chat-header">
                <div>
                    <span id="chat-username">Select a chat</span>
                    <div id="chat-subtitle">You can send messages, pictures and files.</div>
                </div>
                
            </div>

            <div id="message-container">Select a user or group to start chat...</div>

            <form id="message-form" enctype="multipart/form-data" onsubmit="return false;" style="display: none;">
    <input type="text" id="message-input" placeholder="Type a message...">
    <label for="file-upload" id="file-upload-label">📎</label>
    <input type="file" id="file-upload" hidden>
    <span id="selected-file-name"></span>
    <button type="button" id="send-button" onclick="sendMessage()">➤</button>
</form>
        </div>
    </div>
</div>


<div id="group-modal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Create Group</h3>
            <button type="button" class="close-modal-btn" onclick="closeGroupModal()">×</button>
        </div>

        <div class="modal-body">
            <input type="text" id="group-name" placeholder="Group name">

            <div class="member-list modal-member-list">
                <?php foreach ($users as $user): ?>
                    <label>
                        <input type="checkbox" class="group-member" value="<?php echo (int)$user['user_id']; ?>">
                        <?php echo htmlspecialchars($user['user_name']); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="modal-actions">
                <button type="button" class="small-btn" onclick="createGroup()">Create</button>
                <button type="button" class="cancel-btn" onclick="closeGroupModal()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let selectedUser = "";
let selectedGroup = "";
let chatType = "";
let lastMessagesHtml = "";

$("#recipient").change(function () {
    selectedUser = $(this).val();
    selectedGroup = "";
    chatType = selectedUser ? "user" : "";
    $("#group-select").val("");
    lastMessagesHtml = "";

    const name = $("#recipient option:selected").text();
    $("#chat-username").text(selectedUser ? name : "Select a chat");
    $("#chat-subtitle").text(selectedUser ? "Private chat" : "You can send messages, pictures and files.");

   if (selectedUser) {
    $('#message-form').show();
    loadMessages();
} else {
    $('#message-container').html("Select a user or group to start chat...");
    $('#message-form').hide();
}
});

$("#group-select").change(function () {
    selectedGroup = $(this).val();
    selectedUser = "";
    chatType = selectedGroup ? "group" : "";
    $("#recipient").val("");
    lastMessagesHtml = "";

    const name = $("#group-select option:selected").text();
    $("#chat-username").text(selectedGroup ? name : "Select a chat");
    $("#chat-subtitle").text(selectedGroup ? "Group chat" : "You can send messages, pictures and files.");

    if (selectedGroup) {
    $('#message-form').show();
    loadMessages();
} else {
    $('#message-container').html("Select a user or group to start chat...");
    $('#message-form').hide();
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
    loadRecentChats(); // ✅ THIS is the key line
},
        error: function(xhr){
            alert("Send failed: " + xhr.responseText);
        }
    });
}

function loadMessages() {
    if (!chatType) return;

    const data = {
        chat_type: chatType
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
            if (response !== lastMessagesHtml) {
                $('#message-container').html(response);
                lastMessagesHtml = response;

                const el = document.getElementById('message-container');
                el.scrollTop = el.scrollHeight;
            }
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
    alert('Group created');

    // clear form
    $('#group-name').val('');
    $('.group-member').prop('checked', false);
    closeGroupModal();

    // reload group dropdown + recent chats
    location.reload();
},
        error: function(xhr) {
            alert('Could not create group: ' + xhr.responseText);
        }
    });
}

function selectRecent(type, id, name) {
    lastMessagesHtml = "";

    if (type === "user") {
        selectedUser = id;
        selectedGroup = "";
        chatType = "user";

        $("#recipient").val(id);
        $("#group-select").val("");

        $("#chat-subtitle").text("Private chat");
    } else {
        selectedGroup = id;
        selectedUser = "";
        chatType = "group";

        $("#group-select").val(id);
        $("#recipient").val("");

        $("#chat-subtitle").text("Group chat");
    }

    $("#chat-username").text(name);
    $('#message-form').show();
    loadMessages();
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

function openGroupModal() {
    $('#group-modal').fadeIn(150);
}

function closeGroupModal() {
    $('#group-modal').fadeOut(150);
}


function loadRecentChats() {
    $.ajax({
        url: 'index.php?fetch=recent',
        success: function (html) {
            $('#recent-chats').html(html);
        }
    });
}

$(document).ready(function () {
    loadRecentChats();
});

setInterval(() => {
    if (chatType) {
        loadMessages();
    }
}, 2000);
</script>
</body>
</html>
