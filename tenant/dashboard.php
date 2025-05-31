<?php
require_once '../includes/header.php';
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php'; // Ensure functions.php is included for execute_query and fetch_all

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_role('tenant');

$tenant_user_id = get_user_id();

// Fetch tenant's username for display
$tenant_username = '';
if ($tenant_user_id) {
    // Assuming 'username' is the column in the 'users' table that stores the tenant's display name
    $tenant_name_result = execute_query($conn, "SELECT username FROM users WHERE user_id = $tenant_user_id");
    if ($tenant_name_result && num_rows($tenant_name_result) > 0) {
        $tenant_data = fetch_array($tenant_name_result);
        $tenant_username = htmlspecialchars($tenant_data['username']);
    }
}

// --- Refactored: Fetch actual tenant_id early to avoid repetition ---
$actual_tenant_id = null;
if ($tenant_user_id) {
    $get_tenant_id_sql = "SELECT tenant_id FROM tenants WHERE user_id = ?";
    $tenant_id_result = execute_prepared_query($conn, $get_tenant_id_sql, 'i', $tenant_user_id);
    if ($tenant_id_result && $row = fetch_array($tenant_id_result)) {
        $actual_tenant_id = $row['tenant_id'];
    }
}
// --- End Refactored ---

// --- IMPORTANT: Fetch tenant and property details *before* handling POST requests ---
// This ensures $tenant and $tenant_property_id are available for validation
$tenant_info_result = execute_query($conn, "SELECT t.*,
                                                   p.property_id,
                                                   p.name AS property_name,
                                                   p.address AS property_address,
                                                   p.price AS property_price,
                                                   o.username AS owner_username
                                            FROM tenants t
                                            JOIN properties p ON t.property_id = p.property_id
                                            JOIN users o ON p.owner_id = o.user_id
                                            WHERE t.user_id = $tenant_user_id");
$tenant = fetch_array($tenant_info_result);

// Initialize tenant_property_id here, as it's crucial for the POST handler
// Use null coalescing operator to safely get property_id, defaulting to null if $tenant is empty
$tenant_property_id = $tenant['property_id'] ?? null;

// --- End of POST handling. Remaining data fetches use the already retrieved $tenant variable ---

$property_price = $tenant['property_price'] ?? 0;
$total_payments = 0;
$property_rules = [];

if ($tenant_property_id) {
    // Refactored: Use $actual_tenant_id directly
    $total_payments_sql = "SELECT SUM(amount) AS total FROM payments WHERE tenant_id = ? AND property_id = ?";
    $total_payments_result = execute_prepared_query($conn, $total_payments_sql, 'ii', $actual_tenant_id, $tenant_property_id);
    $total_payment_data = fetch_array($total_payments_result);
    $total_payments = $total_payment_data['total'] ?? 0;

    // Fetch property rules for the tenant's property
    $rules_result = execute_query($conn, "SELECT rule_description FROM property_rules WHERE property_id = $tenant_property_id ORDER BY rule_id ASC");
    if ($rules_result) {
        while ($rule = fetch_array($rules_result)) {
            $property_rules[] = htmlspecialchars($rule['rule_description']);
        }
    }
}

$paid_percentage = ($property_price > 0) ? ($total_payments / $property_price) * 100 : 0;

// Fetch recent complaints by the tenant
// Refactored: Use $actual_tenant_id directly
$complaints_sql = "SELECT * FROM complaints WHERE tenant_id = ? ORDER BY complaint_date DESC LIMIT 3"; // Limit to 3 for the tile
$complaints_result = execute_prepared_query($conn, $complaints_sql, 'i', $actual_tenant_id);
$complaints = [];
if ($complaints_result) {
    while ($row = fetch_array($complaints_result)) {
        $complaints[] = $row;
    }
}

// Fetch recent property updates for the tenant's property (no change here, as it uses property_id directly)
$updates = [];
if ($tenant_property_id) {
    $updates_query = "SELECT * FROM updates WHERE property_id = $tenant_property_id ORDER BY update_date DESC LIMIT 3"; // Limit to 3 recent updates
    $updates_result = execute_query($conn, $updates_query);
    if ($updates_result) {
        while ($row = fetch_array($updates_result)) {
            $updates[] = $row;
        }
    }
}
?>

<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f6f8;
        color: #333;
        margin: 0;
        padding: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }

    .dashboard-tile {
        background-color: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        padding: 20px;
        width: calc(33% - 20px); /* Adjust width for desired tile arrangement */
        min-width: 300px;
    }

    @media (max-width: 768px) {
        .dashboard-tile {
            width: calc(50% - 20px);
        }
    }

    @media (max-width: 576px) {
        .dashboard-tile {
            width: 100%;
        }
    }

    h2 {
        color: #007bff;
        margin-bottom: 20px;
        text-align: center;
        font-size: 2em;
        width: 100%; /* Ensure it spans across */
    }

    h3 {
        color: #28a745;
        margin-top: 0;
        margin-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 8px;
    }

    p {
        line-height: 1.6;
        margin-bottom: 8px;
        color: #555;
    }

    strong {
        font-weight: bold;
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    thead {
        background-color: #007bff;
        color: white;
    }

    th, td {
        padding: 8px 10px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .error {
        color: #dc3545;
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 10px;
        border-radius: 5px;
        margin-top: 20px;
        text-align: center;
        width: 100%;
    }

    a {
        color: #007bff;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    a:hover {
        color: #0056b3;
        text-decoration: underline;
    }

    .complaints-tile ul {
        list-style: none;
        padding: 0;
        margin-top: 10px;
    }

    .complaints-tile li {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }

    .complaints-tile li:last-child {
        border-bottom: none;
    }

    .status-pending {
        color: #ffc107; /* Amber */
    }

    .status-replied {
        color: #28a745; /* Green */
    }

    .rules-tile ul {
        list-style: none;
        padding: 0;
        margin-top: 10px;
    }

    .rules-tile li {
        padding: 5px 0;
        border-bottom: 1px dotted #ccc;
    }

    .rules-tile li:last-child {
        border-bottom: none;
    }

    .message-container {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 5px;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
</style>

<h2>Tenant Dashboard</h2>

<?php if (isset($_SESSION['message'])): ?>
    <div class="message-container <?php echo $_SESSION['message_type']; ?>">
        <?php echo $_SESSION['message']; ?>
    </div>
    <?php unset($_SESSION['message']); ?>
    <?php unset($_SESSION['message_type']); ?>
<?php endif; ?>

<?php if ($tenant_username): ?>
    <p style="text-align: center; font-size: 1.2em; color: #34495e; margin-bottom: 30px;">Welcome, <strong><?php echo $tenant_username; ?></strong>! Here's an overview of your tenancy.</p>
<?php else: ?>
    <p style="text-align: center; font-size: 1.2em; color: #34495e; margin-bottom: 30px;">Welcome! Here's an overview of your tenancy.</p>
<?php endif; ?>

<div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; width: 100%;">
    <?php if ($tenant): ?>
        <div class="dashboard-tile" style="flex-grow: 1; min-width: 300px;">
            <h3>Your Property</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($tenant['property_name']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($tenant['property_address']); ?></p>
            <p><strong>Rent:</strong> $<?php echo htmlspecialchars($tenant['property_price']); ?></p>
            <p>
                <strong>Payment Progress:</strong> <?php echo htmlspecialchars(number_format($paid_percentage, 2)); ?>%
                (Paid: $<?php echo htmlspecialchars(number_format($total_payments, 2)); ?>)
            </p>
        </div>

        <?php if ($tenant['owner_username']): ?>
            <div class="dashboard-tile" style="flex-grow: 1; min-width: 300px;">
                <h3>Property Owner</h3>
                <p><strong>Name/Username:</strong> <?php echo htmlspecialchars($tenant['owner_username']); ?></p>
                <p>You can contact the property owner through the platform.</p>
            </div>
        <?php endif; ?>

        <div class="dashboard-tile complaints-tile" style="flex-grow: 1; min-width: 300px;">
            <h3>Recent Complaints</h3>
            <?php if (!empty($complaints)): ?>
                <ul>
                    <?php foreach ($complaints as $complaint): ?>
                        <li>
                            <strong>Date:</strong> <?php echo htmlspecialchars($complaint['complaint_date']); ?><br>
                            <strong>Subject:</strong> <?php echo htmlspecialchars($complaint['subject']); ?><br>
                            <strong>Status:</strong> <span class="<?php echo $complaint['reply'] ? 'status-replied' : 'status-pending'; ?>"><?php echo $complaint['reply'] ? 'Replied' : 'Pending'; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p><a href="complaints.php">View All Complaints</a></p>
            <?php else: ?>
                <p>No recent complaints.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($property_rules)): ?>
            <div class="dashboard-tile rules-tile" style="flex-grow: 1; min-width: 300px;">
                <h3>Property Rules</h3>
                <ul>
                    <?php foreach ($property_rules as $rule): ?>
                        <li><?php echo $rule; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <p class="error">Tenant information not found.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
