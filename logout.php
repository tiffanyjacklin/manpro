<?php
    session_start();
    require "connect.php";

// if(isset($_POST['logout'])){
    session_destroy(); // Hapus semua data sesi
    unset($_SESSION["username"]);
    unset($_SESSION["password"]);
    header("Location: login.php");
    exit(); // Hentikan eksekusi skrip agar tidak melanjutkan ke bagian bawah
// }
?>