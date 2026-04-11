<?php
include_once __DIR__ . '/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) { exit; }

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_name = htmlspecialchars(strip_tags($_POST['user_name']));
    $password = $_POST['password'] ?? '';

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check = $db->prepare("SELECT user_id FROM users WHERE user_name = :user_name");
        $check->execute([':user_name' => $user_name]);

        if ($check->fetch()) {
            $error = "Username already exists.";
        } else {
            // password_hash uses a salt automatically.
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (user_name, password)
                      VALUES (:user_name, :password)";
            $stmt = $db->prepare($query);

            if ($stmt->execute([
                ':user_name' => $user_name,
                ':password' => $password_hash
            ])) {
                $success = "Registration successful!";
            } else {
                $error = "Error registering user.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register</title>
<link rel="stylesheet" href="styles/register.css">
</head>
<body>
<div class="register-container">
    <?php if (!empty($success)): ?>
        <div class="success">
            <?php echo htmlspecialchars($success); ?>
            <br>
            <a href="login.php">Click here to login</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <h2>Create Account</h2>

    <form method="post">
        <input type="text" name="user_name" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password (6+ characters)" required>
        <button type="submit">Register</button>
    </form>

    <form action="login.php" method="get">
        <button type="submit" class="login-btn">Back to Login</button>
    </form>

    <div class="footer-text">Already have an account? Login above</div>
</div>
</body>
</html>
