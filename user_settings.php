<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include_once __DIR__ . '/database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id']; //current user id

$success = "";
$error = "";

// handle form
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // change username
    if (isset($_POST['new_username'])) {
        $new_username = trim($_POST['new_username']); //remove whitespace

        //check if empty
        if ($new_username === "") {
            $error = "Username cannot be empty.";
        } else {
            //check if name already exists
            $check = $db->prepare("SELECT user_id FROM users WHERE user_name = :name AND user_id != :id");
            $check->execute([':name' => $new_username, ':id' => $user_id]);

            if ($check->fetch()) {
                $error = "Username already taken.";
            } else {

                //update name
                $stmt = $db->prepare("UPDATE users SET user_name = :name WHERE user_id = :id");
                $stmt->execute([':name' => $new_username, ':id' => $user_id]);

                //update session
                $_SESSION['user_name'] = $new_username;
                $success = "Username updated.";
            }
        }
    }

    // change password
    if (isset($_POST['change_password'])) {

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Get current password from db
    $stmt = $db->prepare("SELECT password FROM users WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user_data = $stmt->fetch();

    //if pw is wrong
    if (!$user_data || !password_verify($current_password, $user_data['password'])) {
        $error = "Current password is incorrect.";
    }
    //if pw too short
    elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters.";
    }
    //if pw dont match
    elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    }
    else {
        //hash pw
        $hash = password_hash($new_password, PASSWORD_DEFAULT);

        //update pw
        $stmt = $db->prepare("UPDATE users SET password = :pass WHERE user_id = :id");
        $stmt->execute([':pass' => $hash, ':id' => $user_id]);

        $success = "Password updated successfully.";
    }
}

    // profile image
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "uploads/";

        //get file name
        $file_name = basename($_FILES["profile_pic"]["name"]);

        //timestamp to make name unique
        $target_file = $target_dir . time() . "_" . $file_name;

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            //save filepath to db
            $stmt = $db->prepare("UPDATE users SET profile_pic = :pic WHERE user_id = :id");
            $stmt->execute([':pic' => $target_file, ':id' => $user_id]);

            $success = "Profile picture updated.";
        } else {
            $error = "Upload failed.";
        }
    }

    // delete acc
    if (isset($_POST['delete_account'])) {

        //remove from db
        $stmt = $db->prepare("DELETE FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $user_id]);

        session_destroy(); //end session

        //go to register page
        header("Location: register.php");
        exit;
    }
}

// get user data
$stmt = $db->prepare("SELECT user_name, profile_pic FROM users WHERE user_id = :id");
$stmt->execute([':id' => $user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>

<html>
<head>
    <title>Settings</title>
    <link rel="stylesheet" href="styles/styles.css">
    <script>
        // Apply saved theme before page renders
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
</head>
<body>

<div id="layout">
    <!-- TOP BAR (same as chat) -->
    <div id="top-bar">
        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?>'s Account Settings</span>
        <div>
            <a href="index.php" class="logout-btn">Home</a>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div id="app">
        <div id="chat-container">
        <div id="message-container" class="settings-content">
        <div class="settings-grid">

            <!-- LEFT COLUMN -->
            <div class="settings-column">
                <div class="settings-card">
                    <h3>Profile Picture</h3>

                    <?php if (!empty($user['profile_pic'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" class="profile-img">
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="profile_pic">
                        <button type="submit">Upload</button>
                    </form>
                </div>
                <div class="settings-card">
                    <h3>Dark Mode</h3>

                    <div class="theme-toggle">
                <label>
                    <input type="checkbox" id="theme-switch">
                    Dark Mode
                </label>
            </div>
                </div>
            </div>

            <!-- RIGHT COLUMN -->
            <div class="settings-column">

                <div class="settings-card">
                    <h3>Change Username</h3>
                    <form method="post">
                        <input type="text" name="new_username" placeholder="New username">
                        <button type="submit">Update</button>
                    </form>
                </div>

                <div class="settings-card">
                    <h3>Change Password</h3>
                    <form method="post">
                        <input type="password" name="current_password"  placeholder="Current password" required>
                        <input type="password" name="new_password" placeholder="New password" required>
                        <input type="password" name="confirm_password" placeholder="Confirm new password" required>
                        <button type="submit" name="change_password">Change</button>
                    </form>
                </div>

                <div class="settings-card danger">
                    <h3>Danger Zone</h3>
                    <form method="post">
                        <button type="submit" name="delete_account" class="delete-btn"
                            onclick="return confirm('Are you sure you want to delete your account? This cannot be undone.');">
                            Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    const toggle = document.getElementById('theme-switch'); //dark mode checkbox

    // Load saved theme
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark-mode'); //apply dark mode
        toggle.checked = true;
    }

    // Toggle theme
    toggle.addEventListener('change', () => {
        if (toggle.checked) {
            //enable dark mode
            document.documentElement.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');

        } else {
            //go back to light mode
            document.documentElement.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
        }
    });
</script>
</body>
</html>
