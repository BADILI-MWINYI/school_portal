<?php
session_start();
require_once "config.php";

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get user's class (for students)
if ($user_role === 'student') {
    $stmt = $conn->prepare("SELECT class FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($student_class);
    $stmt->fetch();
    $stmt->close();
}

// Fetch files based on role
if ($user_role === 'admin') {
    $query = "SELECT id, title, type, file_path, uploaded_by, class, created_at FROM uploads ORDER BY created_at DESC";
    $result = $conn->query($query);
} elseif ($user_role === 'teacher') {
    $stmt = $conn->prepare("SELECT id, title, type, file_path, uploaded_by, class, created_at FROM uploads WHERE uploaded_by = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($user_role === 'student') {
    $stmt = $conn->prepare("SELECT id, title, type, file_path, uploaded_by, class, created_at FROM uploads WHERE class = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $student_class);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Access denied.");
}

$files = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Files</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Available Files</h2>

    <?php if (empty($files)): ?>
        <div class="alert alert-warning">No files available.</div>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Class</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): ?>
                <tr>
                    <td><?php echo htmlspecialchars($file['title']); ?></td>
                    <td><?php echo strtoupper(htmlspecialchars($file['type'])); ?></td>
                    <td><?php echo htmlspecialchars($file['class']); ?></td>
                    <td><?php echo htmlspecialchars($file['uploaded_by']); ?></td>
                    <td><?php echo date("d M Y, H:i", strtotime($file['created_at'])); ?></td>
                    <td>
                        <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-success btn-sm">Download</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="<?php 
        echo $user_role === 'admin' ? 'admin_dashboard.php' : 
             ($user_role === 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'); 
    ?>" class="btn btn-secondary mt-3">Back</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
