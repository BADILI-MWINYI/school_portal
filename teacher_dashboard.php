<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher'){
    header("Location: index.php");
    exit();
}

// Safely get teacher name
$teacher_name = $_SESSION['name'] ?? "Teacher";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
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
    <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($teacher_name); ?> (Teacher)</h2>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Upload Files</h5>
                    <p class="card-text">Upload study materials for your class</p>
                    <a href="teacher_upload_files.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">View Your Uploads</h5>
                    <p class="card-text">See files you have uploaded</p>
                    <a href="download_files.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
