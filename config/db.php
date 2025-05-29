<?php
$db_host = 'localhost';
$db_user = 'root'; // Replace with your database username
$db_pass = ''; // Replace with your database password
$db_name = 'tenkeep_db';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>