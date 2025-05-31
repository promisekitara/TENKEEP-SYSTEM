<?php
require_once '../includes/header.php';
require_once '../config/db.php';
require_role('owner');

if (!isset($_GET['property_id']) || !is_numeric($_GET['property_id'])) {
    // Handle the case where property_id is missing or invalid
    echo "<p class='error'>Invalid property ID.</p>";
    require_once '../includes/footer.php';
    exit();
}

$property_id = $_GET['property_id'];

// Fetch property details (optional, but good to display)
$property_result = execute_query($conn, "SELECT name FROM properties WHERE property_id = $property_id AND owner_id = " . get_user_id());
if (!$property_result || num_rows($property_result) === 0) {
    echo "<p class='error'>Property not found or you do not own this property.</p>";
    require_once '../includes/footer.php';
    exit();
}
$property = fetch_array($property_result);
?>

<style>
    .add-update-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        padding: 20px;
    }

    .add-update-page h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 20px;
    }

    .add-update-form {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: 0 auto;
    }

    .add-update-form label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: bold;
    }

    .add-update-form textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        font-family: inherit;
        font-size: inherit;
    }

    .add-update-form button {
        background-color: #007bff;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1em;
        transition: background-color 0.3s ease;
    }

    .add-update-form button:hover {
        background-color: #0056b3;
    }

    .error {
        color: red;
        margin-bottom: 10px;
        text-align: center;
    }

    .success {
        color: green;
        margin-bottom: 10px;
        text-align: center;
    }
</style>

<div class="add-update-page">
    <h2>Add Update for <?php echo htmlspecialchars($property['name'] ?? 'Property'); ?></h2>

    <form action="process_update.php" method="post" class="add-update-form">
        <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">

        <label for="update_text">Update:</label>
        <textarea id="update_text" name="update_text" rows="5" required></textarea>

        <button type="submit">Add Update</button>
    </form>
</div>