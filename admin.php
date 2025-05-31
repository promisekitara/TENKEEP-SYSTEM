<?php
session_start();
//the developer is the admin
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'developer') {
    header('Location: ../index.php');
    exit();
}
require_once 'includes/auth.php';
require_once 'includes/functions.php';
require_once 'config/db.php';
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
            $sql = "INSERT INTO users (username, password, role, registration_date) VALUES ('$username_esc', '$hash', 'developer', NOW())";
            if (mysqli_query($conn, $sql)) {
                // Also add to developers table if it exists
                $user_id = mysqli_insert_id($conn);
                @mysqli_query($conn, "INSERT INTO developers (user_id) VALUES ($user_id)");
                $success = 'Developer account created successfully!';
                logActivity($conn, $_SESSION['user_id'], 'Added Developer', "Username: $username");
            } else {
                $error = 'Failed to create developer account.';
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
    <title>Add Developer - XTenKeep</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f4f6f8; }
        .dev-add-container { background: #fff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.08); padding: 40px 30px; max-width: 500px; margin: 40px auto; }
        .dev-add-container h2 { text-align: center; margin-bottom: 25px; color: #2c3e50; }
        .dev-add-container .form-label { font-weight: 600; }
        .dev-add-container .btn-primary { width: 100%; }
        .dev-add-container .alert { margin-bottom: 20px; }
        .dev-add-container .back-link { display: block; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?> 
<div class="container dev-add-container">
    <h2 class="mb-4">Add Developer</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
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
        <button type="submit" class="btn btn-primary">Create Developer Account</button>
    </form>
    <a href="../developer.php" class="back-link">Back to Developer Dashboard</a>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
<?php
// Close the database connection

mysqli_close($conn);
?>