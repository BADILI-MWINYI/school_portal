<?php
session_start();
require_once "config.php";

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username   = trim($_POST['username']);
    $password   = trim($_POST['password']);
    $secret_key = trim($_POST['secret_key']); // Secret key field

    // -------------------------
    // SECRET KEY CHECK
    // -------------------------
    $allowed_key = "schoolportal1234"; // Replace with your secret key or use config.php
    if($secret_key !== $allowed_key){
        die("<div style='color:red'>Access denied: invalid secret key.</div>");
    }

    // -------------------------
    // LOGIN CHECK
    // -------------------------
    $stmt = $conn->prepare("SELECT id, name, role, password FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $name, $role, $hashed_password);
    $stmt->fetch();
    $stmt->close();

    // Verify password using password_verify for bcrypt
    if($user_id && password_verify($password, $hashed_password)) {
        // Set session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;
        $_SESSION['name'] = $name; // <-- store user name

        // Redirect by role
        if($role == 'student') {
            header("Location: student_dashboard.php");
        } elseif($role == 'teacher') {
            header("Location: teacher_dashboard.php");
        } elseif($role == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        echo "<div style='color:red'>Invalid username or password</div>";
    }
}
?>

<!-- -------------------------
     LOGIN FORM HTML
------------------------- -->
<!DOCTYPE html>
<html>
<head>
    <title>Login - School Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
    <h2 class="mb-4">Login to School Portal</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Secret Key</label>
            <input type="text" name="secret_key" class="form-control" placeholder="Enter secret key" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
</body>
</html>
