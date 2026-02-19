<?php
include 'config.php';
$password = password_hash("123456", PASSWORD_DEFAULT);
$conn->query("INSERT INTO users (name, role, username, password) VALUES ('Admin User','admin','admin','$password')");
echo "Admin created!";
?>
