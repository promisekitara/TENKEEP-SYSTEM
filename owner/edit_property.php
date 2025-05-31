<?php
require_once '../includes/header.php';
require_once '../config/db.php';
require_role('owner');

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
    echo "<p class='error'>Property not found or you do not have permission to edit it.</p>";
    require_once '../includes/footer.php';
    exit();
}

$property = fetch_array($result);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $price = $_POST['price'];
    // Add more fields as necessary

    $query = "UPDATE properties SET name = ?, address = ?, price = ?, rules = ? WHERE property_id = ? AND owner_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ssdssi", $name, $address, $price, $rules, $property_id, $owner_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "<p class='success'>Property updated successfully! <a href='properties.php'>Back to Properties</a></p>";
        // Optionally redirect: header("Location: properties.php");
        $result = execute_query($conn, "SELECT * FROM properties WHERE property_id = $property_id AND owner_id = $owner_id");
        $property = fetch_array($result); // Refetch updated data
    } else {
        echo "<p class='error'>Error updating property: " . mysqli_error($conn) . "</p>";
    }
    mysqli_stmt_close($stmt);
}
?>

<style>
    .edit-property-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        padding: 20px;
    }

    .edit-property-page h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 20px;
    }

    .edit-property-page form {
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        max-width: 600px;
        margin: 0 auto;
    }

    .edit-property-page label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: bold;
    }

    .edit-property-page input[type="text"],
    .edit-property-page input[type="number"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .edit-property-page button[type="submit"] {
        background-color: #007bff;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
        transition: background-color 0.3s ease;
    }

    .edit-property-page button[type="submit"]:hover {
        background-color: #0056b3;
    }

    .edit-property-page .error {
        color: red;
        margin-bottom: 10px;
        border: 1px solid red;
        padding: 10px;
        background-color: #ffe0e0;
        border-radius: 4px;
    }

    .edit-property-page .success {
        color: green;
        margin-bottom: 10px;
        border: 1px solid green;
        padding: 10px;
        background-color: #e0ffe0;
        border-radius: 4px;
    }
</style>

<div class="edit-property-page">
    <h2>Edit Property</h2>

    <form method="post">
        <input type="hidden" name="property_id" value="<?php echo htmlspecialchars($property['property_id']); ?>">

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($property['name']); ?>" required><br>

        <label for="address">Address:</label>
        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($property['address']); ?>" required><br>

        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" value="<?php echo htmlspecialchars($property['price']); ?>" required><br>

        <label for="rules">Property Rules:</label>
<textarea id="rules" name="rules" rows="4" cols="50"><?php echo htmlspecialchars($property['rules']); ?></textarea><br>
        <button type="submit">Update Property</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>