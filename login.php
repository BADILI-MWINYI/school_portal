<?php
session_start();
require_once "config.php";

// GitHub access check function
function isGitHubContributor($username) {
    $token = GITHUB_TOKEN;
    $repo = GITHUB_REPO;

    $url = "https://api.github.com/repos/$repo/collaborators/$username";
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_USERAGENT, "SchoolPortalApp"); // required by GitHub API
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: token $token"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $status === 204; // 204 = user is collaborator
}

// Check login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user from DB
    $stmt = $conn->prepare("SELECT id, name, role, password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $name, $role, $hash);
    $stmt->fetch();
    $stmt->close();

    if (!$id) {
        die("Invalid username or password.");
    }

    // Verify password
    if (!password_verify($password, $hash)) {
        die("Invalid username or password.");
    }

    // If teacher or student, check GitHub access
    if ($role === 'teacher' || $role === 'student') {
        if (!isGitHubContributor($username)) {
            die("Access denied. You must have GitHub repository access.");
        }
    }

    // Set session
    $_SESSION['user_id'] = $id;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = $role;

    // Redirect based on role
    if ($role === 'admin') {
        header("Location: admin_dashboard.php");
    } elseif ($role === 'teacher') {
        header("Location: teacher_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - School Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4 text-center">School Portal Login</h2>
    <div class="row justify-content-center">
        <div class="col-md-4">
            <form method="POST" class="card p-4 shadow">
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
