<?php
require_once '../includes/header.php';
require_once '../config/db.php';

require_role('tenant');

$tenant_user_id = get_user_id();

$complaints_result = execute_query($conn, "SELECT c.*, p.name AS property_name
                                          FROM complaints c
                                          JOIN tenants t ON c.tenant_id = t.tenant_id
                                          JOIN properties p ON c.property_id = p.property_id
                                          WHERE t.user_id = $tenant_user_id
                                          ORDER BY c.complaint_date DESC");
$complaints = [];
if ($complaints_result) {
    while ($row = fetch_array($complaints_result)) {
        $complaints[] = $row;
    }
}
?>

<style>
    .tenant-complaints-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: 90%;
    }

    .tenant-complaints-page h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 25px;
        font-size: 2.2em;
    }

    .tenant-complaints-page p {
        margin-bottom: 15px;
        line-height: 1.7;
        color: #555;
    }

    .tenant-complaints-page a {
        color: #3498db;
        text-decoration: none;
        transition: color 0.3s ease;
        font-weight: 500;
    }

    .tenant-complaints-page a:hover {
        color: #217dbb;
        text-decoration: underline;
    }

    .complaints-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .complaints-table thead {
        background-color: #3498db;
        color: white;
    }

    .complaints-table th, .complaints-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ecf0f1;
    }

    .complaints-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .complaints-table tbody tr:hover {
        background-color: #eff6ff;
    }

    .complaints-table td {
        vertical-align: top;
    }

    .no-complaints-message {
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

<div class="tenant-complaints-page">
    <h2>Your Complaints</h2>

    <p><a href="create_complaint.php">Create New Complaint</a></p>

    <?php if (!empty($complaints)): ?>
        <table class="complaints-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Property</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Reply</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $complaint): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($complaint['complaint_date']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['property_name']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                        <td><div style="max-width: 300px; overflow-wrap: break-word;"><?php echo nl2br(htmlspecialchars($complaint['message'])); ?></div></td>
                        <td><div style="max-width: 200px; overflow-wrap: break-word;"><?php echo $complaint['reply'] ? htmlspecialchars($complaint['reply']) : '<span style="color: #95a5a6;">No Reply Yet</span>'; ?></div></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-complaints-message">No complaints submitted yet.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>