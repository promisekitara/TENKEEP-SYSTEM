<?php
// profile.php - Developer contact and info page
session_start();
require_once 'includes/auth.php';
require_once 'includes/functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Only allow access to logged-in users
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

// Developer info (customize as needed)
$developer = [
    'name' => 'Promise Kitara',
    'institution' => 'Gulu University',
    'email' => 'promisekitara@gmail.com',
    'image' => 'roomie.jpg'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Contact - TenKeep</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dev-profile-container { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.08); padding: 30px; text-align: center; }
        .dev-profile-img { width: 180px; height: 180px; object-fit: cover; border-radius: 50%; margin-bottom: 20px; border: 4px solid #007bff; }
        .dev-profile-name { font-size: 1.5em; font-weight: bold; margin-bottom: 10px; }
        .dev-profile-inst { font-size: 1.1em; color: #555; margin-bottom: 10px; }
        .dev-profile-email { font-size: 1em; color: #007bff; margin-bottom: 10px; }
        .internal-header { background: #007bff; color: #fff; padding: 20px 0; text-align: center; margin-bottom: 30px; }
        .internal-header h1 { margin: 0; font-size: 2.2em; }
    </style>
</head>
<body>
<div class="internal-header">
    <h1>Developer Profile</h1>
</div>
<div class="dev-profile-container">
    <img src="roomie.jpg" alt="Developer Photo" class="dev-profile-img">
    <div class="dev-profile-name"><?=htmlspecialchars($developer['name'])?></div>
    <div class="dev-profile-inst">Institution: <?=htmlspecialchars($developer['institution'])?></div>
    <div class="dev-profile-email">Email: <a href="mailto:<?=htmlspecialchars($developer['email'])?>"><?=htmlspecialchars($developer['email'])?></a></div>
</div>
<?php include 'includes/footer.php'; ?>
</body>
</html>
