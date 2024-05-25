<?php
session_start();
require "connect.php";

// Initialize notification variable
$notification = '';

if (isset($_POST['id'])) {
    $admin_id = $_POST['id'];
} else {
    // Redirect back to the main page if the ID is not set
    header('Location: admin.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT); // Hash the new password

    $query = "UPDATE admin SET password = ? WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('si', $hashed_password, $admin_id);

    if ($stmt->execute()) {
        $notification = '<div class="alert alert-success" role="alert">Password updated successfully!</div>';
    } else {
        $notification = '<div class="alert alert-danger" role="alert">Error updating password: ' . $stmt->error . '</div>';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>Edit Password</title>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="main-content">
        <div class="container">
            <h3>Edit Password for Admin ID: <?php echo htmlspecialchars($admin_id); ?></h3>
            <?php echo $notification; ?>
            <form action="edit_password.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($admin_id); ?>">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-outline-info">Update Password</button>
                <a href="admin.php" class="btn btn-outline-secondary">Back to Admin Page</a>
            </form>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>
