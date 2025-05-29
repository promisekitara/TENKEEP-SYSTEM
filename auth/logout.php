<?php
require_once '../includes/auth.php';
logout();
redirect('/tenkeep/auth/login.php');
?>