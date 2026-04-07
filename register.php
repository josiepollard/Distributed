<?php
/**
 * register.php
 * ------------
 * Allows new users to create an account
 * - Saves username + hashed password in DB
 */

include_once __DIR__ . '/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) { exit; }

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Sanitize username so no HTML is stored
    $user_name = htmlspecialchars(strip_tags($_POST['user_name']));

    // Hash password (never store plain text passwords)
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $query = "INSERT INTO users (user_name, password)
              VALUES (:user_name, :password)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_name", $user_name);
    $stmt->bindParam(":password", $password_hash);

    $success = "";
$error = "";

if ($stmt->execute()) {
    $success = "Registration successful!";
} else {
    $error = "Error registering user.";
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
            <?php echo $success; ?>
            <br>
            <a href="login.php">Click here to login</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    <h2>Create Account</h2>

        

    <form method="post">
        <input type="text" name="user_name" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>

    <!-- Back to Login -->
    <form action="login.php" method="get">
        <button type="submit" class="login-btn">Back to Login</button>
    </form>

    <div class="footer-text">
        Already have an account? Login above
    </div>
</div>

</body>
</html>