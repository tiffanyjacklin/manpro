<?php
session_start();
require "connect.php";

// Fetch the current user's data
$user_id = $_SESSION['user_id'];
$query = $con->prepare("SELECT * FROM admin WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$manager = $result->fetch_assoc();

// Fetch all admins for the dropdown menu
$all_admins_query = "SELECT id, username FROM admin";
$all_admins_result = mysqli_query($con, $all_admins_query);

$selected_user_id = $user_id;
$notification = ''; // Initialize notification variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_password'])) {
        // Process password verification
        $selected_user_id = $_POST['selected_user_id'];
        $password = $_POST['password'];
    
        // Query to check if the password is correct
        $check_password_query = "SELECT password FROM admin WHERE id = ?";
        $check_password_stmt = $con->prepare($check_password_query);
        $check_password_stmt->bind_param("i", $selected_user_id);
        $check_password_stmt->execute();
        $check_password_result = $check_password_stmt->get_result();
        $row = $check_password_result->fetch_assoc();
        $hashed_password = $row['password'];
    
        if (password_verify($password, $hashed_password)) {
            // Password is correct, enable editing
            $_SESSION['verified_user_id'] = $selected_user_id;
            header("Location: profile_password.php");
            exit();
        } else {
            // Incorrect password
            $notification = '<div class="alert alert-danger" role="alert">Incorrect password. Please try again.</div>';
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
            <ul class="nav nav-tabs">
                <?php if ($manager['position'] == 1): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo isset($_GET['tab']) && $_GET['tab'] == 'pegawai' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#pegawai">Pegawai</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo isset($_GET['tab']) && $_GET['tab'] == 'profile' ? 'active' : ''; ?>" data-bs-toggle="tab" href="#profile">Profile</a>
                </li>
            </ul>

            <div class="tab-content">
                <?php if ($manager['position'] == 1): ?>
                <div class="tab-pane fade <?php echo isset($_GET['tab']) && $_GET['tab'] == 'pegawai' ? 'show active' : ''; ?>" id="pegawai">
                    <!-- Content for pegawai tab-panel -->
                    <button type="button" class="btn btn-outline-info" onclick="window.location.href='add_employee.php'" style="margin-top:20px;">Add Employee</button>
                    <table class="table table-hover fixed-size-table" id="table-pegawai">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Position</th>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM admin";
                            $result = mysqli_query($con, $query);

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                echo "<td>{$row['username']}</td>";
                                echo "<td>";
                                if ($row['position'] == 1){
                                    echo '<span class="badge rounded-pill text-bg-info">Manager</span>';
                                }else{
                                    echo '<span class="badge rounded-pill text-bg-warning">Pegawai</span>';
                                }
                                echo "</td>";
                                echo "<td>{$row['name']}</td>";
                                echo "<td>{$row['phone_number']}</td>";
                                echo "<td>{$row['address']}</td>";
                                echo '<td>';
                                echo '<form action="edit_password.php" method="POST" style="display: inline;">';
                                echo '<input type="hidden" name="id" value="'.$row['id'].'">';
                                echo '<button type="submit" class="btn btn-outline-info btn-sm">Edit Password</button>';
                                echo '</form>';
                                echo '</td>';
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <div class="tab-pane fade <?php echo isset($_GET['tab']) && $_GET['tab'] == 'profile' ? 'show active' : ''; ?>" id="profile">
                    <!-- Content for profile tab-panel -->
                    <h3>Profile</h3>
                    <?php echo $notification; ?>
                    <table class="table table-hover fixed-size-table" id="table-profile">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Position</th>
                                <th>Name</th>
                                <th>Phone Number</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT * FROM admin";
                            $result = mysqli_query($con, $query);

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                echo "<td>{$row['username']}</td>";
                                echo "<td>{$row['position']}</td>";
                                echo "<td>{$row['name']}</td>";
                                echo "<td>{$row['phone_number']}</td>";
                                echo "<td>{$row['address']}</td>";
                                echo '<td>';
                                echo '<button class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#verifyPasswordModal'.$row['id'].'">Password</button>';
                                echo '</td>';
                                echo "</tr>";

                                // Password Verification Modal
                                echo '<div class="modal fade" id="verifyPasswordModal'.$row['id'].'" tabindex="-1" aria-labelledby="verifyPasswordModalLabel'.$row['id'].'" aria-hidden="true">';
                                echo '<div class="modal-dialog">';
                                echo '<div class="modal-content">';
                                echo '<div class="modal-header">';
                                echo '<h5 class="modal-title" id="verifyPasswordModalLabel'.$row['id'].'">Verify Password</h5>';
                                echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                                echo '</div>';
                                echo '<form method="POST">';
                                echo '<div class="modal-body">';
                                echo '<input type="hidden" name="selected_user_id" value="'.$row['id'].'">';
                                echo '<div class="form-group">';
                                echo '<label for="password">Password:</label>';
                                echo '<input type="password" class="form-control" name="password" required>';
                                echo '</div>';
                                echo '</div>';
                                echo '<div class="modal-footer">';
                                echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
                                echo '<button type="submit" class="btn btn-primary" name="verify_password">Verify Password</button>';
                                echo '</div>';                                                            
                                echo '</form>';
                                echo '</div>';
                                echo '</div>';
                                echo '</div>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#table-pegawai').DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"generate1"lfB><"generateBody"t><"generate2"ipr>'
            });
            $('#table-profile').DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"generate1"lfB><"generateBody"t><"generate2"ipr>'
            });
            var urlParams = new URLSearchParams(window.location.search);
            var tabParam = urlParams.get('tab');
            if (tabParam) {
                $('a[href="#' + tabParam + '"]').addClass('active');
                $('#' + tabParam).addClass('show active');
            } else {
                <?php if ($manager['position'] == 1): ?>
                $('a[href="#pegawai"]').addClass('active');
                $('#pegawai').addClass('show active');
                <?php else: ?>
                $('a[href="#profile"]').addClass('active');
                $('#profile').addClass('show active');
                <?php endif; ?>
            }
        });
    </script>

    <?php include('footer.php'); ?>
</body>
</html>
