<?php
session_start();
require_once "config.php";

// Only logged-in users can download
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student', 'teacher'])){
    die("Access denied. Please login first.");
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

if(!isset($_GET['id'])){
    die("Invalid request.");
}

$file_id = intval($_GET['id']);

// Get student class if needed
$user_class = null;
if($user_role == 'student'){
    $stmt = $conn->prepare("SELECT class FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_class);
    $stmt->fetch();
    $stmt->close();
}

// Fetch file info
if($user_role == 'student'){
    $stmt = $conn->prepare("SELECT file_path, title FROM uploads WHERE id=? AND class=?");
    $stmt->bind_param("is", $file_id, $user_class);
} else { // teacher
    $stmt = $conn->prepare("SELECT file_path, title FROM uploads WHERE id=?");
    $stmt->bind_param("i", $file_id);
}

$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

if(!$file){
    die("File not found or access denied.");
}

// Secure file path
$uploadsDir = "C:\\xampp\\secure_uploads\\";  // change to your folder
$filePath = realpath($uploadsDir . $file['file_path']);

if(!$filePath || strpos($filePath, $uploadsDir) !== 0 || !file_exists($filePath)){
    die("Invalid file or missing.");
}

// Force download
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

header("Content-Description: File Transfer");
header("Content-Type: $mimeType");
header("Content-Disposition: attachment; filename=\"" . basename($file['file_path']) . "\"");
header("Content-Length: " . filesize($filePath));
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: public");

readfile($filePath);
exit();
