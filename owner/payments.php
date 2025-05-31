<?php
require_once '../includes/header.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('owner');

$owner_id = get_user_id();

$error = '';
$success = '';

// Fetch properties owned by the owner for the dropdown
$properties_result = execute_query($conn, "SELECT property_id, name FROM properties WHERE owner_id = $owner_id");
$properties = [];
if ($properties_result) {
    while ($row = fetch_array($properties_result)) {
        $properties[$row['property_id']] = $row['name'];
    }
}

// Fetch tenants for the dropdown.  Adjust the query to get only tenants of the owner
$tenants_result = execute_query($conn, "SELECT t.tenant_id, t.name AS tenant_name, p.property_id
                                        FROM tenants t
                                        JOIN properties p ON t.property_id = p.property_id
                                        WHERE p.owner_id = $owner_id"); //only tenants for owner's properties.

$tenants = [];
if ($tenants_result) {
    while ($row = fetch_array($tenants_result)) {
        $tenants[$row['tenant_id']] = [
            'tenant_name' => $row['tenant_name'],
            'property_id' => $row['property_id']
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tenant_id = intval($_POST['tenant_id'] ?? 0);
    $property_id = intval($_POST['property_id'] ?? 0);
    $payment_date = trim($_POST['payment_date'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    if ($tenant_id <= 0 || $property_id <= 0 || !$payment_date || $amount <= 0) {
        $error = 'All fields are required and must be valid.';
    } elseif (!isset($tenants[$tenant_id])) {
        $error = 'Invalid Tenant ID.';
    } elseif (!isset($properties[$property_id])) {
        $error = 'Invalid Property ID.';
    } elseif ($tenants[$tenant_id]['property_id'] != $property_id) {
        $error = "Tenant is not associated with selected property";
    } else {
        // Use null for empty description
        $desc = $description !== '' ? $description : null;
        if (recordPayment($conn, $tenant_id, $property_id, $payment_date, $amount, $desc)) {
            $success = 'Payment recorded successfully.';
        } else {
            $error = 'Error recording payment. Please check your input or contact support.';
        }
    }
}

// Fetch payments for display
$payments_result = execute_query($conn, "SELECT py.*, t.name AS tenant_name, p.name AS property_name
                                         FROM payments py
                                         JOIN tenants t ON py.tenant_id = t.tenant_id
                                         JOIN properties p ON py.property_id = p.property_id
                                         WHERE p.owner_id = $owner_id
                                         ORDER BY py.payment_date DESC");
$payments = [];
if ($payments_result) {
    while ($row = fetch_array($payments_result)) {
        $payments[] = $row;
    }
}
?>

<style>
    .payments-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        padding: 30px;
    }

    .payments-page h2 {
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

    .record-payment-section h3 {
        color: #3498db;
        margin-top: 25px;
        margin-bottom: 15px;
        border-bottom: 2px solid #ecf0f1;
        padding-bottom: 10px;
    }

    .record-payment-form {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        max-width: 500px;
        margin-left: auto; /* Center the form */
        margin-right: auto; /* Center the form */
    }

    .record-payment-form div {
        margin-bottom: 15px;
    }

    .record-payment-form label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: bold;
    }

    .record-payment-form select,
    .record-payment-form input[type="date"],
    .record-payment-form input[type="number"],
    .record-payment-form input[type="text"] {
        width: calc(100% - 22px); /* Adjust for padding and border */
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1em;
    }

    .record-payment-form button {
        background-color: #28a745;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1.1em;
        width: 100%;
        transition: background-color 0.3s ease;
    }

    .record-payment-form button:hover {
        background-color: #218838;
    }

    .payment-history-section h3 {
        color: #3498db;
        margin-top: 25px;
        margin-bottom: 15px;
        border-bottom: 2px solid #ecf0f1;
        padding-bottom: 10px;
    }

    .payments-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        overflow: hidden; /* Ensures rounded corners */
    }

    .payments-table th, .payments-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .payments-table thead {
        background-color: #f8f9fa;
        color: #333;
    }

    .payments-table tbody tr:last-child td {
        border-bottom: none;
    }

    .no-payments {
        text-align: center;
        color: #777;
        font-style: italic;
        margin-top: 20px;
        padding: 15px;
        background-color: #f0f0f0;
        border-radius: 8px;
    }
</style>

<div class="payments-page">
    <h2>Payments</h2>

    <?php if ($error): ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
        <p class="success-message"><?php echo $success; ?></p>
    <?php endif; ?>

    <div class="record-payment-section">
        <h3>Record New Payment</h3>
        <form method="post" class="record-payment-form">
            <div>
                <label for="tenant_id">Tenant:</label>
                <select id="tenant_id" name="tenant_id" required>
                    <option value="">Select Tenant</option>
                    <?php foreach ($tenants as $id => $tenant): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($_POST['tenant_id']) && $_POST['tenant_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tenant['tenant_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="property_id">Property:</label>
                <select id="property_id" name="property_id" required>
                    <option value="">Select Property</option>
                    <?php foreach ($properties as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($_POST['property_id']) && $_POST['property_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="payment_date">Payment Date:</label>
                <input type="date" id="payment_date" name="payment_date" value="<?php echo htmlspecialchars($_POST['payment_date'] ?? date('Y-m-d')); ?>" required>
            </div>
            <div>
                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" step="0.01" value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>" required>
            </div>
            <div>
                <label for="description">Description (Optional):</label>
                <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($_POST['description'] ?? ''); ?>">
            </div>
            <button type="submit">Record Payment</button>
        </form>
    </div>

    <div class="payment-history-section">
        <h3>Payment History</h3>
        <?php if (!empty($payments)): ?>
            <div class="table-responsive">
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Tenant</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($payment['payment_date']); ?></td>
                                <td><?php echo htmlspecialchars($payment['tenant_name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['property_name']); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format($payment['amount'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($payment['description'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="no-payments">No payments recorded yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
