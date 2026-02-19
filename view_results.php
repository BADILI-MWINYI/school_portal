<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student'){
    header("Location: ../index.php");
    exit();
}

require_once "config.php";

$student_id = $_SESSION['user_id'];

// Fetch results for this student
$stmt = $conn->prepare("SELECT subject, marks, grade, term, created_at FROM results WHERE student_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$results = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">School Portal</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Your Results</h2>

        <?php if(!$results): ?>
            <div class="alert alert-warning">No results uploaded yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>Subject</th>
                            <th>Term / Exam</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Uploaded On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $res): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($res['subject']); ?></td>
                            <td><?php echo htmlspecialchars($res['term']); ?></td>
                            <td><?php echo $res['marks']; ?></td>
                            <td><?php echo htmlspecialchars($res['grade']); ?></td>
                            <td><?php echo date("d M Y, H:i", strtotime($res['created_at'])); ?></td>
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
