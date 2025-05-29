<?php
require_once '../includes/header.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

require_role('owner');

$error = '';
$success = '';

// Fetch owner's properties for the dropdown
$owner_id = get_user_id();
$properties_result = execute_query($conn, "SELECT property_id, name FROM properties WHERE owner_id = $owner_id");
$properties = [];
if ($properties_result) {
    while ($row = fetch_array($properties_result)) {
        $properties[$row['property_id']] = $row['name'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = escape_string($conn, $_POST['name']);
    $contact_number = escape_string($conn, $_POST['contact_number']);
    $property_id = intval($_POST['property_id']);
    $username = escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    if (empty($name) || empty($username) || empty($password) || !isset($properties[$property_id])) {
        $error = 'All fields are required.';
    } else {
        // Check if username already exists
        $check_sql = "SELECT username FROM users WHERE username = '$username'";
        $check_result = execute_query($conn, $check_sql);
        if (num_rows($check_result) > 0) {
            $error = 'Username already exists.';
        } else {
            // Create user account for the tenant
            $password_hashed = password_hash($password, PASSWORD_DEFAULT);
            $user_insert_sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password_hashed', 'tenant')";
            if (execute_query($conn, $user_insert_sql)) {
                $new_user_id_result = mysqli_insert_id($conn);
                // Add tenant details
                $tenant_insert_sql = "INSERT INTO tenants (user_id, property_id, name, contact_number) VALUES ($new_user_id_result, $property_id, '$name', '$contact_number')";
                if (execute_query($conn, $tenant_insert_sql)) {
                    $success = 'Tenant added successfully. The tenant can now log in with the provided username and password.';
                } else {
                    $error = 'Error adding tenant details.';
                    // Optionally, delete the created user if tenant creation fails
                    execute_query($conn, "DELETE FROM users WHERE user_id = $new_user_id_result");
                }
            } else {
                $error = 'Error creating user account for the tenant.';
            }
        }
    }
}
?>

<style>
    .add-tenant-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        padding: 30px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        gap: 30px;
    }

    .floating-image-left, .floating-image-right {
        flex: 0 0 auto;
        max-width: 200px;
        height: auto;
    }

    .floating-image-left img, .floating-image-right img {
        display: block;
        width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .main-content {
        flex: 1;
        background-color: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        max-width: 500px;
    }

    .main-content h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 25px;
    }

    .error-message, .success-message {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
    }

    .error-message {
        background-color: #ffebee;
        color: #d32f2f;
        border: 1px solid #ef9a9a;
    }

    .success-message {
        background-color: #e8f5e9;
        color: #388e3c;
        border: 1px solid #a5d6a7;
    }

    .add-tenant-form div {
        margin-bottom: 15px;
    }

    .add-tenant-form label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: bold;
    }

    .add-tenant-form input[type="text"],
    .add-tenant-form input[type="password"],
    .add-tenant-form select {
        width: calc(100% - 22px);
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1em;
    }

    .add-tenant-form button {
        background-color: #007bff;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1.1em;
        width: 100%;
        transition: background-color 0.3s ease;
    }

    .add-tenant-form button:hover {
        background-color: #0056b3;
    }

    .add-tenant-page p.success a {
        color: #198754;
        text-decoration: underline;
        font-weight: bold;
    }
</style>

<div class="add-tenant-page">
    <div class="floating-image-left">
        <img src="../assets/images/pexels-vladbagacian-1212053.jpg" alt="Tenant Image Left">
    </div>

    <div class="main-content">
        <h2>Add New Tenant</h2>

        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php else: ?>
            <form method="post" class="add-tenant-form">
                <div>
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div>
                    <label for="contact_number">Contact Number:</label>
                    <input type="text" id="contact_number" name="contact_number">
                </div>
                <div>
                    <label for="property_id">Property:</label>
                    <select id="property_id" name="property_id" required>
                        <option value="">Select Property</option>
                        <?php foreach ($properties as $id => $name): ?>
                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit">Add Tenant</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="floating-image-right">
        <img src="../assets/images/pexels-kindelmedia-7578992.jpg" alt="Tenant Image Right">
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>