<?php
require_once '../includes/header.php';
require_once '../config/db.php';

require_role('tenant');

$tenant_user_id = get_user_id();

// Fetch tenant and property details along with owner (manager) details
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

$property_price = $tenant['property_price'] ?? 0;
$tenant_property_id = $tenant['property_id'] ?? null;
$total_payments = 0;

if ($tenant_property_id) {
    $total_payments_result = execute_query($conn, "SELECT SUM(amount) AS total FROM payments
                                                  WHERE tenant_id = (SELECT tenant_id FROM tenants WHERE user_id = $tenant_user_id)
                                                    AND property_id = $tenant_property_id");
    $total_payment_data = fetch_array($total_payments_result);
    $total_payments = $total_payment_data['total'] ?? 0;
}

$paid_percentage = ($property_price > 0) ? ($total_payments / $property_price) * 100 : 0;

// Fetch recent complaints by the tenant
$complaints_result = execute_query($conn, "SELECT * FROM complaints WHERE tenant_id = (SELECT tenant_id FROM tenants WHERE user_id = $tenant_user_id) ORDER BY complaint_date DESC LIMIT 3"); // Limit to 3 for the tile
$complaints = [];
if ($complaints_result) {
    while ($row = fetch_array($complaints_result)) {
        $complaints[] = $row;
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
</style>

<h2>Tenant Dashboard</h2>

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

    <?php else: ?>
        <p class="error">Tenant information not found.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>