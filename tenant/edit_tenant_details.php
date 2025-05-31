<?php
// edit_tenant_details.php - Tenant can edit their details
require_once '../includes/header.php';
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_role('tenant');

$tenant_user_id = get_user_id();

// Fetch current tenant details
$tenant_info_result = execute_query($conn, "SELECT t.*, u.username FROM tenants t JOIN users u ON t.user_id = u.user_id WHERE t.user_id = $tenant_user_id");
$tenant = fetch_array($tenant_info_result);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $username = trim($_POST['username'] ?? '');

    if ($name && $contact_number && $username) {
        // Use transactions for atomicity
        mysqli_begin_transaction($conn);
        try {
            $update_tenant_sql = "UPDATE tenants SET name = ?, contact_number = ? WHERE user_id = ?";
            $tenant_stmt = mysqli_prepare($conn, $update_tenant_sql);
            mysqli_stmt_bind_param($tenant_stmt, 'ssi', $name, $contact_number, $tenant_user_id);
            $tenant_ok = mysqli_stmt_execute($tenant_stmt);
            mysqli_stmt_close($tenant_stmt);

            $update_user_sql = "UPDATE users SET username = ? WHERE user_id = ?";
            $user_stmt = mysqli_prepare($conn, $update_user_sql);
            mysqli_stmt_bind_param($user_stmt, 'si', $username, $tenant_user_id);
            $user_ok = mysqli_stmt_execute($user_stmt);
            mysqli_stmt_close($user_stmt);

            if ($tenant_ok && $user_ok) {
                mysqli_commit($conn);
                $success = 'Details updated successfully!';
                $tenant_info_result = execute_query($conn, "SELECT t.*, u.username FROM tenants t JOIN users u ON t.user_id = u.user_id WHERE t.user_id = $tenant_user_id");
                $tenant = fetch_array($tenant_info_result);
            } else {
                mysqli_rollback($conn);
                $error = 'Failed to update details.';
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Database error: ' . $e->getMessage();
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
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($tenant['name'] ?? ''); ?>" required>
        <label for="contact_number">Contact Number:</label>
        <input type="text" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($tenant['contact_number'] ?? ''); ?>" required>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($tenant['username'] ?? ''); ?>" required>
        <button type="submit">Update Details</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
