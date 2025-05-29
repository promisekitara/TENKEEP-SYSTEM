<?php
require_once '../includes/header.php';
require_once '../config/db.php';

require_role('tenant');

$tenant_user_id = get_user_id();

$payments_result = execute_query($conn, "SELECT py.*, p.name AS property_name
                                         FROM payments py
                                         JOIN tenants t ON py.tenant_id = t.tenant_id
                                         JOIN properties p ON py.property_id = p.property_id
                                         WHERE t.user_id = $tenant_user_id
                                         ORDER BY py.payment_date DESC");
$payments = [];
if ($payments_result) {
    while ($row = fetch_array($payments_result)) {
        $payments[] = $row;
    }
}
?>

<style>
    .tenant-payments-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: 80%;
    }

    .tenant-payments-page h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 25px;
        font-size: 2.2em;
    }

    .payments-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .payments-table thead {
        background-color: #3498db;
        color: white;
    }

    .payments-table th, .payments-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ecf0f1;
    }

    .payments-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .payments-table tbody tr:hover {
        background-color: #eff6ff;
    }

    .payments-table td {
        vertical-align: top;
    }

    .no-payments-message {
        text-align: center;
        color: #7f8c8d;
        font-style: italic;
        padding: 20px;
        border: 1px solid #ecf0f1;
        border-radius: 8px;
        background-color: #f0f0f0;
        margin-top: 20px;
    }
</style>

<div class="tenant-payments-page">
    <h2>Your Payments</h2>

    <table class="payments-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Property</th>
                <th>Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($payments)): ?>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                        <td><?php echo htmlspecialchars($payment['property_name']); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($payment['description'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="no-payments-message">No payments recorded yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>