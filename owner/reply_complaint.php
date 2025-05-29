<?php
require_once '../includes/header.php';
require_once '../config/db.php';

require_role('owner');

$complaint_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the complaint details
$complaint_result = execute_query($conn, "SELECT c.*, t.name AS tenant_name, p.name AS property_name
                                         FROM complaints c
                                         JOIN tenants t ON c.tenant_id = t.tenant_id
                                         JOIN properties p ON c.property_id = p.property_id
                                         WHERE c.complaint_id = $complaint_id");
$complaint = fetch_array($complaint_result);

if (!$complaint) {
    echo "<p class='error'>Complaint not found.</p>";
    require_once '../includes/footer.php';
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_message = escape_string($conn, $_POST['reply']);
    $reply_date = date('Y-m-d H:i:s');

    $update_sql = "UPDATE complaints SET reply = '$reply_message', reply_date = '$reply_date' WHERE complaint_id = $complaint_id";
    if (execute_query($conn, $update_sql)) {
        $success = 'Reply submitted successfully. <a href="complaints.php">Back to Complaints</a>';
        // Refetch the complaint to show the reply
        $complaint_result = execute_query($conn, "SELECT c.*, t.name AS tenant_name, p.name AS property_name
                                             FROM complaints c
                                             JOIN tenants t ON c.tenant_id = t.tenant_id
                                             JOIN properties p ON c.property_id = p.property_id
                                             WHERE c.complaint_id = $complaint_id");
        $complaint = fetch_array($complaint_result);
    } else {
        $error = 'Error submitting reply.';
    }
}
?>

<h2>Reply to Complaint</h2>

<p><strong>Tenant:</strong> <?php echo htmlspecialchars($complaint['tenant_name']); ?></p>
<p><strong>Property:</strong> <?php echo htmlspecialchars($complaint['property_name']); ?></p>
<p><strong>Date:</strong> <?php echo htmlspecialchars($complaint['complaint_date']); ?></p>
<p><strong>Subject:</strong> <?php echo htmlspecialchars($complaint['subject']); ?></p>
<p><strong>Complaint:</strong><br><?php echo nl2br(htmlspecialchars($complaint['message'])); ?></p>

<?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p class="success"><?php echo $success; ?></p>
    <?php if ($complaint['reply']): ?>
        <h3>Your Reply:</h3>
        <p><?php echo nl2br(htmlspecialchars($complaint['reply'])); ?> (<?php echo htmlspecialchars($complaint['reply_date']); ?>)</p>
    <?php endif; ?>
<?php else: ?>
    <h3>Your Reply:</h3>
    <form method="post">
        <div>
            <textarea name="reply" rows="5" style="width: 100%;"><?php echo htmlspecialchars($complaint['reply'] ?? ''); ?></textarea>
        </div>
        <button type="submit">Submit Reply</button>
        <p><a href="complaints.php">Cancel</a></p>
    </form>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>