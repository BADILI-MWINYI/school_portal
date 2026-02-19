<?php
session_start();
require_once "config.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin'){
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// -----------------------------
// Handle Filter Input
// -----------------------------
$filter_class = isset($_GET['class']) ? trim($_GET['class']) : '';
$filter_teacher = isset($_GET['teacher']) ? trim($_GET['teacher']) : '';

// -----------------------------
// Build base query
// -----------------------------
$query = "SELECT uploads.id, uploads.title, uploads.type, uploads.file_path, uploads.created_at, users.name AS teacher_name, uploads.class
          FROM uploads
          LEFT JOIN users ON uploads.uploaded_by = users.id";
$conditions = [];
$params = [];
$param_types = "";

if($filter_class){
    $conditions[] = "uploads.class = ?";
    $params[] = $filter_class;
    $param_types .= "s";
}
if($filter_teacher){
    $conditions[] = "users.id = ?";
    $params[] = $filter_teacher;
    $param_types .= "i";
}

if($conditions){
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY uploads.created_at DESC";

$stmt = $conn->prepare($query);

if(!empty($params)){
    $stmt->bind_param($param_types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$files = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// -----------------------------
// Fetch all teachers for dropdown
// -----------------------------
$teachers_result = $conn->query("SELECT id, name FROM users WHERE role='teacher' ORDER BY name ASC");
$teachers = $teachers_result->fetch_all(MYSQLI_ASSOC);

// -----------------------------
// Fetch all classes for dropdown
// -----------------------------
$classes_result = $conn->query("SELECT DISTINCT class FROM uploads ORDER BY class ASC");
$classes = $classes_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">School Portal</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?> (Admin)</h2>

    <!-- -------------------------
         Deletion Feedback Alert
         ------------------------- -->
    <?php if(isset($_GET['delete_status'])): ?>
        <?php if($_GET['delete_status'] == 'SUCCESS'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                File deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php elseif($_GET['delete_status'] == 'FAILED'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                File deletion failed. Check server permissions.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Manage Users</h5>
                    <p class="card-text">Add, edit or remove teachers & students</p>
                    <a href="manage_users.php" class="btn btn-primary">Go</a>
                </div>
            </div>
        </div>
    </div>

    <!-- -------------------------
         Filter Form
         ------------------------- -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <select name="class" class="form-select">
                <option value="">-- Filter by Class --</option>
                <?php foreach($classes as $c): ?>
                    <option value="<?php echo htmlspecialchars($c['class']); ?>" <?php if($filter_class == $c['class']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($c['class']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select name="teacher" class="form-select">
                <option value="">-- Filter by Teacher --</option>
                <?php foreach($teachers as $t): ?>
                    <option value="<?php echo $t['id']; ?>" <?php if($filter_teacher == $t['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($t['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <!-- -------------------------
         Uploaded Files Table
         ------------------------- -->
    <h3 class="mb-3">Uploaded Files</h3>
    <?php if(empty($files)): ?>
        <div class="alert alert-warning">No files found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Class</th>
                        <th>Uploaded By</th>
                        <th>Uploaded On</th>
                        <th>Download</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($files as $file): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($file['title']); ?></td>
                            <td><?php echo strtoupper(htmlspecialchars($file['type'])); ?></td>
                            <td><?php echo htmlspecialchars($file['class']); ?></td>
                            <td><?php echo htmlspecialchars($file['teacher_name']); ?></td>
                            <td><?php echo date("d M Y, H:i", strtotime($file['created_at'])); ?></td>
                            <td>
                                <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-success btn-sm">Download</a>
                            </td>
                            <td>
                                <a href="delete_file.php?id=<?php echo $file['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this file?');">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>