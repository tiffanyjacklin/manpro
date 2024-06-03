<?php
include "database.php";
require "connect.php";
session_start();
$user_id = $_SESSION['user_id'];
$add_message = "";

if (isset($_POST["add"])){
    // Check if all required fields are not empty
    if (!empty($_POST['Username']) && !empty($_POST['Password']) && !empty($_POST['Position']) && !empty($_POST['Name']) && !empty($_POST['Phone_number']) && !empty($_POST['Address'])) {
        // Assign POST values to variables
        // $id = $_POST['Id'];
        $username = $_POST['Username'];
        $password1 = $_POST['Password'];
        
        // Prepare the command to hash the password
        $function_name = "hash_password";
        $command = escapeshellcmd("python ./password.py " . escapeshellarg($function_name) . " " . escapeshellarg($password1));
        $hashed_password = trim(shell_exec($command));  // Capture and trim the output

        // If the hashing was successful, continue
        if (!empty($hashed_password)) {
            $position = $_POST['Position'];
            $name = $_POST['Name'];
            $phone_number = $_POST['Phone_number'];
            $address = $_POST['Address'];

            // Create and execute the SQL query
            $sql = "INSERT INTO admin (Username, Password, Position, Name, Phone_number, Address)
                    VALUES ('$username', '$hashed_password', '$position', '$name', '$phone_number', '$address')";
            
            if($db->query($sql)){
                $add_message = "Admin berhasil ditambahkan";
                $res_admin = mysqli_query($con, "SELECT id FROM `admin` ORDER BY `id` DESC LIMIT 1");
                if (mysqli_num_rows($res_admin)){
                    $row_admin = mysqli_fetch_assoc($res_admin);
                    mysqli_query($con, "INSERT INTO `log` (`id_admin`, `id_table`, `action`, `detail_action`, `timestamp`) VALUES (".$user_id.", 5, 1, 'ID: ".$row_admin['id'].", Name: ".$name."', current_timestamp()); ");
                }
            } else {
                $add_message = "Data admin tidak masuk";
            }
        } else {
            $add_message = "Gagal untuk meng-hash password";
        }
    } else {
        $add_message = "Semua field harus diisi";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Add Employee</title>
</head>
<body>
    <?php   
      include('navbar.php');
    ?>
    <div class="main-content">
        <div class="container mt-3">
            <?php if ($add_message): ?>
                <div class="alert <?= $add_message == 'Admin berhasil ditambahkan' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                    <?= $add_message ?>
                </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-header">
                    <h3>Add Admin</h3>
                </div>
                <div class="card-body">
                    <form action="add_employee.php" method="POST">
                        <!-- <div class="form-group">
                            <label for="Id">Id</label>
                            <input type="number" class="form-control" id="Id" placeholder="Enter Id" name="Id" required>
                        </div> -->
                        <div class="form-group">
                            <label for="Username">Username</label>
                            <input type="text" class="form-control" id="Username" placeholder="Enter Username" name="Username" required>
                        </div>
                        <div class="form-group">
                            <label for="Password">Password</label>
                            <input type="text" class="form-control" id="Password" placeholder="Enter Password" name="Password" required>
                        </div>
                        <div class="form-group">
                            <label for="Position">Position</label>
                            <select class="form-control" id="Position" name="Position" required>
                                <option value="1">Manager</option>
                                <option value="2">Pegawai</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Name">Name</label>
                            <input type="text" class="form-control" id="Name" placeholder="Enter Name" name="Name" required>
                        </div>
                        <div class="form-group">
                            <label for="Phone_number">Phone Number</label>
                            <input type="text" class="form-control" id="Phone_number" placeholder="Enter Phone Number" name="Phone_number" required>
                        </div>
                        <div class="form-group">
                            <label for="Address">Address</label>
                            <input type="text" class="form-control" id="Address" placeholder="Enter Address" name="Address" required>
                        </div>
                        <button type="submit" class="btn btn-info mt-3" name="add">Daftarkan Admin</button>
                    </form>
                    <button class="btn btn-outline-secondary mt-3" onclick="window.location.href='admin.php'">Kembali ke Halaman Admin</button>
                </div>
            </div>
           
        </div>
        <?php
            include('footer.php');
            ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
