<?php
session_start();
require_once "config.php";

// Check logged-in student
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student'){
    die("Access denied.");
}

if(!isset($_GET['id'])){
    die("Invalid request.");
}

$file_id = intval($_GET['id']);
$student_id = $_SESSION['user_id'];

// Get student's class
$stmt = $conn->prepare("SELECT class FROM users WHERE id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($student_class);
$stmt->fetch();
$stmt->close();

// Get file info if it belongs to student's class
$stmt = $conn->prepare("SELECT file_path, title FROM uploads WHERE id=? AND class=?");
$stmt->bind_param("is", $file_id, $student_class);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

if(!$file){
    die("File not found or access denied.");
}

// Secure file path (absolute path)
$uploadsDir = "C:\\xampp\\secure_uploads\\";  // Your secure uploads folder
$filePath = realpath($uploadsDir . $file['file_path']);

if(!$filePath || strpos($filePath, $uploadsDir) !== 0 || !file_exists($filePath)){
    die("Invalid file or missing.");
}

// Get MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $filePath);
finfo_close($finfo);

// Force file download headers
header("Content-Description: File Transfer");
header("Content-Type: $mimeType");
header("Content-Disposition: attachment; filename=\"" . basename($file['file_path']) . "\"");
header("Content-Length: " . filesize($filePath));
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: public");

// Output file
readfile($filePath);
exit();
