<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teacher Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">School Portal</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard Container -->
    <div class="container mt-5">
        <h2 class="mb-4">
            Welcome, <?php echo $_SESSION['name']; ?> (Teacher)
        </h2>

        <div class="row">

            <!-- Upload Files -->
            <div class="col-md-4 mb-3">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Upload CATs/Exams/Notes</h5>
                        <p class="card-text">Upload files for students to download</p>
                        <a href="teacher_upload_files.php" class="btn btn-primary">
                            Go
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upload Results -->
            <div class="col-md-4 mb-3">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Upload Results</h5>
                        <p class="card-text">Add student marks and grades</p>
                        <a href="upload_results.php" class="btn btn-primary">
                            Go
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
