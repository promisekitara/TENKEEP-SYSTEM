<?php
session_start();
require_once 'config/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Ensure only developers can access this page
require_role('developer');

$developer_user_id = get_user_id();

// Fetch developer's username for display
$developer_username = '';
if ($developer_user_id) {
    $developer_name_result = mysqli_query($conn, "SELECT username FROM users WHERE user_id = $developer_user_id");
    if ($developer_name_result && mysqli_num_rows($developer_name_result) > 0) {
        $developer_data = mysqli_fetch_assoc($developer_name_result);
        $developer_username = htmlspecialchars($developer_data['username']);
    }
}

// --- Handle User Freeze/Unfreeze Action ---
$freeze_success = '';
$freeze_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['freeze_user_id']) || isset($_POST['unfreeze_user_id']))) {
    $target_user_id = 0;
    $is_frozen_status = 0; // Default to unfreeze

    if (isset($_POST['freeze_user_id'])) {
        $target_user_id = intval($_POST['freeze_user_id']);
        $is_frozen_status = 1; // Set to frozen
    } elseif (isset($_POST['unfreeze_user_id'])) {
        $target_user_id = intval($_POST['unfreeze_user_id']);
        $is_frozen_status = 0; // Set to unfrozen
    }

    if ($target_user_id > 0) {
        // Prevent developers from freezing/unfreezing themselves
        if ($target_user_id == $developer_user_id) {
            $freeze_error = "You cannot freeze or unfreeze your own account.";
        } else {
            // Call a new function to update user status
            if (toggleUserFreezeStatus($conn, $target_user_id, $is_frozen_status)) {
                $status_text = $is_frozen_status ? 'frozen' : 'unfrozen';
                $freeze_success = "User account (ID: {$target_user_id}) has been successfully {$status_text}.";
            } else {
                $freeze_error = "Failed to update user account status for ID: {$target_user_id}.";
            }
        }
    } else {
        $freeze_error = "Invalid user ID provided for freeze/unfreeze action.";
    }
}
// --- End Handle User Freeze/Unfreeze Action ---


// Fetch all activity logs
$logs = [];
$sql = "SELECT al.*, u.username FROM activity_logs al JOIN users u ON al.user_id = u.user_id ORDER BY al.timestamp DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $logs[] = $row;
    }
} else {
    error_log("Error fetching activity logs: " . mysqli_error($conn));
}

// Calculate dashboard statistics
$total_logs = count($logs);
$unique_users_logged = count(array_unique(array_column($logs, 'user_id')));

// Fetch total users by role
$user_roles = [];
$users_by_role_sql = "SELECT role, COUNT(user_id) as count FROM users GROUP BY role";
$users_by_role_result = mysqli_query($conn, $users_by_role_sql);
if ($users_by_role_result) {
    while ($row = mysqli_fetch_assoc($users_by_role_result)) {
        $user_roles[$row['role']] = $row['count'];
    }
}

// Fetch recent logins (last 5)
$recent_logins = [];
$recent_logins_sql = "SELECT al.timestamp, u.username FROM activity_logs al JOIN users u ON al.user_id = u.user_id WHERE al.action LIKE '%Login%' ORDER BY al.timestamp DESC LIMIT 5";
$recent_logins_result = mysqli_query($conn, $recent_logins_sql);
if ($recent_logins_result) {
    while ($row = mysqli_fetch_assoc($recent_logins_result)) {
        $recent_logins[] = $row;
    }
}

// Fetch complaint status counts
$pending_complaints = 0;
$replied_complaints = 0;
$complaint_status_sql = "SELECT COUNT(*) as count, CASE WHEN reply IS NULL OR reply = '' THEN 'pending' ELSE 'replied' END as status FROM complaints GROUP BY status";
$complaint_status_result = mysqli_query($conn, $complaint_status_sql);
if ($complaint_status_result) {
    while ($row = mysqli_fetch_assoc($complaint_status_result)) {
        if ($row['status'] == 'pending') {
            $pending_complaints = $row['count'];
        } else {
            $replied_complaints = $row['count'];
        }
    }
}

// Fetch total number of properties
$total_properties = 0;
$properties_count_sql = "SELECT COUNT(*) as count FROM properties";
$properties_count_result = mysqli_query($conn, $properties_count_sql);
if ($properties_count_result && $row = mysqli_fetch_assoc($properties_count_result)) {
    $total_properties = $row['count'];
}

// Fetch total number of owners
$total_owners = 0;
$owners_count_sql = "SELECT COUNT(*) as count FROM users WHERE role = 'owner'";
$owners_count_result = mysqli_query($conn, $owners_count_sql);
if ($owners_count_result && $row = mysqli_fetch_assoc($owners_count_result)) {
    $total_owners = $row['count'];
}

// Fetch total number of tenants
$total_tenants = 0;
$tenants_count_sql = "SELECT COUNT(*) as count FROM tenants";
$tenants_count_result = mysqli_query($conn, $tenants_count_sql);
if ($tenants_count_result && $row = mysqli_fetch_assoc($tenants_count_result)) {
    $total_tenants = $row['count'];
}


// Fetch detailed owner, property, and tenant information
$owner_details = [];
$owners_sql = "SELECT user_id, username FROM users WHERE role = 'owner'";
$owners_result = mysqli_query($conn, $owners_sql);
if ($owners_result) {
    while ($owner_row = mysqli_fetch_assoc($owners_result)) {
        $owner_details[$owner_row['user_id']] = [
            'username' => htmlspecialchars($owner_row['username']),
            'properties' => []
        ];
    }
}

// Fetch all properties and assign to owners
if (!empty($owner_details)) {
    $property_sql = "SELECT property_id, name, address, price, owner_id FROM properties WHERE owner_id IN (" . implode(',', array_keys($owner_details)) . ")";
    $property_result = mysqli_query($conn, $property_sql);
    if ($property_result) {
        while ($property_row = mysqli_fetch_assoc($property_result)) {
            $owner_id = $property_row['owner_id'];
            if (isset($owner_details[$owner_id])) {
                $owner_details[$owner_id]['properties'][$property_row['property_id']] = [
                    'name' => htmlspecialchars($property_row['name']),
                    'address' => htmlspecialchars($property_row['address']),
                    'price' => htmlspecialchars(number_format($property_row['price'], 2)),
                    'tenants' => []
                ];
            }
        }
    }
}

// Fetch all tenants and assign to properties
$all_property_ids = [];
foreach ($owner_details as $owner) {
    foreach ($owner['properties'] as $prop_id => $prop_data) {
        $all_property_ids[] = $prop_id;
    }
}

if (!empty($all_property_ids)) {
    // Using prepared statement for security
    $placeholders = implode(',', array_fill(0, count($all_property_ids), '?'));
    $tenants_sql = "SELECT tenant_id, name, contact_number, property_id FROM tenants WHERE property_id IN ($placeholders)";
    $stmt = mysqli_prepare($conn, $tenants_sql);
    if ($stmt) {
        // Dynamically bind parameters
        $types = str_repeat('i', count($all_property_ids));
        mysqli_stmt_bind_param($stmt, $types, ...$all_property_ids);
        mysqli_stmt_execute($stmt);
        $tenants_result = mysqli_stmt_get_result($stmt);

        if ($tenants_result) {
            while ($tenant_row = mysqli_fetch_assoc($tenants_result)) {
                $property_id = $tenant_row['property_id'];
                foreach ($owner_details as $owner_id => &$owner_data) { // Use & for reference to modify in place
                    if (isset($owner_data['properties'][$property_id])) {
                        $owner_data['properties'][$property_id]['tenants'][] = [
                            'name' => htmlspecialchars($tenant_row['name']),
                            'contact_number' => htmlspecialchars($tenant_row['contact_number'])
                        ];
                        break; // Found the property, move to next tenant
                    }
                }
                unset($owner_data); // Unset the reference
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("Failed to prepare statement for fetching tenants: " . mysqli_error($conn));
    }
}

// Fetch all users for User Management section, including their frozen status
$all_users = [];
$all_users_sql = "SELECT user_id, username, role, is_frozen FROM users ORDER BY username ASC";
$all_users_result = mysqli_query($conn, $all_users_sql);
if ($all_users_result) {
    while ($user_row = mysqli_fetch_assoc($all_users_result)) {
        $all_users[] = $user_row;
    }
} else {
    error_log("Error fetching all users: " . mysqli_error($conn));
}

// Fetch pages/actions with errors
$error_logs = [];
// Assuming 'action' or 'details' might contain keywords like 'Error', 'Failed', 'Exception'
$error_logs_sql = "SELECT timestamp, action, details FROM activity_logs WHERE action LIKE '%Error%' OR action LIKE '%Failed%' OR details LIKE '%Error%' OR details LIKE '%Failed%' ORDER BY timestamp DESC LIMIT 10";
$error_logs_result = mysqli_query($conn, $error_logs_sql);
if ($error_logs_result) {
    while ($row = mysqli_fetch_assoc($error_logs_result)) {
        $error_logs[] = $row;
    }
} else {
    error_log("Error fetching error logs: " . mysqli_error($conn));
}


// Prepare data for D3.js charts
$chart_data_user_roles = [];
foreach ($user_roles as $role => $count) {
    $chart_data_user_roles[] = ['label' => ucfirst($role) . 's', 'value' => $count];
}

$chart_data_complaint_status = [
    ['label' => 'Pending Complaints', 'value' => $pending_complaints],
    ['label' => 'Replied Complaints', 'value' => $replied_complaints]
];

// Calculate total payments for chart
$total_payments_amount = 0;
$total_expected_rent = 0; // You would need to calculate this from properties and tenants
// For demonstration, let's assume a fixed expected rent or fetch it from properties
$total_expected_rent_sql = "SELECT SUM(price) as total_price FROM properties";
$total_expected_rent_result = mysqli_query($conn, $total_expected_rent_sql);
if ($total_expected_rent_result && $row = mysqli_fetch_assoc($total_expected_rent_result)) {
    $total_expected_rent = $row['total_price'];
}

$total_payments_amount_sql = "SELECT SUM(amount) as total_paid FROM payments";
$total_payments_amount_result = mysqli_query($conn, $total_payments_amount_sql);
if ($total_payments_amount_result && $row = mysqli_fetch_assoc($total_payments_amount_result)) {
    $total_payments_amount = $row['total_paid'];
}

$chart_data_payment_progress = [
    ['label' => 'Collected Payments', 'value' => $total_payments_amount],
    ['label' => 'Remaining Expected Rent', 'value' => max(0, $total_expected_rent - $total_payments_amount)] // Ensure non-negative
];

// Fetch user registration data for trend chart
$user_registration_data = [];
$registration_sql = "SELECT DATE_FORMAT(registration_date, '%Y-%m-01') as month, COUNT(user_id) as count FROM users GROUP BY month ORDER BY month ASC";
$registration_result = mysqli_query($conn, $registration_sql);
if ($registration_result) {
    $cumulative_count = 0;
    while ($row = mysqli_fetch_assoc($registration_result)) {
        $cumulative_count += $row['count'];
        $user_registration_data[] = [
            'date' => $row['month'],
            'new_users' => $row['count'],
            'cumulative_users' => $cumulative_count
        ];
    }
} else {
    error_log("Error fetching user registration data: " . mysqli_error($conn));
}


// Convert PHP arrays to JSON for JavaScript
$json_chart_data_user_roles = json_encode($chart_data_user_roles);
$json_chart_data_complaint_status = json_encode($chart_data_complaint_status);
$json_chart_data_payment_progress = json_encode($chart_data_payment_progress);
$json_user_registration_data = json_encode($user_registration_data);
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        background-color: #f8f9fa;
        color: #343a40;
    }

    .container-fluid {
        display: flex;
        min-height: 100vh;
    }

    .left-pane {
        width: 250px;
        background-color: #f0f2f5;
        padding: 20px;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        flex-shrink: 0;
        position: fixed; /* Make it fixed */
        height: 100vh; /* Take full viewport height */
        overflow-y: auto; /* Enable scrolling if content overflows */
        top: 0;
        left: 0;
    }

    .main-content-area {
        flex-grow: 1;
        padding: 30px;
        margin-left: 250px; /* Offset by the width of the fixed left pane */
        max-width: calc(100% - 250px); /* Adjust based on left-pane width */
        overflow-x: hidden; /* Prevent horizontal scroll */
    }

    .activity-log-page {
        background-color: #f8f9fa;
        padding: 0; /* Remove padding here as it's handled by main-content-area */
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        margin: 0 auto;
        max-width: 100%; /* Take full width of main-content-area */
    }

    .activity-log-page h2 {
        color: #2c3e50;
        text-align: center;
        margin-bottom: 25px;
        font-size: 2.2em;
    }

    .welcome-message {
        text-align: center;
        font-size: 1.2em;
        color: #34495e;
        margin-bottom: 30px;
    }

    .dashboard-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        padding: 20px;
        text-align: center;
        transition: transform 0.2s ease-in-out;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card h3 {
        color: #007bff;
        margin-top: 0;
        margin-bottom: 10px;
        font-size: 1.2em;
    }

    .stat-card p {
        font-size: 1.8em;
        font-weight: bold;
        color: #2c3e50;
        margin: 0;
    }

    .activity-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
    }

    .activity-table th, .activity-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }

    .activity-table th {
        background-color: #007bff;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9em;
    }

    .activity-table tbody tr:nth-child(even) {
        background-color: #f6f6f6;
    }

    .activity-table tbody tr:hover {
        background-color: #e9f5ff;
        transition: background-color 0.2s ease;
    }

    .activity-table td.details-col {
        max-width: 350px;
        word-wrap: break-word;
    }

    .no-logs-message {
        text-align: center;
        color: #7f8c8d;
        font-style: italic;
        padding: 20px;
        border: 1px solid #ecf0f1;
        border-radius: 8px;
        background-color: #f0f0f0;
        margin-top: 20px;
    }

    .dashboard-section {
        background-color: #fff;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .dashboard-section h3 {
        color: #007bff;
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 1.8em;
        text-align: center;
    }

    .recent-logins-list {
        list-style: none;
        padding: 0;
    }

    .recent-logins-list li {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .recent-logins-list li:last-child {
        border-bottom: none;
    }

    .recent-logins-list .username {
        font-weight: bold;
        color: #34495e;
    }

    .recent-logins-list .timestamp {
        font-size: 0.9em;
        color: #7f8c8d;
    }

    .role-counts {
        display: flex;
        justify-content: space-around;
        flex-wrap: wrap;
        gap: 15px;
    }

    .role-item {
        background-color: #f0f8ff;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        text-align: center;
        flex: 1;
        min-width: 150px;
    }

    .role-item h4 {
        margin: 0 0 5px 0;
        color: #0056b3;
        font-size: 1.1em;
    }

    .role-item p {
        font-size: 1.5em;
        font-weight: bold;
        color: #2c3e50;
        margin: 0;
    }

    details {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        margin-bottom: 15px;
        padding: 15px;
        border: 1px solid #e0e0e0;
    }

    summary {
        font-weight: bold;
        cursor: pointer;
        outline: none;
        padding: 5px 0;
        color: #007bff;
        font-size: 1.3em;
        display: flex;
        align-items: center;
    }

    summary::marker {
        content: '';
    }

    summary::-webkit-details-marker {
        display: none;
    }

    summary:before {
        content: '▶';
        margin-right: 10px;
        font-size: 0.8em;
        transition: transform 0.2s ease;
    }

    details[open] summary:before {
        content: '▼';
        transform: rotate(90deg);
    }

    .details-content {
        padding-top: 10px;
        border-top: 1px solid #eee;
        margin-top: 10px;
    }

    .property-details-summary {
        font-weight: bold;
        cursor: pointer;
        color: #28a745;
        font-size: 1.1em;
        margin-top: 10px;
        display: flex;
        align-items: center;
    }

    .property-details-summary::before {
        content: '▶';
        margin-right: 8px;
        font-size: 0.7em;
        transition: transform 0.2s ease;
    }

    details[open] .property-details-summary::before {
        content: '▼';
        transform: rotate(90deg);
    }

    .property-info-content {
        padding-left: 20px;
        padding-top: 5px;
    }

    .tenant-list-nested {
        list-style: none;
        padding-left: 20px;
        margin-top: 10px;
    }

    .tenant-list-nested li {
        margin-bottom: 5px;
        color: #555;
    }

    .user-management-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
    }

    .user-management-table th, .user-management-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e0e0e0;
    }

    .user-management-table th {
        background-color: #007bff;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9em;
    }

    .user-management-table tbody tr:nth-child(even) {
        background-color: #f6f6f6;
    }

    .user-management-table tbody tr:hover {
        background-color: #e9f5ff;
        transition: background-color 0.2s ease;
    }

    .user-status-frozen {
        color: #dc3545;
        font-weight: bold;
    }

    .user-status-active {
        color: #28a745;
        font-weight: bold;
    }

    .action-button {
        padding: 8px 12px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s ease;
        border: none;
    }

    .freeze-button {
        background-color: #ffc107;
        color: #333;
    }

    .freeze-button:hover {
        background-color: #e0a800;
    }

    .unfreeze-button {
        background-color: #17a2b8;
        color: #fff;
    }

    .unfreeze-button:hover {
        background-color: #138496;
    }

    .message-container {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
        font-weight: bold;
    }

    .success-message {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .error-message {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .chart-container {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        gap: 30px;
        margin-top: 20px;
    }

    .chart-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        padding: 20px;
        text-align: center;
        flex: 1;
        min-width: 300px;
        max-width: 45%;
        box-sizing: border-box;
    }

    .chart-card h4 {
        color: #007bff;
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.5em;
    }

    .chart-legend {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 15px;
        font-size: 0.9em;
    }

    .legend-item {
        display: flex;
        align-items: center;
        margin: 0 10px 5px 0;
    }

    .legend-color {
        width: 15px;
        height: 15px;
        border-radius: 3px;
        margin-right: 5px;
    }

    .line-chart-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        padding: 20px;
        text-align: center;
        margin-top: 30px;
        box-sizing: border-box;
        width: 100%;
    }

    .line-chart-card h4 {
        color: #007bff;
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.5em;
    }

    .axis path,
    .axis line {
        fill: none;
        stroke: #ccc;
        shape-rendering: crispEdges;
    }

    .axis text {
        font-size: 10px;
        fill: #666;
    }

    .line {
        fill: none;
        stroke: steelblue;
        stroke-width: 2px;
    }

    .prediction-line {
        fill: none;
        stroke: orange;
        stroke-dasharray: 5,5;
        stroke-width: 2px;
    }

    .tooltip-line-chart {
        position: absolute;
        text-align: center;
        padding: 8px;
        font: 12px sans-serif;
        background: lightsteelblue;
        border: 0px;
        border-radius: 8px;
        pointer-events: none;
        opacity: 0;
    }

    /* Quick Links Navigation */
    .quick-links-nav {
        background-color: #e9ecef;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px; /* Adjusted margin-bottom for spacing from content */
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .quick-links-nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: block; /* Changed to block for vertical stacking */
    }

    .quick-links-nav li {
        margin-bottom: 10px; /* Spacing between vertical links */
    }

    .quick-links-nav li:last-child {
        margin-bottom: 0; /* No margin for the last item */
    }

    .quick-links-nav a {
        text-decoration: none;
        color: #007bff;
        font-weight: 600;
        padding: 8px 15px;
        border-radius: 5px;
        transition: background-color 0.3s ease, color 0.3s ease;
        display: block; /* Make links take full width of their list item */
        text-align: left; /* Align text to the left within the link */
    }

    .quick-links-nav a:hover {
        background-color: #007bff;
        color: white;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .container-fluid {
            flex-direction: column;
        }
        .left-pane {
            position: static; /* Revert to static on small screens */
            width: 100%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            height: auto;
            overflow-y: visible;
        }
        .main-content-area {
            margin-left: 0; /* Remove left margin on small screens */
            max-width: 100%;
            padding: 15px;
        }
        .quick-links-nav ul {
            display: flex; /* Revert to flex for small screens for horizontal layout */
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }
        .quick-links-nav li {
            margin-bottom: 0;
        }
        .quick-links-nav a {
            display: inline-block; /* Revert to inline-block */
            text-align: center;
        }
        .chart-card {
            max-width: 100%; /* Stack charts vertically on small screens */
        }
    }
</style>

<div class="container-fluid">
    <div class="left-pane">
        <div style="text-align:right; margin-bottom:20px;">
            <a href="auth/logout.php" class="btn btn-danger" style="background:#dc3545;color:#fff;padding:8px 18px;border-radius:5px;text-decoration:none;font-weight:600;">Logout</a>
        </div>
        <nav class="quick-links-nav">
            <ul>
                <li><a href="#stats-overview">Stats Overview</a></li>
                <li><a href="#system-charts">System Charts</a></li>
                <li><a href="#user-roles">User Roles</a></li>
                <li><a href="#recent-logins">Recent Logins</a></li>
                <li><a href="#user-management">User Management</a></li>
                <li><a href="#owner-tenant-overview">Owner/Tenant Overview</a></li>
                <li><a href="#error-logs">Error Logs</a></li>
                <li><a href="#all-activity-logs">All Activity Logs</a></li>
            </ul>
        </nav>
    </div>

    <div class="main-content-area">
        <div class="activity-log-page">
            <h2>Developer Dashboard</h2>

            <?php if ($developer_username): ?>
                <p class="welcome-message">Welcome, <strong><?php echo $developer_username; ?></strong>! Here's an overview of system activity.</p>
            <?php else: ?>
                <p class="welcome-message">Welcome! Here's an overview of system activity.</p>
            <?php endif; ?>

            <div id="stats-overview" class="dashboard-stats-grid">
                <div class="stat-card">
                    <h3>Total Activity Logs</h3>
                    <p><?php echo $total_logs; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Unique Users Logged</h3>
                    <p><?php echo $unique_users_logged; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending Complaints</h3>
                    <p><?php echo $pending_complaints; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Replied Complaints</h3>
                    <p><?php echo $replied_complaints; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Properties</h3>
                    <p><?php echo $total_properties; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Owners</h3>
                    <p><?php echo $total_owners; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Tenants</h3>
                    <p><?php echo $total_tenants; ?></p>
                </div>
            </div>

            <div id="system-charts" class="dashboard-section">
                <h3>System Progress Charts</h3>
                <div class="chart-container">
                    <div class="chart-card">
                        <h4>User Role Distribution</h4>
                        <svg id="userRoleChart"></svg>
                        <div id="userRoleLegend" class="chart-legend"></div>
                    </div>
                    <div class="chart-card">
                        <h4>Complaint Status</h4>
                        <svg id="complaintStatusChart"></svg>
                        <div id="complaintStatusLegend" class="chart-legend"></div>
                    </div>
                    <div class="chart-card">
                        <h4>Payment Progress</h4>
                        <svg id="paymentProgressChart"></svg>
                        <div id="paymentProgressLegend" class="chart-legend"></div>
                    </div>
                </div>
                <div class="line-chart-card">
                    <h4>User Account Trend & Prediction</h4>
                    <svg id="userTrendChart"></svg>
                </div>
            </div>

            <div id="user-roles" class="dashboard-section">
                <h3>User Role Distribution</h3>
                <div class="role-counts">
                    <?php foreach ($user_roles as $role => $count): ?>
                        <div class="role-item">
                            <h4><?php echo htmlspecialchars(ucfirst($role)); ?>s</h4>
                            <p><?php echo htmlspecialchars($count); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="recent-logins" class="dashboard-section">
                <h3>Recent Logins</h3>
                <?php if (!empty($recent_logins)): ?>
                    <ul class="recent-logins-list">
                        <?php foreach ($recent_logins as $login): ?>
                            <li>
                                <span class="username"><?php echo htmlspecialchars($login['username']); ?></span>
                                <span class="timestamp"><?php echo htmlspecialchars($login['timestamp']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-logs-message">No recent logins to display.</p>
                <?php endif; ?>
            </div>

            <div id="user-management" class="dashboard-section">
                <h3>User Account Management</h3>
                <?php if ($freeze_success): ?>
                    <div class="message-container success-message"><?php echo $freeze_success; ?></div>
                <?php elseif ($freeze_error): ?>
                    <div class="message-container error-message"><?php echo $freeze_error; ?></div>
                <?php endif; ?>

                <?php if (!empty($all_users)): ?>
                    <div class="table-responsive">
                        <table class="user-management-table">
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                                        <td>
                                            <span class="<?php echo $user['is_frozen'] ? 'user-status-frozen' : 'user-status-active'; ?>">
                                                <?php echo $user['is_frozen'] ? 'Frozen' : 'Active'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($user['user_id'] != $developer_user_id): // Prevent freezing/unfreezing self ?>
                                                <form method="post" style="display:inline-block;">
                                                    <?php if ($user['is_frozen']): ?>
                                                        <input type="hidden" name="unfreeze_user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                                                        <button type="submit" class="action-button unfreeze-button" onclick="return confirm('Are you sure you want to unfreeze <?php echo htmlspecialchars($user['username']); ?>?');">Unfreeze</button>
                                                    <?php else: ?>
                                                        <input type="hidden" name="freeze_user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                                                        <button type="submit" class="action-button freeze-button" onclick="return confirm('Are you sure you want to freeze <?php echo htmlspecialchars($user['username']); ?>?');">Freeze</button>
                                                    <?php endif; ?>
                                                </form>
                                            <?php else: ?>
                                                <span style="color:#777;">(Your Account)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-logs-message">No users found for management.</p>
                <?php endif; ?>
            </div>


            <div id="owner-tenant-overview" class="dashboard-section">
                <h3>Owner, Property & Tenant Overview</h3>
                <?php if (!empty($owner_details)): ?>
                    <?php foreach ($owner_details as $owner_id => $owner_data): ?>
                        <details class="owner-detail-card">
                            <summary>Owner: <?php echo $owner_data['username']; ?></summary>
                            <div class="details-content">
                                <?php if (!empty($owner_data['properties'])): ?>
                                    <ul class="property-list">
                                        <?php foreach ($owner_data['properties'] as $property_id => $property_data): ?>
                                            <details class="property-item">
                                                <summary class="property-details-summary">Property: <?php echo $property_data['name']; ?></summary>
                                                <div class="property-info-content">
                                                    <p><strong>Address:</strong> <?php echo $property_data['address']; ?></p>
                                                    <p><strong>Price:</strong> $<?php echo $property_data['price']; ?></p>
                                                    <?php if (!empty($property_data['tenants'])): ?>
                                                        <h6>Tenants:</h6>
                                                        <ul class="tenant-list-nested">
                                                            <?php foreach ($property_data['tenants'] as $tenant_data): ?>
                                                                <li><?php echo $tenant_data['name']; ?> (Contact: <?php echo $tenant_data['contact_number']; ?>)</li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    <?php else: ?>
                                                        <p>No tenants for this property.</p>
                                                    <?php endif; ?>
                                                </div>
                                            </details>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>No properties managed by this owner.</p>
                                <?php endif; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-logs-message">No owner, property, or tenant data found.</p>
                <?php endif; ?>
            </div>

            <div id="error-logs" class="dashboard-section">
                <h3>System Error Logs</h3>
                <?php if (!empty($error_logs)): ?>
                    <div class="table-responsive">
                        <table class="activity-table">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Action</th>
                                    <th class="details-col">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($error_logs as $log): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                                        <td class="details-col"><?php echo htmlspecialchars($log['details'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="no-logs-message">No error logs found.</p>
                <?php endif; ?>
            </div>

            <h3 id="all-activity-logs">All Activity Logs</h3>
            <?php if (!empty($logs)): ?>
                <div class="table-responsive">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>IP Address</th>
                                <th class="details-col">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                    <td><?php echo htmlspecialchars($log['username'] ?? 'N/A'); ?> (ID: <?php echo htmlspecialchars($log['user_id']); ?>)</td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                    <td class="details-col"><?php echo htmlspecialchars($log['details'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-logs-message">No activity logs found yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script src="https://d3js.org/d3.v7.min.js"></script>

<script>
    // PHP variables converted to JavaScript
    const userRoleData = <?php echo $json_chart_data_user_roles; ?>;
    const complaintStatusData = <?php echo $json_chart_data_complaint_status; ?>;
    const paymentProgressData = <?php echo $json_chart_data_payment_progress; ?>;
    const userRegistrationData = <?php echo $json_user_registration_data; ?>;

    // Function to draw a pie chart
    function drawPieChart(data, svgId, legendId, title) {
        const width = 300;
        const height = 300;
        const radius = Math.min(width, height) / 2;

        const svg = d3.select(svgId)
            .attr("width", width)
            .attr("height", height)
            .append("g")
            .attr("transform", `translate(${width / 2}, ${height / 2})`);

        const color = d3.scaleOrdinal()
            .range(d3.schemeCategory10); // Use a D3 color scheme

        // Generate the pie
        const pie = d3.pie()
            .value(d => d.value)
            .sort(null);

        // Generate the arcs
        const arc = d3.arc()
            .innerRadius(0)
            .outerRadius(radius);

        const arcs = svg.selectAll("arc")
            .data(pie(data))
            .enter()
            .append("g")
            .attr("class", "arc");

        arcs.append("path")
            .attr("d", arc)
            .attr("fill", (d, i) => color(i))
            .attr("stroke", "white")
            .style("stroke-width", "2px")
            .style("opacity", 0.7)
            .on("mouseover", function(event, d) {
                d3.select(this)
                    .transition()
                    .duration(100)
                    .style("opacity", 1)
                    .attr("transform", `scale(1.03)`); // Slightly enlarge on hover

                // Show tooltip
                const tooltip = d3.select("body").append("div")
                    .attr("class", "tooltip")
                    .style("position", "absolute")
                    .style("background-color", "rgba(0,0,0,0.7)")
                    .style("color", "white")
                    .style("padding", "8px")
                    .style("border-radius", "5px")
                    .style("pointer-events", "none")
                    .html(`<strong>${d.data.label}:</strong> ${d.data.value} (${((d.data.value / d3.sum(data, d => d.value)) * 100).toFixed(1)}%)`);

                tooltip.style("left", (event.pageX + 10) + "px")
                       .style("top", (event.pageY - 20) + "px");
            })
            .on("mouseout", function() {
                d3.select(this)
                    .transition()
                    .duration(100)
                    .style("opacity", 0.7)
                    .attr("transform", `scale(1)`);
                d3.selectAll(".tooltip").remove(); // Remove tooltip
            });

        // Add text labels
        arcs.append("text")
            .attr("transform", d => `translate(${arc.centroid(d)})`)
            .attr("text-anchor", "middle")
            .attr("fill", "white")
            .style("font-size", "12px")
            .style("font-weight", "bold")
            .text(d => {
                const percentage = (d.data.value / d3.sum(data, d => d.value)) * 100;
                return percentage > 5 ? `${percentage.toFixed(1)}%` : ''; // Only show percentage if large enough
            });

        // Create legend
        const legend = d3.select(legendId);
        data.forEach((d, i) => {
            const legendItem = legend.append("div")
                .attr("class", "legend-item");
            legendItem.append("div")
                .attr("class", "legend-color")
                .style("background-color", color(i));
            legendItem.append("span")
                .text(d.label);
        });
    }

    // Function to draw a line chart with prediction
    function drawLineChart(data, svgId, title) {
        const margin = {top: 20, right: 30, bottom: 60, left: 60};
        const width = 600 - margin.left - margin.right;
        const height = 400 - margin.top - margin.bottom;

        const svg = d3.select(svgId)
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", `translate(${margin.left}, ${margin.top})`);

        // Parse the date and sort data
        const parseDate = d3.timeParse("%Y-%m-%d");
        data.forEach(d => {
            d.date = parseDate(d.date);
            d.cumulative_users = +d.cumulative_users;
        });
        data.sort((a, b) => a.date - b.date);

        // Set up scales
        const x = d3.scaleTime()
            .domain(d3.extent(data, d => d.date))
            .range([0, width]);

        const y = d3.scaleLinear()
            .domain([0, d3.max(data, d => d.cumulative_users) * 1.2]) // 20% buffer for prediction
            .range([height, 0]);

        // Add X axis
        svg.append("g")
            .attr("transform", `translate(0, ${height})`)
            .call(d3.axisBottom(x).tickFormat(d3.timeFormat("%Y-%m")))
            .selectAll("text")
            .style("text-anchor", "end")
            .attr("dx", "-.8em")
            .attr("dy", ".15em")
            .attr("transform", "rotate(-65)");

        // Add Y axis
        svg.append("g")
            .call(d3.axisLeft(y));

        // Add the line
        const line = d3.line()
            .x(d => x(d.date))
            .y(d => y(d.cumulative_users));

        svg.append("path")
            .datum(data)
            .attr("class", "line")
            .attr("d", line);

        // Add dots
        svg.selectAll("dot")
            .data(data)
            .enter().append("circle")
            .attr("r", 5)
            .attr("cx", d => x(d.date))
            .attr("cy", d => y(d.cumulative_users))
            .attr("fill", "steelblue")
            .on("mouseover", function(event, d) {
                d3.select(".tooltip-line-chart")
                    .style("opacity", 1)
                    .html(`Date: ${d3.timeFormat("%Y-%m-%d")(d.date)}<br>Users: ${d.cumulative_users}`)
                    .style("left", (event.pageX + 10) + "px")
                    .style("top", (event.pageY - 20) + "px");
            })
            .on("mouseout", function() {
                d3.select(this)
                    .transition()
                    .duration(100)
                    .style("opacity", 0.7)
                    .attr("transform", `scale(1)`);
                d3.selectAll(".tooltip").remove(); // Remove tooltip
            });

        // Simple Linear Regression for Prediction
        // Calculate sums for linear regression (y = mx + b)
        let sum_x = 0;
        let sum_y = 0;
        let sum_xy = 0;
        let sum_xx = 0;
        let n = data.length;

        data.forEach(d => {
            const x_val = d.date.getTime(); // Convert date to timestamp for calculation
            const y_val = d.cumulative_users;
            sum_x += x_val;
            sum_y += y_val;
            sum_xy += x_val * y_val;
            sum_xx += x_val * x_val;
        });

        const m = (n * sum_xy - sum_x * sum_y) / (n * sum_xx - sum_x * sum_x);
        const b = (sum_y - m * sum_x) / n;

        // Predict future points (e.g., next 3 months)
        const lastDate = data[data.length - 1].date;
        const predictionData = [];
        for (let i = 1; i <= 3; i++) { // Predict for next 3 months
            const futureDate = d3.timeMonth.offset(lastDate, i);
            const predictedUsers = m * futureDate.getTime() + b;
            predictionData.push({ date: futureDate, cumulative_users: predictedUsers });
        }

        // Combine historical and prediction data for the prediction line
        const fullPredictionData = data.concat(predictionData);

        // Draw the prediction line
        svg.append("path")
            .datum(fullPredictionData)
            .attr("class", "prediction-line")
            .attr("d", line);

        // Tooltip for line chart
        d3.select("body").append("div")
            .attr("class", "tooltip-line-chart")
            .style("opacity", 0);
    }


    // Draw the charts when the window loads
    window.onload = function() {
        drawPieChart(userRoleData, "#userRoleChart", "#userRoleLegend", "User Role Distribution");
        drawPieChart(complaintStatusData, "#complaintStatusChart", "#complaintStatusLegend", "Complaint Status");
        drawPieChart(paymentProgressData, "#paymentProgressChart", "#paymentProgressLegend", "Payment Progress");
        drawLineChart(userRegistrationData, "#userTrendChart", "User Account Trend & Prediction");
    };
</script>
