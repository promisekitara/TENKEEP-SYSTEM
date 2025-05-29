<?php
require_once '../includes/header.php';
require_once '../config/db.php';
require_once '../includes/auth.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_role('owner');

$owner_user_id = get_user_id();

// Fetch properties owned by the current owner
$properties_result = execute_query($conn, "SELECT property_id, name, price FROM properties WHERE owner_id = $owner_user_id");
$properties = fetch_all($properties_result);
$total_properties = count($properties);
$total_property_value = array_sum(array_column($properties, 'price'));

// Fetch all tenants associated with the owner's properties
$tenants_result = execute_query($conn, "SELECT t.tenant_id, t.name AS tenant_name, p.name AS property_name, t.property_id, p.price AS property_price
                                        FROM tenants t
                                        JOIN properties p ON t.property_id = p.property_id
                                        WHERE p.owner_id = $owner_user_id");
$tenants = fetch_all($tenants_result);
$active_tenants = count($tenants);

// Calculate overall rent collected percentage (approximate) for the owner's properties
$total_rent_paid_overall = 0;
foreach ($tenants as $tenant) {
    $payments_result = execute_query($conn, "SELECT SUM(amount) AS paid FROM payments WHERE tenant_id = " . $tenant['tenant_id'] . " AND property_id = " . $tenant['property_id']);
    $payment_data = fetch_array($payments_result);
    $total_rent_paid_overall += $payment_data['paid'] ?? 0;
}
$overall_paid_percentage = ($total_property_value > 0) ? ($total_rent_paid_overall / $total_property_value) * 100 : 0;

// Get data for a single tenant (the first one associated with the owner's properties)
$single_tenant_data = null;
if (!empty($tenants)) {
    $single_tenant = $tenants[0];
    $single_tenant_payments_result = execute_query($conn, "SELECT SUM(amount) AS paid FROM payments WHERE tenant_id = " . $single_tenant['tenant_id'] . " AND property_id = " . $single_tenant['property_id']);
    $single_tenant_payment_data = fetch_array($single_tenant_payments_result);
    $single_tenant_paid_amount = $single_tenant_payment_data['paid'] ?? 0;
    $single_tenant_paid_percentage = ($single_tenant['property_price'] > 0) ? ($single_tenant_paid_amount / $single_tenant['property_price']) * 100 : 0;
    $single_tenant_data = [
        'name' => $single_tenant['tenant_name'],
        'property' => $single_tenant['property_name'],
        'paid_percentage' => number_format($single_tenant_paid_percentage, 2),
    ];
}

// Calculate vacant properties (simplistic approach: properties without a tenant)
$properties_with_tenants = array_unique(array_column($tenants, 'property_id'));
$vacant_properties = $total_properties - count($properties_with_tenants);

?>

<style>
    /* Styling from the previous response */
       body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: #333;
        margin: 0;
        padding: 20px;
        /* Animated gradient background */
        background: linear-gradient(45deg, #e0f2f7, #f0f8ff, #e0f2f7);
        background-size: 300% 100%;
        animation: gradientAnimation 10s infinite alternate;
    }

    @keyframes gradientAnimation {
        0% {
            background-position: 0%;
        }
        100% {
            background-position: 100%;
        }
    }


    h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 30px;
    }

    h3 {
        color: #3498db;
        margin-top: 25px;
        margin-bottom: 15px;
        border-bottom: 2px solid #ecf0f1;
        padding-bottom: 10px;
    }

    .dashboard-widgets {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .widget {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 20px;
        text-align: center;
    }

    .widget h3 {
        color: #555;
        margin-top: 0;
        margin-bottom: 10px;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }

    .widget p {
        font-size: 1.5em;
        color: #3498db;
        font-weight: bold;
        margin: 0;
    }

    .tenant-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .tenant-item {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 15px;
    }

    .tenant-item h4 {
        color: #2c3e50;
        margin-top: 0;
        margin-bottom: 10px;
    }

    .tenant-item p {
        color: #777;
        margin-bottom: 5px;
    }

    .tenant-item .rent-paid {
        font-weight: bold;
        color: #27ae60; /* Example color for paid percentage */
    }

    .single-tenant-info {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-top: 20px;
    }

    .single-tenant-info h4 {
        color: #2c3e50;
        margin-top: 0;
        margin-bottom: 10px;
    }

    .single-tenant-info p {
        font-size: 1.2em;
        font-weight: bold;
        color: #e67e22; /* Example color for single tenant percentage */
    }

    .overall-progress {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-top: 20px;
        text-align: center;
    }

    .overall-progress h3 {
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }

    .overall-progress p {
        font-size: 1.3em;
        font-weight: bold;
        color: #1abc9c; /* Example color for overall progress */
        margin-bottom: 0;
    }

    .error {
        color: #c0392b;
        background-color: #fdecea;
        padding: 10px;
        border-radius: 4px;
        margin-top: 20px;
        text-align: center;
        border: 1px solid #e74c3c;
    }
</style>

<h2>Owner Dashboard</h2>

<div class="dashboard-widgets">
    <div class="widget">
        <h3>Total Properties</h3>
        <p><?php echo $total_properties; ?></p>
    </div>
    <div class="widget">
        <h3>Active Tenants</h3>
        <p><?php echo $active_tenants; ?></p>
    </div>
    <div class="widget">
        <h3>Vacant Properties</h3>
        <p><?php echo $vacant_properties; ?></p>
    </div>
    <div class="widget">
        <h3>Overall Rent Collected (Approx.)</h3>
        <p><?php echo number_format($overall_paid_percentage, 2); ?>%</p>
    </div>
</div>

<h3>Tenant Overview</h3>
<div class="tenant-list">
    <?php if (!empty($tenants)): ?>
        <?php foreach ($tenants as $tenant): ?>
            <div class="tenant-item">
                <h4><?php echo htmlspecialchars($tenant['tenant_name']); ?></h4>
                <p>Property: <?php echo htmlspecialchars($tenant['property_name']); ?></p>
                <?php
                $tenant_payments_result = execute_query($conn, "SELECT SUM(amount) AS paid FROM payments WHERE tenant_id = " . $tenant['tenant_id'] . " AND property_id = " . $tenant['property_id']);
                $tenant_payment_data = fetch_array($tenant_payments_result);
                $tenant_paid_amount = $tenant_payment_data['paid'] ?? 0;
                $tenant_paid_percentage_single = ($tenant['property_price'] > 0) ? ($tenant_paid_amount / $tenant['property_price']) * 100 : 0;
                ?>
                <p class="rent-paid">Rent Paid: <?php echo number_format($tenant_paid_percentage_single, 2); ?>%</p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No tenants found for your properties.</p>
    <?php endif; ?>
</div>

<h3>Single Tenant Highlight</h3>
<?php if ($single_tenant_data): ?>
    <div class="single-tenant-info">
        <h4><?php echo htmlspecialchars($single_tenant_data['name']); ?></h4>
        <p>Property: <?php echo htmlspecialchars($single_tenant_data['property']); ?></p>
        <p>Rent Paid: <?php echo $single_tenant_data['paid_percentage']; ?>%</p>
    </div>
<?php else: ?>
    <p>No tenants to highlight for your properties.</p>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>