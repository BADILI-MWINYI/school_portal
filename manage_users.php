<?php
session_start();
require_once "config.php";

// Only logged-in admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Fetch users
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="admin_dashboard.php">School Portal</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Manage Users</h2>

    <?php if ($result->num_rows === 0): ?>
        <div class="alert alert-warning">No users found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Class</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['username'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['role'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['class'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at'] ?? ''); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $row['id'] ?? 0; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_user.php?id=<?php echo $row['id'] ?? 0; ?>" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
