<?php
session_start();
require_once '../includes/header.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

require_role('owner');

$owner_id = get_user_id();

$complaints_result = execute_query($conn, "SELECT c.*, t.name AS tenant_name, p.name AS property_name
                                          FROM complaints c
                                          JOIN tenants t ON c.tenant_id = t.tenant_id
                                          JOIN properties p ON c.property_id = p.property_id
                                          WHERE p.owner_id = $owner_id
                                          ORDER BY c.complaint_date DESC");
$complaints = [];
if ($complaints_result) {
    while ($row = fetch_array($complaints_result)) {
        $complaints[] = $row;
    }
}
?>

<style>
    .complaints-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        padding: 30px;
    }

    .complaints-page h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 20px;
    }

    .complaints-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .complaints-table th, .complaints-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .complaints-table thead {
        background-color: #f8f9fa;
        color: #333;
    }

    .complaints-table tbody tr:last-child td {
        border-bottom: none;
    }

    .complaints-table td.actions a {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 8px 12px;
        border-radius: 4px;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .complaints-table td.actions a:hover {
        background-color: #0056b3;
    }

    .no-complaints {
        text-align: center;
        color: #777;
        font-style: italic;
    }
</style>

<div class="complaints-page">
    <h2>Complaints</h2>

    <?php if (!empty($complaints)): ?>
        <table class="complaints-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Reply</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $complaint): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($complaint['complaint_date']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['tenant_name']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['property_name']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($complaint['message'])); ?></td>
                        <td><?php echo $complaint['reply'] ? htmlspecialchars($complaint['reply']) : '<span style="color: orange;">Pending</span>'; ?></td>
                        <td class="actions">
                            <a href="reply_complaint.php?id=<?php echo $complaint['complaint_id']; ?>">
                                <?php echo $complaint['reply'] ? 'View/Reply' : 'Reply'; ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-complaints">No complaints yet.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>