<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher'){
    header("Location: ../index.php");
    exit();
}

require_once "config.php";

$message = "";
$message_type = "";

// Fetch students for selected class
$class = isset($_POST['class']) ? $_POST['class'] : "";
$students = [];
if($class && isset($_POST['load_students'])){
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE role='student' AND class=?");
    $stmt->bind_param("s", $class);
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()){
        $students[] = $row;
    }
}

// Handle form submission
if(isset($_POST['submit_results'])){
    $subject = $_POST['subject'];
    $term = $_POST['term'];
    $student_ids = $_POST['student_id'];
    $marks = $_POST['marks'];
    $grades = $_POST['grades'];

    $stmt = $conn->prepare("INSERT INTO results (student_id, subject, marks, grade, term, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");

    for($i=0; $i<count($student_ids); $i++){
        $stmt->bind_param(
            "isissi",
            $student_ids[$i],
            $subject,
            $marks[$i],
            $grades[$i],
            $term,
            $_SESSION['user_id']
        );
        $stmt->execute();
    }
    $message = "Results uploaded successfully!";
    $message_type = "success";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Results</title>
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
        <h2 class="mb-4">Upload Results</h2>

        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Step 1: Select class -->
        <div class="card shadow p-4 mb-4">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Select Class</label>
                    <input type="text" name="class" class="form-control" placeholder="e.g Form 1A" required>
                </div>
                <button type="submit" name="load_students" class="btn btn-primary">Load Students</button>
            </form>
        </div>

        <?php if($students): ?>
            <!-- Step 2: Enter results -->
            <div class="card shadow p-4">
                <form method="post">
                    <input type="hidden" name="class" value="<?php echo htmlspecialchars($class); ?>">

                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Term</label>
                        <input type="text" name="term" class="form-control" placeholder="e.g 1st Term CAT" required>
                    </div>

                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Marks</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($students as $s): ?>
                            <tr>
                                <td>
                                    <?php echo $s['name']; ?>
                                    <input type="hidden" name="student_id[]" value="<?php echo $s['id']; ?>">
                                </td>
                                <td><input type="number" name="marks[]" class="form-control" required></td>
                                <td>
                                    <select name="grades[]" class="form-select" required>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                        <option value="E">E</option>
                                        <option value="F">F</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <button type="submit" name="submit_results" class="btn btn-primary w-100">Upload Results</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
