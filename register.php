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

    if ($stmt->execute()) {
        echo "Registration successful! <a href='login.php'>Login here</a>";
        exit;
    } else {
        echo "Error registering user.";
    }
}
?>

<!-- HTML Form for Registration -->
<form method="post">
    <input type="text" name="user_name" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>