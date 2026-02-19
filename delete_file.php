<?php
session_start();
require_once "config.php";

// Only admin can delete files
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    die("Access denied.");
}

if(!isset($_GET['id'])){
    die("Invalid request.");
}

$file_id = intval($_GET['id']);
$admin_name = $_SESSION['name'];  // for logging

// -----------------------------
// Get file info from database
// -----------------------------
$stmt = $conn->prepare("SELECT file_path, title FROM uploads WHERE id=?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

if(!$file){
    die("File not found.");
}

// -----------------------------
// Define secure uploads folder
// -----------------------------
$uploadsDir = "C:\\xampp\\secure_uploads\\"; // adjust to your secure folder
$filePath = realpath($uploadsDir . $file['file_path']);

// -----------------------------
// Security check: file must be inside uploads folder
// -----------------------------
if(!$filePath || strpos($filePath, $uploadsDir) !== 0 || !file_exists($filePath)){
    die("Unauthorized or missing file.");
}

// -----------------------------
// Delete the physical file
// -----------------------------
if(unlink($filePath)){
    $status = "SUCCESS";
} else {
    $status = "FAILED";
}

// -----------------------------
// Delete from database
// -----------------------------
$stmt = $conn->prepare("DELETE FROM uploads WHERE id=?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$stmt->close();

// -----------------------------
// Log the deletion
// -----------------------------
$log_file = __DIR__ . "/deletion_log.txt";
$log_entry = date("Y-m-d H:i:s") . " | Admin: $admin_name | File: {$file['title']} | Path: {$file['file_path']} | Status: $status" . PHP_EOL;
file_put_contents($log_file, $log_entry, FILE_APPEND);

// -----------------------------
// Redirect back to admin dashboard
// -----------------------------
header("Location: admin_dashboard.php");
exit();
