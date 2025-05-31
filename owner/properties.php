<?php
// Enable error reporting for debugging (REMOVE IN PRODUCTION)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Adjust paths as needed based on your actual file structure
// Ensure auth.php and functions.php are loaded BEFORE header.php if header.php uses their functions.
require_once '../config/db.php';     // Database connection first
require_once '../includes/auth.php';     // Authentication functions (is_logged_in, require_role, get_user_id)
require_once '../includes/functions.php'; // General utility functions (execute_prepared_query, logActivity, etc.)
require_once '../includes/header.php';   // Header, which might use functions from auth.php

// Ensure only users with 'owner' role can access this page
require_role('owner', '../auth/login.php'); // Redirect to login if not owner

$owner_id = get_user_id();

// Log activity for accessing the properties page
if ($owner_id) {
    logActivity($conn, $owner_id, 'Viewed Properties', 'Accessed the owner\'s property list.');
} else {
    error_log("Attempt to view properties without a valid owner ID in session.");
}

// Handle adding a new rule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_rule']) && isset($_POST['property_id_add_rule']) && isset($_POST['new_rule'])) {
    $property_id_add = intval($_POST['property_id_add_rule']);
    $new_rule = trim($_POST['new_rule']);

    if ($property_id_add > 0 && !empty($new_rule)) {
        $insert_rule_sql = "INSERT INTO property_rules (property_id, rule_description) VALUES (?, ?)";
        if (execute_prepared_query($conn, $insert_rule_sql, 'is', $property_id_add, $new_rule)) {
            $_SESSION['message'] = "Rule added successfully.";
            $_SESSION['message_type'] = 'success';
            header("Location: properties.php");
            exit();
        } else {
            //$_SESSION['message'] = "Failed to add rule.";
            //$_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = "Invalid property or rule description.";
        $_SESSION['message_type'] = 'warning';
    }
    header("Location: properties.php");
    exit();
}

// Handle deleting a rule
if (isset($_GET['delete_rule']) && isset($_GET['rule_id'])) {
    $rule_id_to_delete = intval($_GET['rule_id']);
    if ($rule_id_to_delete > 0) {
        $delete_rule_sql = "DELETE FROM property_rules WHERE rule_id = ?";
        if (execute_prepared_query($conn, $delete_rule_sql, 'i', $rule_id_to_delete)) {
            $_SESSION['message'] = "Rule deleted successfully.";
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = "Failed to delete rule.";
            $_SESSION['message_type'] = 'danger';
        }
    }
    header("Location: properties.php");
    exit();
}

// Fetch properties owned by the current owner using a prepared statement
$properties_sql = "SELECT property_id, name, address, price, currency, image_path FROM properties WHERE owner_id = ?";
$properties_result = execute_prepared_query($conn, $properties_sql, 'i', $owner_id);
$properties = [];
if ($properties_result) {
    $properties = fetch_all($properties_result);
}

// Fetch all rules for the fetched properties
$property_rules = [];
if (!empty($properties)) {
    $property_ids = array_column($properties, 'property_id');
    // Create placeholders for the IN clause (e.g., ?, ?, ?)
    $placeholders = implode(',', array_fill(0, count($property_ids), '?'));
    $sql_rules = "SELECT rule_id, property_id, rule_description FROM property_rules WHERE property_id IN ($placeholders) ORDER BY property_id ASC, rule_id ASC";
    // Create types string for binding (e.g., 'iii' for three integers)
    $types = str_repeat('i', count($property_ids));

    $rules_result = execute_prepared_query($conn, $sql_rules, $types, ...$property_ids);

    if ($rules_result) {
        while ($rule = fetch_array($rules_result)) {
            $property_rules[$rule['property_id']][] = ['id' => $rule['rule_id'], 'description' => $rule['rule_description']];
        }
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
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* Use 1fr for flexible width */
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

    .property-tile .rules-list {
        margin-top: 10px;
        padding-left: 20px;
        color: #666;
        font-size: 0.9em;
    }

    .property-tile .rules-list li {
        margin-bottom: 3px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .property-tile .rules-list li span {
        flex-grow: 1;
    }

    .property-tile .rules-list li a.delete-rule-btn {
        color: #dc3545;
        text-decoration: none;
        margin-left: 10px;
        font-size: 0.8em;
    }

    .property-tile .rules-list li a.delete-rule-btn:hover {
        text-decoration: underline;
    }

    .property-tile .add-rule-form {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #eee;
    }

    .property-tile .add-rule-form label {
        display: block;
        margin-bottom: 5px;
        font-size: 0.9em;
        color: #333;
    }

    .property-tile .add-rule-form input[type="text"] {
        width: calc(100% - 22px);
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 0.9em;
    }

    .property-tile .add-rule-form button[type="submit"] {
        background-color: #28a745;
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9em;
        transition: background-color 0.3s ease;
    }

    .property-tile .add-rule-form button[type="submit"]:hover {
        background-color: #218838;
    }

    .property-tile .actions {
        margin-top: auto; /* Pushes actions to the bottom */
        padding-top: 15px;
        border-top: 1px solid #eee;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .property-tile .actions a {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 6px 10px;
        border-radius: 4px;
        text-decoration: none;
        transition: background-color 0.3s ease;
        font-size: 0.9em;
    }

    .property-tile .actions a.delete-btn {
        background-color: #dc3545; /* Red for delete */
    }
    .property-tile .actions a.delete-btn:hover {
        background-color: #c82333;
    }

    .property-tile .actions a:hover {
        background-color: #0056b3;
    }

    .no-properties {
        text-align: center;
        color: #777;
        font-style: italic;
        padding: 30px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-top: 20px;
    }

    .message-container {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 5px;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .warning {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
</style>

<div class="properties-page">
    <h2>Your Properties</h2>

    <p><a href="add_property.php">Add New Property</a></p>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message-container <?php echo $_SESSION['message_type']; ?>">
            <?php echo $_SESSION['message']; ?>
        </div>
        <?php unset($_SESSION['message']); ?>
        <?php unset($_SESSION['message_type']); ?>
    <?php endif; ?>

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
                    <?php if (!empty($property_rules[$property['property_id']])): ?>
                        <p><strong>Rules:</strong></p>
                        <ul class="rules-list">
                            <?php foreach ($property_rules[$property['property_id']] as $rule): ?>
                                <li>
                                    <span><?php echo htmlspecialchars($rule['description']); ?></span>
                                    <a href="?delete_rule=1&rule_id=<?php echo $rule['id']; ?>" class="delete-rule-btn" onclick="return confirm('Are you sure you want to delete this rule?');">Delete</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No rules defined for this property.</p>
                    <?php endif; ?>

                    <div class="add-rule-form">
                        <label for="new_rule_<?php echo $property['property_id']; ?>">Add New Rule:</label>
                        <form method="post">
                            <input type="hidden" name="add_rule">
                            <input type="hidden" name="property_id_add_rule" value="<?php echo $property['property_id']; ?>">
                            <input type="text" id="new_rule_<?php echo $property['property_id']; ?>" name="new_rule" placeholder="Enter rule here" required>
                            <button type="submit">Add Rule</button>
                        </form>
                    </div>

                    <div class="actions">
                        <a href="edit_property.php?id=<?php echo $property['property_id']; ?>">Edit</a>
                        <a href="delete_property.php?id=<?php echo $property['property_id']; ?>" onclick="return confirm('Are you sure you want to delete this property?');" class="delete-btn">Delete</a>
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