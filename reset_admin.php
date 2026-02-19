<?php
include 'config.php';
$password = password_hash("123456", PASSWORD_DEFAULT); // new password
$conn->query("UPDATE users SET password='$password' WHERE username='admin'");
echo "Admin password reset!";
?>
