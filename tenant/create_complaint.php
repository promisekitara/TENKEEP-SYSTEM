<?php
require_once '../includes/header.php';
require_once '../config/db.php';

require_role('tenant');

$tenant_user_id = get_user_id();

// Fetch the tenant's property ID
$tenant_property_result = execute_query($conn, "SELECT property_id, name FROM tenants WHERE user_id = $tenant_user_id");
$tenant_property = fetch_array($tenant_property_result);

if (!$tenant_property) {
    echo "<div class='error-container'><p class='error'>Could not find your property information.</p></div>";
    require_once '../includes/footer.php';
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = escape_string($conn, $_POST['subject']);
    $message = escape_string($conn, $_POST['message']);
    $tenant_id_result = execute_query($conn, "SELECT tenant_id FROM tenants WHERE user_id = $tenant_user_id");
    $tenant_data = fetch_array($tenant_id_result);
    $tenant_id = $tenant_data['tenant_id'] ?? 0;
    $property_id = $tenant_property['property_id'];

    if (empty($subject) || empty($message)) {
        $error = 'Subject and message are required.';
    } else {
        $sql = "INSERT INTO complaints (tenant_id, property_id, subject, message) VALUES ($tenant_id, $property_id, '$subject', '$message')";
        if (execute_query($conn, $sql)) {
            $success = 'Complaint submitted successfully. <a href="complaints.php">View Complaints</a>';
        } else {
            $error = 'Error submitting complaint.';
        }
    }
}
?>

<style>
    .create-complaint-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: 600px;
        color: #333;
    }

    .create-complaint-page h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 25px;
        font-size: 2.2em;
    }

    .create-complaint-page p {
        margin-bottom: 15px;
        line-height: 1.7;
        color: #555;
        text-align: center;
    }

    .create-complaint-page strong {
        font-weight: bold;
        color: #3498db;
    }

    .error-container, .success-container {
        text-align: center;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 6px;
    }

    .error {
        color: #c0392b;
        background-color: #fdecea;
        border: 1px solid #e74c3c;
    }

    .success {
        color: #27ae60;
        background-color: #e8f8f3;
        border: 1px solid #2ecc71;
    }

    .create-complaint-form {
        background-color: #ffffff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .create-complaint-form div {
        margin-bottom: 15px;
    }

    .create-complaint-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #555;
    }

    .create-complaint-form input[type="text"],
    .create-complaint-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1em;
    }

    .create-complaint-form textarea {
        resize: vertical;
        min-height: 120px;
    }

    .create-complaint-form button {
        background-color: #3498db;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 1.1em;
        transition: background-color 0.3s ease;
    }

    .create-complaint-form button:hover {
        background-color: #2980b9;
    }

    .create-complaint-page a {
        color: #3498db;
        text-decoration: none;
        font-weight: bold;
    }

    .create-complaint-page a:hover {
        text-decoration: underline;
    }
</style>

<div class="create-complaint-page">
    <h2>Create New Complaint</h2>

    <?php if ($error): ?>
        <div class="error-container"><p class="error"><?php echo $error; ?></p></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-container"><p class="success"><?php echo $success; ?></p></div>
    <?php else: ?>
        <p>Regarding property: <strong><?php echo htmlspecialchars($tenant_property['name']); ?></strong></p>
        <div class="create-complaint-form">
            <form method="post">
                <div>
                    <label for="subject">Subject:</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div>
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit">Submit Complaint</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>