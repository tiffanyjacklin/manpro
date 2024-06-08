<?php
session_start();

if(isset($_SESSION['admin'])){
    header("Location: dashboard.php");
}

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Lakukan koneksi ke database
    $conn = new mysqli('localhost', 'root', '', 'logistics_company');

    // Check koneksi
    if($conn->connect_error){
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Lakukan query untuk mencari admin dengan username tertentu
    $sql = "SELECT * FROM admin WHERE username='$username'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        // Verify password
        $hashed_password_from_db = $row['password'];
        $command = escapeshellcmd("python ./password.py check_password " . escapeshellarg($password) . " " . escapeshellarg($hashed_password_from_db));
        $output = trim(shell_exec($command));
        
        if($output === "True"){   
            // Set session for admin
            $_SESSION['admin'] = $row['username'];
            // Set additional session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['position'] = $row['position']; // Add position to session
        
            // Redirect to appropriate page based on user position
            if($row['position'] == 1) {
                header("Location: dashboard.php"); // Admin
            } else if ($row['position'] == 2) {
                header("Location: dashboard.php"); // Employee
            } else if ($row['position'] == 3) {
                $_SESSION['driver_name'] = $row['username']; // Set driver name in session
                header("Location: dashboard.php"); // Driver
            }
            exit();
        }
        else {
            echo "<div class='alert alert-info' role='alert'>Password salah.</div>";
            echo "<script>setTimeout(function() { $('.alert').fadeOut('slow'); }, 5000);</script>";
        }
    } else {
        echo "<div class='alert alert-info' role='alert'>Username tidak ditemukan.</div>";
        echo "<script>setTimeout(function() { $('.alert').fadeOut('slow'); }, 5000);</script>";
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html>
<head>
    <?php include('head1.php'); ?>
    <title>TIP LOGISTICS | Login Page</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="fonts/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/select2/select2.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
</head>
<body>
    
    <div class="limiter">
        <div class="container-login100" style="background-color: rgba(178, 235, 242, 0.5); ">
            <div class="wrap-login100 p-l-55 p-r-55 p-t-65 p-b-54">
                <form class="login100-form validate-form" method="post" action="login.php">
                    <span class="login100-form-title p-b-49">
                        Login
                    </span>

                    <div class="wrap-input100 validate-input m-b-23" data-validate = "Username is required">
                        <span class="label-input100">Username</span>
                        <input class="input100" type="text" name="username" placeholder="Type your username" required>
                        <span class="focus-input100" data-symbol="&#xf206;"></span>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="Password is required">
                        <span class="label-input100">Password</span>
                        <input class="input100" type="password" id="password" name="password" placeholder="Type your password" required>
                        <span class="focus-input100" data-symbol="&#xf190;"></span>
                        
                    </div>
                    
                    <div class="text-right p-t-8 p-b-31">
                        <input class="form-check-input" type="checkbox" id="showPasswordCheckbox" onchange="togglePasswordVisibility()"> Show Password
                    </div>
                    
                    <div class="container-login100-form-btn">
                        <div class="wrap-login100-form-btn">
                            <div class="login100-form-bgbtn"></div>
                            <button class="login100-form-btn" type="submit" name="login">
                                Login
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div id="dropDownSelect1"></div>
    <script>
        function togglePasswordVisibility() {
            var passwordField = document.getElementById("password");
            var showPasswordCheckbox = document.getElementById("showPasswordCheckbox");
            passwordField.type = showPasswordCheckbox.checked ? "text" : "password";
        }
    </script>
    <script src="vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="vendor/animsition/js/animsition.min.js"></script>
    <script src="vendor/bootstrap/js/popper.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="vendor/select2/select2.min.js"></script>
    <script src="vendor/daterangepicker/moment.min.js"></script>
    <script src="vendor/daterangepicker/daterangepicker.js"></script>
    <script src="vendor/countdowntime/countdowntime.js"></script>
    <script src="js/main.js"></script>

</body>
</html>
