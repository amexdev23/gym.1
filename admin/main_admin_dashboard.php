<?php
session_start();
if (!isset($_SESSION['main_admin_id'])) {
    header("Location: index.php");
    exit();
}
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Main Admin Dashboard</h2>
        <a href="manage_admins.php" class="btn btn-primary">Manage Admins</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>