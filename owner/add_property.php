<?php
require_once '../includes/header.php';
require_once '../config/db.php';
require_once '../includes/functions.php';
require_role('owner');

$error = '';
$success = '';
$allowed_currencies = ['USD', 'EUR', 'UGX', 'GBP', 'JPY'];
$allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
$upload_directory = '../uploads/'; // Make sure this directory exists and is writable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = escape_string($conn, $_POST['name']);
    $address = escape_string($conn, $_POST['address']);
    $price = escape_string($conn, $_POST['price']);
    $currency = escape_string($conn, $_POST['currency']);
    $owner_id = get_user_id();

    if (empty($name) || empty($address) || empty($price) || empty($currency)) {
        $error = 'All text fields and currency are required.';
    } elseif (!in_array($currency, $allowed_currencies)) {
        $error = 'Invalid currency selected.';
    } else {
        $image_path = null;
        if (!empty($_FILES['image']['name'])) {
            if (in_array($_FILES['image']['type'], $allowed_image_types)) {
                $unique_filename = uniqid() . '_' . basename($_FILES['image']['name']);
                $target_path = $upload_directory . $unique_filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                    $image_path = escape_string($conn, $target_path);
                } else {
                    $error .= "Error uploading image. ";
                }
            } else {
                $error .= "Invalid image format. Only JPEG, PNG, and GIF are allowed. ";
            }
        }

       $sql = "INSERT INTO properties (name, address, price, currency, rules, owner_id) VALUES ('$name', '$address', '$price', '$currency', '$rules', '$owner_id')";
        if (execute_query($conn, $sql)) {
            $success = 'Property added successfully. <a href="properties.php">View Properties</a>';
        } else {
            $error .= 'Error adding property details to the database.';
        }
    }
}
?>

<style>
    /* Existing styles remain the same, adding a few for file upload */
    .add-property-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        padding: 30px;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        gap: 30px;
    }

    .floating-image-left,
    .floating-image-right {
        flex: 0 0 auto;
        max-width: 200px;
        height: auto;
    }

    .floating-image-left img,
    .floating-image-right img {
        display: block;
        width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .main-content {
        flex: 1;
        background-color: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        max-width: 600px;
    }

    .main-content h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 25px;
    }

    .error-message,
    .success-message {
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

    .add-property-form div {
        margin-bottom: 15px;
    }

    .add-property-form label {
        display: block;
        margin-bottom: 8px;
        color: #555;
        font-weight: bold;
    }

    .add-property-form input[type="text"],
    .add-property-form input[type="number"],
    .add-property-form textarea,
    .add-property-form select,
    .add-property-form input[type="file"] {
        width: calc(100% - 22px);
        padding: 10px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1em;
    }

    .add-property-form textarea {
        min-height: 80px;
        resize: vertical;
    }

    .add-property-form button {
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

    .add-property-form button:hover {
        background-color: #218838;
    }

    .add-property-page p.success a {
        color: #198754;
        text-decoration: underline;
        font-weight: bold;
    }
</style>

<div class="add-property-page">
    <div class="floating-image-left">
        <img src="../assets/images/pexels-vladbagacian-1212053.jpg" alt="Property Image Left">
    </div>

    <div class="main-content">
        <h2>Add New Property</h2>

        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p class="success-message"><?php echo $success; ?></p>
        <?php else: ?>
            <form method="post" class="add-property-form" enctype="multipart/form-data">
                <div>
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div>
                    <label for="address">Address:</label>
                    <textarea id="address" name="address" required></textarea>
                </div>
                <div>
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                <div>
                    <label for="currency">Currency:</label>
                    <select id="currency" name="currency" required>
                        <option value="">Select Currency</option>
                        <?php foreach ($allowed_currencies as $currency_code): ?>
                            <option value="<?php echo $currency_code; ?>"><?php echo $currency_code; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
    <label for="rules">Property Rules:</label>
    <textarea id="rules" name="rules" rows="4" cols="50"></textarea>
</div>
               
                <button type="submit">Add Property</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="floating-image-right">
        <img src="../assets/images/pexels-lucaspezeta-2212875.jpg" alt="Property Image Right">
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>