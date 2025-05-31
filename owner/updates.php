<?php
// updates.php - List all property updates for the owner
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    redirect('../auth/login.php');
}

$owner_id = intval($_SESSION['user_id']);
$sql = "SELECT u.*, p.name AS property_name FROM updates u JOIN properties p ON u.property_id = p.property_id WHERE p.owner_id = $owner_id ORDER BY u.update_date DESC";
$result = mysqli_query($conn, $sql);
$updates = fetch_all($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Updates - XTenKeep</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container my-5">
    <h1 class="mb-4">Property Updates</h1>
    <a href="add_update.php" class="btn btn-primary mb-3">Add New Update</a>
    <?php if (count($updates) > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Property</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($updates as $i => $update): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($update['property_name']) ?></td>
                            <td><?= htmlspecialchars($update['title']) ?></td>
                            <td><?= htmlspecialchars($update['description']) ?></td>
                            <td><?= htmlspecialchars($update['update_date']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No updates found for your properties.</div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>