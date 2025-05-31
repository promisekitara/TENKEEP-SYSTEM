<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' LIMIT 1";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['username'] = $user['username'];
            logActivity($conn, $user['user_id'], ucfirst($user['role']) . ' Login');
            if ($user['role'] === 'developer') {
                header('Location: ../developer.php');
            } elseif ($user['role'] === 'owner') {
                header('Location: ../owner/dashboard.php');
            } elseif ($user['role'] === 'tenant') {
                header('Location: ../tenant/dashboard.php');
            } else {
                header('Location: ../index.php');
            }
            exit();
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'Account not found.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TenKeep - Login</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .login-container {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            width: 80%;
            max-width: 900px;
            overflow: hidden;
        }

        .login-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
        }

        .login-logo {
            margin-bottom: 30px;
        }

        .login-logo img {
            max-width: 150px; /* Adjust as needed */
            height: auto;
        }

        .login-left h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 2.5em;
            text-align: center;
        }

        .error {
            color: #c0392b;
            background-color: #fdecea;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            border: 1px solid #e74c3c;
        }

        .login-form div {
            margin-bottom: 15px;
        }

        .login-form label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
        }

        .login-form button {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .login-form button:hover {
            background-color: #2980b9;
        }

        .login-left p {
            margin-top: 20px;
            color: #777;
            text-align: center;
            font-size: 0.9em;
        }

        .login-left p a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }

        .login-left p a:hover {
            text-decoration: underline;
        }

        .login-right {
            flex: 1;
            background: #f9f9f9; /* A light background for the info */
            color: #333;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            text-align: center;
        }

        .sth-text{
            font-size:50px;
            color:navy;
        }
        .developer-info-right img {
            height: 500px;
            width: 500px;
            border-radius: 20% 20% 0 0;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid #ddd;
        }

        .developer-info-right h3 {
            font-size: 1.3em;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        .developer-info-right p {
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .login-container {
                width: 95%;
                flex-direction: column;
            }
            .login-right {
                display: none; /* Hide on smaller screens */
            }
            .login-left {
                padding: 30px;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="login-logo">
                <img src="TenKeepLogo copy.png" alt="TenKeep Logo">
            </div>
            <h2>TenKeep Login</h2>
            <?php if ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="post" class="login-form">
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Log In</button>
            </form>
            <p>Don't have an account? (Owners only can register for now) <a href="register.php">Register</a></p>
        </div>
        <div class="login-right">
            <div class="developer-info-right">
                <img src="pexels-asphotograpy-101808.jpg">
            </div>
            <div class="sth-text">
                <p><strong>Safety First</strong></p>
            </div>
        </div>
    </div>
</body>
</html>