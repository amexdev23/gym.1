<?php
session_start();
if (!isset($_SESSION['main_admin_id'])) {
    header("Location: index.php");
    exit();
}
include 'config.php';

// Fetch all admins
$stmt = $conn->prepare("SELECT * FROM admin");
$stmt->execute();
$admins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Manage Admins</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Gym Name</th>
                    <th>Subscription Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?php echo $admin['id']; ?></td>
                    <td><?php echo $admin['name']; ?></td>
                    <td><?php echo $admin['email']; ?></td>
                    <td><?php echo $admin['gym_name']; ?></td>
                    <td><?php echo $admin['subscription_status']; ?></td>
                    <td>
                        <a href="edit_admin.php?id=<?php echo $admin['id']; ?>" class="btn btn-warning">Edit</a>
                        <a href="delete_admin.php?id=<?php echo $admin['id']; ?>" class="btn btn-danger">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="add_admin.php" class="btn btn-success">Add Admin</a>
    </div>
</body>
</html>