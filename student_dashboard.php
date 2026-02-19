<?php
session_start();
require_once "config.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'] ?? "Student";

// Get student's class
$stmt = $conn->prepare("SELECT class FROM users WHERE id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($student_class);
$stmt->fetch();
$stmt->close();

// Fetch files for student's class
$stmt = $conn->prepare("SELECT id, title, type, created_at FROM uploads WHERE class=? ORDER BY created_at DESC");
$stmt->bind_param("s", $student_class);
$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">School Portal</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($student_name); ?> (<?php echo htmlspecialchars($student_class); ?>)</h2>

    <?php if(empty($files)): ?>
        <div class="alert alert-warning">No files available yet.</div>
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
                            <td><?php echo htmlspecialchars($file['title'] ?? ""); ?></td>
                            <td><?php echo strtoupper(htmlspecialchars($file['type'] ?? "")); ?></td>
                            <td><?php echo date("d M Y, H:i", strtotime($file['created_at'] ?? "")); ?></td>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
