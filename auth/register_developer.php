<?php
// register_developer.php - Register form for developer accounts only
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

//error message
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!$username || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $username_esc = escape_string($conn, $username);
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE username='$username_esc'");
        if ($check && mysqli_num_rows($check) > 0) {
            $error = 'Username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, password, role) VALUES ('$username_esc', '$hash', 'developer')";
            if (mysqli_query($conn, $sql)) {
                $user_id = mysqli_insert_id($conn);
                // Ensure developers table exists before inserting
                $dev_table_check = mysqli_query($conn, "SHOW TABLES LIKE 'developers'");
                if ($dev_table_check && mysqli_num_rows($dev_table_check) > 0) {
                    @mysqli_query($conn, "INSERT INTO developers (user_id) VALUES ($user_id)");
                }
                $success = 'Developer account created successfully! You can now log in.';
            } else {
                $error = 'Failed to create developer account. Reason: ' . mysqli_error($conn);
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
    <title>Register Developer - XTenKeep</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body { background: #f4f6f8; }
        .dev-register-container { background: #fff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.08); padding: 40px 30px; max-width: 500px; margin: 40px auto; }
        .dev-register-container h2 { text-align: center; margin-bottom: 25px; color: #2c3e50; }
        .dev-register-container .form-label { font-weight: 600; }
        .dev-register-container .btn-primary { width: 100%; }
        .dev-register-container .alert { margin-bottom: 20px; }
        .dev-register-container .back-link { display: block; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
<div class="dev-register-container">
    <h2>Register Developer Account</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"> <?=htmlspecialchars($error)?> </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"> <?=htmlspecialchars($success)?> </div>
    <?php endif; ?>
    <form method="post" autocomplete="off">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3">
            <label for="confirm" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="confirm" name="confirm" required>
        </div>
        <button type="submit" class="btn btn-primary">Register Developer</button>
    </form>
    <a href="../auth/login.php" class="back-link">Back to Login</a>
</div>
</body>
</html>
