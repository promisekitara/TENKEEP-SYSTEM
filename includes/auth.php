<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_user_role() {
    return $_SESSION['role'] ?? ($_SESSION['user_role'] ?? null);
}

function login($conn, $username, $password) {
    $username = escape_string($conn, $username);
    $password = escape_string($conn, $password);

    $sql = "SELECT user_id, password, role FROM users WHERE username = '$username'";
    $result = execute_query($conn, $sql);

    if ($result && num_rows($result) == 1) {
        $user = fetch_array($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = $user['role'];
            return true;
        }
    }
    return false;
}

function logout() {
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

function require_login() {
    if (!is_logged_in()) {
        redirect('/tenkeep/auth/login.php');
    }
}

function require_role($role) {
    require_login();
    if (get_user_role() !== $role) {
        // Redirect to login page with unauthorized message
        header('Location: /XTenKeep/auth/login.php?unauthorized=1');
        exit();
    }
}
?>