<?php
session_start();

// Ensure That user is logged in, if not, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include_once __DIR__ . '/database.php';

// Connect to DB
$database = new Database();
$db = $database->getConnection();

// AJAX Endpoint, fetch recent chats
if (isset($_GET['fetch']) && $_GET['fetch'] === 'recent') {

    // Get the 5 most recent chats (includes group and private). 
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

    // execute with current user ID
    $stmt = $db->prepare($recentQuery);
    $stmt->execute([':uid' => $_SESSION['user_id']]);
    $recentChats = $stmt->fetchAll();

    // If no existing chats, display message
    if (!$recentChats) {
        echo "<div class='empty-recent'>No recent chats</div>";
        exit;
    }

    echo "<ul class='recent-list'>";

    $first = true; //Auto open most recent chat


foreach ($recentChats as $chat) {

    // build chat items
    echo "<li 
        class='recent-item " . ($first ? "auto-open" : "") . "'
        data-type='{$chat['type']}'
        data-id='{$chat['id']}'
        data-name=\"" . htmlspecialchars($chat['name']) . "\"
        onclick=\"selectRecent('{$chat['type']}', {$chat['id']}, '" . htmlspecialchars($chat['name']) . "')\">";

    //Show profile photo or group chat icon
    if ($chat['type'] === 'user') {
        $pic = !empty($chat['profile_pic']) ? $chat['profile_pic'] : 'uploads/default.png';
        echo "<img src='$pic' class='recent-avatar'>";
    } else {
        echo "<div class='group-icon'>🙂</div>";
    }

    echo htmlspecialchars($chat['name']);
    echo "</li>";

    $first = false; // only first chat auto open
}

echo "</ul>";

    exit; // IMPORTANT
}

// If Database fail, stop
if (!$db) { exit; }

// Load all users (to show in dropdown)
$query = "SELECT user_id, user_name FROM users WHERE user_id != :current_user ORDER BY user_name ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(":current_user", $_SESSION['user_id'], PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

// Load groups that current user is part of
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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


<script>
    // track current chat
    let selectedUser = ""; 
    let selectedGroup = "";
    let chatType = "";
    let lastMessagesHtml = "";


    //Private chat
    $("#recipient").change(function () {
        selectedUser = $(this).val(); //Get selected users ID
        selectedGroup = ""; //Clears group selection
        chatType = selectedUser ? "user" : ""; //Sets the chat type
        $("#group-select").val(""); //Reset group dropdown
        lastMessagesHtml = ""; //reset cashe

        //Update header text
        const name = $("#recipient option:selected").text();
        $("#chat-username").text(selectedUser ? name : "Select a chat");
        $("#chat-subtitle").text(selectedUser ? "Private chat" : "You can send messages, pictures and files.");

        //Show chat
        if (selectedUser) {
            $('#message-form').show(); //show the input box
            loadMessages();
        } else {
            //Message placeholder
            $('#message-container').html("Select a user or group to start chat...");
            $('#message-form').hide(); //hide input box
        }
    });


    //Group chat
    $("#group-select").change(function () {
        selectedGroup = $(this).val(); //Group ID
        selectedUser = ""; //clear user selection
        chatType = selectedGroup ? "group" : "";
        $("#recipient").val(""); //reset user dropdown
        lastMessagesHtml = "";

        // update UI
        const name = $("#group-select option:selected").text();
        $("#chat-username").text(selectedGroup ? name : "Select a chat");
        $("#chat-subtitle").text(selectedGroup ? "Group chat" : "You can send messages, pictures and files.");

        // show chat
        if (selectedGroup) {
        $('#message-form').show();
        loadMessages();
        } else {
            //placeholder
            $('#message-container').html("Select a user or group to start chat...");
            $('#message-form').hide();
        }
    });


    // Send Message
    function sendMessage() {
        const message = $('#message-input').val(); //text based message
        const file = $('#file-upload')[0].files[0]; //File attachment
        const formData = new FormData();

        //If no chat has been selected
        if (!chatType) {
            alert("Please select a chat first.");
            return;
        }

        // No text AND no file attachment
        if (message.trim() === "" && !file) {
            alert("Please enter a message or upload a file.");
            return;
        }

        // formData for AJAX
        formData.append('message', message);
        formData.append('chat_type', chatType);

        // private chat or group?
        if (chatType === 'group') {
            formData.append('group_id', selectedGroup);
        } else {
            formData.append('user_to', selectedUser);
        }

        // Attach file if there is one
        if (file) {
            formData.append('file', file);
        }

        // backend
        $.ajax({
            url: 'send_message.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,

            success: function () {
                //clear the inputs
                $('#message-input').val('');
                $('#file-upload').val('');
                $('#selected-file-name').text('');

                //Refresh UI
                loadMessages();
                loadRecentChats(); 
            },

            error: function(xhr){
                alert("Send failed: " + xhr.responseText);
            }
        });
    }

    //Load Messages
    function loadMessages() {
        if (!chatType) return; //Nothing selected

        const data = {
            chat_type: chatType
        };

        // group chat or private?
        if (chatType === 'group') {
            data.group_id = selectedGroup;
        } else {
            data.user_to = selectedUser;
        }

        //fetch messages
        $.ajax({
            url: 'get_messages.php',
            type: 'GET',
            data: data,
            success: function (response) {
                //update if changed. this prevents chat flickering
                if (response !== lastMessagesHtml) {
                    $('#message-container').html(response);
                    lastMessagesHtml = response;

                    //Auto scroll to bottom of chat
                    const el = document.getElementById('message-container');
                    el.scrollTop = el.scrollHeight;
                }
            }
        });
    }

    //Creat a new group
    function createGroup() {
        const groupName = $('#group-name').val().trim();
        const members = [];

        //Selected users
        $('.group-member:checked').each(function () {
            members.push($(this).val());
        });

        //Check group name is not empty
        if (groupName === '') {
            alert('Please enter a group name');
            return;
        }

        //ensure at least 1 member is selected
        if (members.length === 0) {
            alert('Please choose at least one member');
            return;
        }

        //backend
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

                // reload page to refresh list
                location.reload();
            },

            error: function(xhr) {
                alert('Could not create group: ' + xhr.responseText);
            }
        });
    }

    //Select recent chat
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

        //Update UI and load 
        $("#chat-username").text(name);
        $('#message-form').show();
        loadMessages();
    }

    //Send message when enter key pressed
    $('#message-input').keypress(function(e) {
        if (e.which === 13) {
            sendMessage();
        }
    });


    //Display file name
    $('#file-upload').change(function() {
        const fileName = this.files[0]?.name || '';
        $('#selected-file-name').text(fileName);
    });


    //Open 'create group' modal
    function openGroupModal() {
        $('#group-modal').fadeIn(150);
    }

    //close the 'create group' modal
    function closeGroupModal() {
        $('#group-modal').fadeOut(150);
    }


    //load recent chats in sidebar
    function loadRecentChats() {
        $.ajax({
            url: 'index.php?fetch=recent',
            success: function (html) {
                $('#recent-chats').html(html);

                // auto open first chat if nothing selected yet
                if (!chatType) {
                    const first = document.querySelector('.recent-item.auto-open');

                    if (first) {
                        const type = first.dataset.type;
                        const id = first.dataset.id;
                        const name = first.dataset.name;

                        selectRecent(type, id, name);
                    }
                }
            }
        });
    }

    $(document).ready(function () {
        loadRecentChats(); 
    });

    //refresh sidebar every 3 sec
    setInterval(() => {
        loadRecentChats();
    }, 3000);

    //refresh messages every 2 sec
    setInterval(() => {
        if (chatType) {
            loadMessages();
        }
    }, 2000);
</script>
</body>
</html>
