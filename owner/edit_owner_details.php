<?php
// edit_owner_details.php - Owner can edit their details
require_once '../includes/header.php';
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_role('owner');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$owner_user_id = get_user_id();

// Fetch current owner details
$owner_info_result = execute_query($conn, "SELECT u.username, u.profile_image FROM users u WHERE u.user_id = $owner_user_id");
$owner = fetch_array($owner_info_result);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $profile_image = $_FILES['profile_image'] ?? null;

    if ($username) {
        mysqli_begin_transaction($conn);
        try {
            $user_ok = true;
            $image_path = $owner['profile_image'] ?? null;
            if ($profile_image && $profile_image['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                if (in_array($profile_image['type'], $allowed_types)) {
                    $ext = pathinfo($profile_image['name'], PATHINFO_EXTENSION);
                    $new_filename = 'owner_' . $owner_user_id . '_' . time() . '.' . $ext;
                    $upload_dir = '../uploads/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                    $target_path = $upload_dir . $new_filename;
                    if (move_uploaded_file($profile_image['tmp_name'], $target_path)) {
                        $image_path = 'uploads/' . $new_filename;
                        $update_img_sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
                        $img_stmt = mysqli_prepare($conn, $update_img_sql);
                        mysqli_stmt_bind_param($img_stmt, 'si', $image_path, $owner_user_id);
                        $user_ok = $user_ok && mysqli_stmt_execute($img_stmt);
                        mysqli_stmt_close($img_stmt);
                    } else {
                        throw new Exception('Failed to upload image.');
                    }
                } else {
                    throw new Exception('Invalid image type. Only JPG, PNG, GIF allowed.');
                }
            }
            $update_user_sql = "UPDATE users SET username = ? WHERE user_id = ?";
            $user_stmt = mysqli_prepare($conn, $update_user_sql);
            if (!$user_stmt) {
                throw new Exception('Failed to prepare statement for user update: ' . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($user_stmt, 'si', $username, $owner_user_id);
            $user_ok = $user_ok && mysqli_stmt_execute($user_stmt);
            mysqli_stmt_close($user_stmt);

            if ($user_ok) {
                mysqli_commit($conn);
                $success = 'Details updated successfully!';
                $owner_info_result = execute_query($conn, "SELECT username, profile_image FROM users WHERE user_id = $owner_user_id");
                $owner = fetch_array($owner_info_result);
            } else {
                mysqli_rollback($conn);
                $error = 'Failed to update details.';
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Username is required.';
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
    <form method="post" enctype="multipart/form-data">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($owner['username'] ?? ''); ?>" required>
       
        <button type="submit">Update Details</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
