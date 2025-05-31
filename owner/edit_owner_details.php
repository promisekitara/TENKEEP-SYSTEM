<?php
// edit_owner_details.php - Owner can edit their details
require_once '../includes/header.php';
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_role('owner');

$owner_user_id = get_user_id();

// Fetch current owner details
$owner_info_result = execute_query($conn, "SELECT u.username, u.email FROM users u WHERE u.user_id = $owner_user_id");
$owner = fetch_array($owner_info_result);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($username && $email) {
        $update_owner_sql = "UPDATE users SET username = ?, email = ? WHERE user_id = ?";
        $result = execute_prepared_query($conn, $update_owner_sql, 'ssi', [$username, $email, $owner_user_id]);
        if ($result !== false) {
            $success = 'Details updated successfully!';
            // Refresh owner info
            $owner_info_result = execute_query($conn, "SELECT username, email FROM users WHERE user_id = $owner_user_id");
            $owner = fetch_array($owner_info_result);
        } else {
            $error = 'Failed to update details.';
        }
    } else {
        $error = 'All fields are required.';
    }
}
?>

<style>
.edit-details-page {
    max-width: 500px;
    margin: 40px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.08);
    padding: 30px;
}
.edit-details-page h2 {
    text-align: center;
    color: #007bff;
    margin-bottom: 25px;
}
.edit-details-page label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: bold;
}
.edit-details-page input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1em;
}
.edit-details-page button {
    background-color: #007bff;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1em;
    width: 100%;
    transition: background-color 0.3s ease;
}
.edit-details-page button:hover {
    background-color: #0056b3;
}
.edit-details-page .success-message {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    text-align: center;
}
.edit-details-page .error-message {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
    text-align: center;
}
</style>

<div class="edit-details-page">
    <h2>Edit Your Details</h2>
    <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($owner['username'] ?? ''); ?>" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($owner['email'] ?? ''); ?>" required>
        <button type="submit">Update Details</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
