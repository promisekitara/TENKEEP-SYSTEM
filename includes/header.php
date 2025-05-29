<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

if (!is_logged_in()) {
    redirect('/tenkeep/auth/login.php');
}

// You might fetch logo URL from a database or configuration here
$logo_url = '../assets/images/logo.png'; // Example default logo
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TenKeep</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f8f9fa;
            color: #343a40;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        header {
            background-color: #007bff;
            color: white;
            padding: 15px 0; /* Slightly reduced padding */
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .logo-section {
            display: flex;
            align-items: center;
            margin-left: 20px;
        }

        .logo-section img {
            max-height: 40px; /* Adjust logo size as needed */
            margin-right: 15px;
        }

        header h1 {
            margin: 0;
            font-size: 2.2em; /* Slightly smaller title */
        }

        nav {
            margin-right: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 12px; /* Slightly smaller padding */
            margin-left: 8px; /* Slightly smaller margin */
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        nav a:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }

        main {
            padding: 20px;
            min-height: 60vh;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-section">
                <img src="../TenkeepLogo.png" alt="TenKeep Logo">
                <h1>TenKeep</h1>
            </div>
            <nav>
                <?php if (get_user_role() === 'owner'): ?>
                    <a href="../owner/dashboard.php">Dashboard</a>
                    <a href="../owner/properties.php">Properties</a>
                    <a href="../owner/tenants.php">Tenants</a>
                    <a href="../owner/complaints.php">Complaints</a>
                    <a href="../owner/payments.php">Payments</a>
                <?php elseif (get_user_role() === 'tenant'): ?>
                    <a href="../tenant/dashboard.php">Dashboard</a>
                    <a href="../tenant/complaints.php">Complaints</a>
                    <a href="../tenant/payments.php">Payments</a>
                <?php endif; ?>
                <a href="../auth/logout.php">Logout</a>
            </nav>
        </header>
        <main>