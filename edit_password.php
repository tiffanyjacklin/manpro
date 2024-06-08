<?php
session_start();
require "connect.php";
$user_id = $_SESSION['user_id'];

// Initialize notification variable
$notification = '';

if (isset($_POST['id'])) {
    $admin_id = $_POST['id'];
} else {
    // Redirect back to the main page if the ID is not set
    header('Location: admin.php');
    exit();
}

function generateRandomPassword($length =5) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomPassword = '';
    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomPassword;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // Process the password reset if confirmation is given
    $new_password = generateRandomPassword();
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT); // Hash the new password

    $query = "UPDATE admin SET password = ? WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param('si', $hashed_password, $admin_id);

    if ($stmt->execute()) {
        $notification = '<div class="alert alert-success" role="alert">Password updated successfully! The new password is: <strong>' . htmlspecialchars($new_password) . '</strong></div>';
        mysqli_query($con, "INSERT INTO `log` (`id_admin`, `id_table`, `action`, `detail_action`, `timestamp`) VALUES ($user_id, 5, 2, 'Password changed for Admin ID ".$admin_id.".', current_timestamp()); ");
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
            
            <?php if (!isset($_POST['confirm'])): ?>
                <!-- Confirmation form -->
                <form action="edit_password.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($admin_id); ?>">
                    <p>Are you sure you want to reset the password for Admin ID: <?php echo htmlspecialchars($admin_id); ?>?</p>
                    <button type="submit" name="confirm" value="yes" class="btn btn-outline-info">Yes, Reset Password</button>
                    <a href="admin.php" class="btn btn-outline-secondary">No, Go Back</a>
                </form>
            <?php else: ?>
            <!-- Display the password reset form again in case of error -->
            <form action="edit_password.php" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($admin_id); ?>">
                <button type="submit" class="btn btn-outline-info">Reset Password</button>
                <a href="admin.php" class="btn btn-outline-secondary">Back to Admin Page</a>
            </form>
        <?php endif; ?>
    </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>
