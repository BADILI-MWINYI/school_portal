<?php
session_start();
require_once "config.php";

// Only logged-in users allowed
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['student', 'teacher', 'admin'])){
    die("Access denied. Please login first.");
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Get user's class (only for students)
$user_class = null;
if($user_role == 'student'){
    $stmt = $conn->prepare("SELECT class FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($user_class);
    $stmt->fetch();
    $stmt->close();
}

// Fetch files
if($user_role == 'student'){
    // Student sees only their class files
    $stmt = $conn->prepare("SELECT id, title, type, file_path, created_at FROM uploads WHERE class=? ORDER BY created_at DESC");
    $stmt->bind_param("s", $user_class);
} else {
    // Teacher and Admin see all files
    $stmt = $conn->prepare("SELECT id, title, type, file_path, created_at FROM uploads ORDER BY created_at DESC");
}

$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Download Files</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">Available Files</h2>

    <?php if(empty($files)): ?>
        <div class="alert alert-warning">No files uploaded yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Uploaded On</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($files as $file): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($file['title']); ?></td>
                        <td><?php echo strtoupper(htmlspecialchars($file['type'])); ?></td>
                        <td><?php echo date("d M Y, H:i", strtotime($file['created_at'])); ?></td>
                        <td>
                            <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-success btn-sm">Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

</body>
</html>
