<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $username = escape_string($conn, $username);
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        $check_sql = "SELECT username FROM users WHERE username = '$username'";
        $check_result = execute_query($conn, $check_sql);

        if (num_rows($check_result) > 0) {
            $error = 'Username already exists.';
        } else {
            $insert_sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password_hashed', 'owner')";
            if (execute_query($conn, $insert_sql)) {
                $success = 'Registration successful. You can now <a href="login.php">log in</a>.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TenKeep - Owner Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <h2>TenKeep - Owner Registration</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <form method="post">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Log In</a></p>
    </div>
</body>
</html>