<?php
session_start();
include_once __DIR__ . '/database.php';

//db connection
$database = new Database();
$db = $database->getConnection();

//stop if db fail
if (!$db) { exit; }

//handle login form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    //clean username input
    $user_name = htmlspecialchars(strip_tags($_POST['user_name']));

    //get password
    $password  = $_POST['password'];

    //check that user exists
    $query = "SELECT user_id, user_name, password FROM users WHERE user_name = :user_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_name", $user_name);
    $stmt->execute();

    //fetch user data
    $user = $stmt->fetch();

    // Verify password 
    //compare entered password with hashed pw in db
    if ($user && password_verify($password, $user['password'])) {

        //store user info in session
        $_SESSION['user_id']   = (int)$user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];

        // Redirect to chat
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid login credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>

<div class="login-container">
    <?php if (!empty($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
        <h2>Welcome Back</h2>
        <form method="post">
            <input type="text" name="user_name" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <form action="register.php" method="get">
            <button type="submit" class="register-btn">Create Account</button>
        </form>
        <div class="footer-text">
            Don’t have an account? Register above
        </div>
    </div>
</body>
</html>