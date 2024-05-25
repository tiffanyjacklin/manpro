<?php
session_start();
require "connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user has verified password
if (!isset($_SESSION['verified_user_id'])) {
    header("Location: admin.php");
    exit();
}

// Fetch the current user's data
$user_id = $_SESSION['verified_user_id'];
$query = $con->prepare("SELECT * FROM admin WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$manager = $result->fetch_assoc();

$notification = ''; // Initialize notification variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process profile update
    $name = $_POST['name'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];
    
    // Query to update profile data
    $update_profile_query = "UPDATE admin SET name = ?, phone_number = ?, address = ? WHERE id = ?";
    $update_profile_stmt = $con->prepare($update_profile_query);
    $update_profile_stmt->bind_param("sssi", $name, $phone_number, $address, $user_id);
    
    if ($update_profile_stmt->execute()) {
        // Profile successfully updated
        $notification = '<div class="alert alert-success" role="alert">Profile updated successfully!</div>';
        unset($_SESSION['verified_user_id']); // Unset verified session variable
    } else {
        // Error updating profile
        $notification = '<div class="alert alert-danger" role="alert">Error updating profile. Please try again.</div>';
    }

    // Process password change if provided
    if (!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];

        // Query to check if old password is correct
        $check_password_query = "SELECT password FROM admin WHERE id = ?";
        $check_password_stmt = $con->prepare($check_password_query);
        $check_password_stmt->bind_param("i", $user_id);
        $check_password_stmt->execute();
        $check_password_result = $check_password_stmt->get_result();
        $row = $check_password_result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($old_password, $hashed_password)) {
            // Old password is correct, proceed with changing password
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_password_query = "UPDATE admin SET password = ? WHERE id = ?";
            $update_password_stmt = $con->prepare($update_password_query);
            $update_password_stmt->bind_param("si", $hashed_new_password, $user_id);
            if ($update_password_stmt->execute()) {
                // Password successfully updated
                $notification = '<div class="alert alert-success" role="alert">Password updated successfully!</div>';
                unset($_SESSION['verified_user_id']); // Unset verified session variable
            } else {
                // Error updating password
                $notification = '<div class="alert alert-danger" role="alert">Error updating password. Please try again.</div>';
            }
        } else {
            // Old password is incorrect
            $notification = '<div class="alert alert-danger" role="alert">Incorrect old password. Please try again.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Admin</title>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="main-content">
        <div class="container">
            <h3>Edit Profile & Change Password</h3>
            <?php echo $notification; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $manager['name']; ?>">
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo $manager['phone_number']; ?>">
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?php echo $manager['address']; ?>">
                </div>
                <hr>
                <div class="mb-3">
                    <label for="old_password" class="form-label">Old Password</label>
                    <input type="password" class="form-control" id="old_password" name="old_password">
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password">
                </div>
                <!-- Only display the Update Data button -->
                <button type="submit" class="btn btn-primary" name="update_profile">Update Data</button>
            </form>
            <br>
            <!-- Add a button to navigate back to admin page -->
            <a href="admin.php" class="btn btn-secondary">Back to Admin Page</a>
        </div>
    </div>

    <?php include('footer.php'); ?>
</body>
</html>
