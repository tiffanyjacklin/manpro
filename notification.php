<?php
session_start();
require "connect.php";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Notification</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
    
      <div class="container">
      </div>
    <div class="alert alert-warning" role="alert">
      A simple warning alert—check it out!
    </div>
    <div class="alert alert-info" role="alert">
      A simple info alert—check it out!
    </div>


    <?php
      include('footer.php');
    ?>
    </div>


</body>
</html>