<?php
session_start();
require_once "config.php";

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied.");
}

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$file_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get file info
$stmt = $conn->prepare("
    SELECT uploads.file_path, uploads.class, uploads.uploaded_by
    FROM uploads
    WHERE uploads.id = ?
");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

if (!$file) {
    die("File not found.");
}

$filePath = realpath("C:/xampp/secure_uploads/" . $file['file_path']);

// Security check: file exists
if (!$filePath || !file_exists($filePath)) {
    die("File missing.");
}

// ===============================
// ROLE-BASED ACCESS CONTROL
// ===============================

// ADMIN → can download anything
if ($user_role === 'admin') {
    // allowed
}

// TEACHER → can only download their own uploads
elseif ($user_role === 'teacher') {
    if ($file['uploaded_by'] != $user_id) {
        die("Access denied.");
    }
}

// STUDENT → can only download files for their class
elseif ($user_role === 'student') {

    $stmt = $conn->prepare("SELECT class FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($student_class);
    $stmt->fetch();
    $stmt->close();

    if ($student_class !== $file['class']) {
        die("Access denied.");
    }
}
else {
    die("Access denied.");
}

// ===============================
// FORCE DOWNLOAD
// ===============================

$filename = basename($filePath);

header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . filesize($filePath));
header("Cache-Control: no-cache");
readfile($filePath);
exit();
?>
