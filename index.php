<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php'; // Ensure this is also included if you use redirect() elsewhere in index.php
if (is_logged_in()) {
    if (get_user_role() === 'owner') {
        redirect('owner/dashboard.php');
    } elseif (get_user_role() === 'tenant') {
        redirect('tenant/dashboard.php');
    }
} else {
    redirect('auth/login.php');
}
?>