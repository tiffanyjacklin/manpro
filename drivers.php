<?php
session_start();
require "connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Driver</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
      <div class="container">
        <button type="button" class="btn btn-outline-info" onclick="window.location.href='calculate_salary.php'" style="margin-top:20px;">Pay Salary</button>


      </div>
    <?php
      include('footer.php');
    ?>
    </div>


</body>
</html>