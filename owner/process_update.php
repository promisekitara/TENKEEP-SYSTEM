<?php
// process_update.php - Handles property update form submission
session_start();
require_once '../config/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    redirect('../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = intval($_POST['property_id'] ?? 0);
    $name = escape_string($conn, $_POST['name'] ?? '');
    $address = escape_string($conn, $_POST['address'] ?? '');
    $description = escape_string($conn, $_POST['description'] ?? '');
    $rent = floatval($_POST['rent'] ?? 0);
    $status = escape_string($conn, $_POST['status'] ?? '');

    if ($property_id > 0 && $name && $address && $rent > 0) {
        $sql = "UPDATE properties SET name='$name', address='$address', description='$description', rent=$rent, status='$status' WHERE property_id=$property_id AND owner_id=" . intval($_SESSION['user_id']);
        $result = mysqli_query($conn, $sql);
        if ($result) {
            logActivity($conn, $_SESSION['user_id'], 'Updated Property', "Property ID: $property_id");
            $_SESSION['success'] = 'Property updated successfully!';
        } else {
            $_SESSION['error'] = 'Failed to update property.';
        }
    } else {
        $_SESSION['error'] = 'Invalid input. Please fill all required fields.';
    }
    redirect('properties.php');
} else {
    redirect('properties.php');
}