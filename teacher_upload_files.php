<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Access denied.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $type = $_POST['type'] ?? '';
    $class = $_POST['class'] ?? '';

    if (!isset($_FILES['file']) || empty($title) || empty($type) || empty($class)) {
        $error = "All fields are required.";
    } else {
        $upload_dir = __DIR__ . "/private_uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $filename = uniqid() . "-" . basename($_FILES['file']['name']);
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
            $stmt = $conn->prepare("INSERT INTO uploads (title, type, class, file_path, uploaded_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssi", $title, $type, $class, $filename, $user_id);
            $stmt->execute();
            $stmt->close();
            $success = "File uploaded successfully!";
        } else {
            $error = "Failed to upload file.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Files</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Upload New File</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="mt-3">
        <div class="mb-3">
            <label>File Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>File Type</label>
            <input type="text" name="type" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Class</label>
            <input type="text" name="class" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Select File</label>
            <input type="file" name="file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
        <a href="teacher_dashboard.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
