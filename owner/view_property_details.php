<?php
require_once '../includes/header.php';
require_once '../config/db.php';
require_role('owner'); // Or perhaps a different role if you want other users to view details

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Handle case where ID is missing or not valid
    echo "<p class='error'>Invalid property ID.</p>";
    require_once '../includes/footer.php';
    exit();
}

$property_id = $_GET['id'];
$owner_id = get_user_id();

// Fetch the property details
$result = execute_query($conn, "SELECT * FROM properties WHERE property_id = $property_id AND owner_id = $owner_id");

if (!$result || num_rows($result) === 0) {
    // Handle case where property doesn't exist or doesn't belong to the owner
    echo "<p class='error'>Property not found or you do not have permission to view it.</p>";
    require_once '../includes/footer.php';
    exit();
}

$property = fetch_array($result);
?>

<style>
    .property-details-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        padding: 20px;
    }

    .property-details-page h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 20px;
    }

    .property-details-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: 0 auto;
    }

    .property-details-container h3 {
        color: #3498db;
        margin-top: 0;
        margin-bottom: 10px;
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
    }

    .property-details-container p {
        color: #555;
        margin-bottom: 10px;
    }

    .property-details-container strong {
        font-weight: bold;
        color: #333;
    }

    .property-details-page .back-link {
        display: inline-block;
        margin-top: 20px;
        text-decoration: none;
        color: #007bff;
        transition: color 0.3s ease;
    }

    .property-details-page .back-link:hover {
        color: #0056b3;
    }

    .property-details-page .error {
        color: red;
        margin-bottom: 10px;
        border: 1px solid red;
        padding: 10px;
        background-color: #ffe0e0;
        border-radius: 4px;
        text-align: center;
    }
</style>

<div class="property-details-page">
    <h2>Property Details</h2>

    <?php if ($property): ?>
        <div class="property-details-container">
            <h3><?php echo htmlspecialchars($property['name']); ?></h3>
            <p><strong>Property ID:</strong> <?php echo htmlspecialchars($property['property_id']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($property['address']); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars(number_format($property['price'], 2)); ?></p>
            </div>
        <p><a href="properties.php" class="back-link">Back to Properties</a></p>
    <?php else: ?>
        <p class="error">Could not retrieve property details.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>