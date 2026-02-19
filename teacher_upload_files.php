<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher'){
    header("Location: index.php");
    exit();
}

require_once "config.php";

$message = "";
$message_type = "";

if(isset($_POST['upload'])){

    $title = trim($_POST['title']);
    $type = trim($_POST['type']);
    $class = trim($_POST['class']);

    // Check if file exists
    if(isset($_FILES['file']) && $_FILES['file']['error'] === 0){

        $fileName = $_FILES['file']['name'];
        $fileTmp  = $_FILES['file']['tmp_name'];
        $fileSize = $_FILES['file']['size'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowed = ['pdf','doc','docx','ppt','pptx','jpg','png'];

        if(in_array($fileExt, $allowed)){

            // Limit file size to 10MB
            if($fileSize <= 10 * 1024 * 1024){

                // Create uploads folder if it doesn't exist
                $uploadDir = __DIR__ . "/uploads/";

                if(!is_dir($uploadDir)){
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = uniqid() . "-" . preg_replace("/[^a-zA-Z0-9.\-_]/", "", $fileName);
                $destination = $uploadDir . $newFileName;

                if(move_uploaded_file($fileTmp, $destination)){

                    // Save relative path in DB
                    $dbPath = "uploads/" . $newFileName;

                    $stmt = $conn->prepare("INSERT INTO uploads (title, type, class, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssi", $title, $type, $class, $dbPath, $_SESSION['user_id']);

                    if($stmt->execute()){
                        $message = "File uploaded successfully!";
                        $message_type = "success";
                    } else {
                        $message = "Database error: " . $stmt->error;
                        $message_type = "danger";
                    }

                } else {
                    $message = "Failed to move uploaded file.";
                    $message_type = "danger";
                }

            } else {
                $message = "File is too large. Maximum size is 10MB.";
                $message_type = "danger";
            }

        } else {
            $message = "File type not allowed!";
            $message_type = "danger";
        }

    } else {
        $message = "Please select a file.";
        $message_type = "danger";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Upload Files</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">School Portal</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Upload CATs/Exams/Notes</h2>

    <?php if($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="card shadow p-4">
        <form method="post" enctype="multipart/form-data">

            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-select" required>
                    <option value="exam">Exam</option>
                    <option value="cat">CAT</option>
                    <option value="notes">Notes</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Class</label>
                <input type="text" name="class" class="form-control" placeholder="e.g Form 1A" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Choose File</label>
                <input type="file" name="file" class="form-control" required>
            </div>

            <button type="submit" name="upload" class="btn btn-primary w-100">
                Upload
            </button>

        </form>
    </div>
</div>

</body>
</html>
