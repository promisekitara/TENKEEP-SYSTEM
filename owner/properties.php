<?php
require_once '../includes/header.php';
require_once '../config/db.php';
require_role('owner');

$owner_id = get_user_id();

$properties_result = execute_query($conn, "SELECT * FROM properties WHERE owner_id = $owner_id");
$properties = [];
if ($properties_result) {
    while ($row = fetch_array($properties_result)) {
        $properties[] = $row;
    }
}
?>

<style>
    .properties-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        padding: 20px;
    }

    .properties-page h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 20px;
    }

    .properties-page p a {
        display: inline-block;
        background-color: #28a745;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        text-decoration: none;
        margin-bottom: 20px;
        transition: background-color 0.3s ease;
    }

    .properties-page p a:hover {
        background-color: #218838;
    }

    .properties-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, auto)); /* Adjust minmax for image */
        gap: 20px;
    }

    .property-tile {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 15px;
        display: flex;
        flex-direction: column;
    }

    .property-tile .image-container {
        width: 100%;
        height: 200px; /* Adjust as needed */
        overflow: hidden;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .property-tile .image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .property-tile h3 {
        color: #3498db;
        margin-top: 0;
        margin-bottom: 8px;
    }

    .property-tile p {
        color: #555;
        margin-bottom: 6px;
    }

    .property-tile .actions {
        margin-top: 10px;
    }

    .property-tile .actions a {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 6px 10px;
        border-radius: 4px;
        text-decoration: none;
        margin-right: 5px;
        transition: background-color 0.3s ease;
        font-size: 0.9em;
    }

    .property-tile .actions a:hover {
        background-color: #0056b3;
    }

    .no-properties {
        text-align: center;
        color: #777;
        font-style: italic;
    }

    /* Optional: Table view styles remain largely the same */
    .properties-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .properties-table th,
    .properties-table td {
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .properties-table thead {
        background-color: #f8f9fa;
        color: #333;
    }

    .properties-table tbody tr:last-child td {
        border-bottom: none;
    }

    .properties-table .actions a {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 6px 10px;
        border-radius: 4px;
        text-decoration: none;
        margin-right: 5px;
        transition: background-color 0.3s ease;
        font-size: 0.9em;
    }

    .properties-table .actions a:hover {
        background-color: #0056b3;
    }
</style>

<div class="properties-page">
    <h2>Your Properties</h2>

    <p><a href="add_property.php">Add New Property</a></p>

    <?php if (!empty($properties)): ?>
        <div class="properties-grid">
            <?php foreach ($properties as $property): ?>
                <div class="property-tile">
                    <?php if (!empty($property['image_path'])): ?>
                        <div class="image-container">
                            <img src="<?php echo htmlspecialchars($property['image_path']); ?>" alt="<?php echo htmlspecialchars($property['name']); ?>">
                        </div>
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($property['name']); ?></h3>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($property['address']); ?></p>
                    <p><strong>Price:</strong> <?php echo htmlspecialchars(number_format($property['price'], 2)); ?>
                        <?php if (!empty($property['currency'])): ?>
                            <?php echo htmlspecialchars($property['currency']); ?>
                        <?php endif; ?>
                    </p>
                    <div class="actions">
                        <a href="edit_property.php?id=<?php echo $property['property_id']; ?>">Edit</a>
                        <a href="delete_property.php?id=<?php echo $property['property_id']; ?>" onclick="return confirm('Are you sure you want to delete this property?');">Delete</a>
                        <a href="view_property_details.php?id=<?php echo $property['property_id']; ?>">Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="no-properties">No properties added yet.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>