<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student'){
    header("Location: index.php");
    exit();
}

require_once "config.php";

$student_id = $_SESSION['user_id'];

// Get student's class
$stmt = $conn->prepare("SELECT class FROM users WHERE id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($student_class);
$stmt->fetch();
$stmt->close();

// Fetch files for student's class
$stmt = $conn->prepare("SELECT id, title, type, file_path, created_at 
                        FROM uploads 
                        WHERE class=? 
                        ORDER BY created_at DESC");
$stmt->bind_param("s", $student_class);
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

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">School Portal</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">
        Available Files for <?php echo htmlspecialchars($student_class); ?>
    </h2>

    <?php if(empty($files)): ?>
        <div class="alert alert-warning">
            No files uploaded yet.
        </div>
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
                            <!-- ✅ Secure download button -->
                            <a href="download.php?id=<?php echo $file['id']; ?>" 
                               class="btn btn-success btn-sm">
                                Download
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>

    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-3">
        Back to Dashboard
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
