<?php
require_once '../includes/header.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('owner');

$owner_id = get_user_id();

$error = '';
$success = '';

$tenants_result = execute_query($conn, "SELECT t.*, p.name AS property_name, u.username FROM tenants t
                                        JOIN properties p ON t.property_id = p.property_id
                                        JOIN users u ON t.user_id = u.user_id
                                        WHERE p.owner_id = $owner_id");
$tenants = [];
if ($tenants_result) {
    while ($row = fetch_array($tenants_result)) {
        $tenants[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id_to_remove = intval($_POST['tenant_id']);
    $delete_user = isset($_POST['delete_user']) && $_POST['delete_user'] === '1'; // Check the checkbox

    //  Check if the tenant exists and is associated with this owner.
    $check_tenant_sql = "SELECT t.tenant_id FROM tenants t JOIN properties p ON t.property_id = p.property_id WHERE t.tenant_id = $tenant_id_to_remove AND p.owner_id = $owner_id";
    $check_tenant_result = execute_query($conn, $check_tenant_sql);

    if (num_rows($check_tenant_result) == 0) {
        $error = "Tenant does not exist or is not associated with your property.";
    } else {
        if (removeTenant($conn, $tenant_id_to_remove, $delete_user)) {
            $success = 'Tenant removed successfully.';
            //  Remove the deleted tenant from the array.
            $tenants = array_filter($tenants, function($tenant) use ($tenant_id_to_remove) {
                return $tenant['tenant_id'] != $tenant_id_to_remove;
            });
        } else {
            $error = 'Error removing tenant.';
        }
    }
}
?>

<style>
    .tenants-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        padding: 30px;
    }

    .tenants-page h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 20px;
    }

    .add-tenant-link {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        text-decoration: none;
        margin-bottom: 20px;
        transition: background-color 0.3s ease;
    }

    .add-tenant-link:hover {
        background-color: #0056b3;
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

    .tenants-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .tenants-table th, .tenants-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .tenants-table thead {
        background-color: #f8f9fa;
        color: #333;
    }

    .tenants-table tbody tr:last-child td {
        border-bottom: none;
    }

    .tenants-table td form {
        margin-bottom: 0; /* Remove default form margin */
    }

    .tenants-table button {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin-right: 5px;
    }

    .tenants-table button:hover {
        background-color: #c82333;
    }

    .tenants-table label {
        font-size: 0.9em;
        color: #555;
        margin-left: 10px;
    }

    .no-tenants {
        text-align: center;
        color: #777;
        font-style: italic;
    }
</style>

<div class="tenants-page">
    <h2>Your Tenants</h2>

    <p><a href="add_tenant.php" class="add-tenant-link">Add New Tenant</a></p>

    <?php if ($error): ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success-message"><?php echo $success; ?></p>
    <?php endif; ?>

    <?php if (!empty($tenants)): ?>
        <table class="tenants-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Property</th>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $tenant): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tenant['name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['contact_number']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['property_name']); ?></td>
                        <td><?php echo htmlspecialchars($tenant['username']); ?></td>
                        <td>
                            <form method="post" style="display: inline-block;">
                                <input type="hidden" name="tenant_id" value="<?php echo $tenant['tenant_id']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to remove this tenant?');">Remove</button>
                                <label><input type="checkbox" name="delete_user" value="1"> Also delete user</label>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-tenants">No tenants added yet.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>