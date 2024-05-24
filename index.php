<!-- <script>
    window.location.href = 'dashboard.php';
</script> -->

<?php
session_start();

// if(isset($_POST['logout'])){
//     session_destroy(); // Hapus semua data sesi
//     header("Location: login.php");
//     exit(); // Hentikan eksekusi skrip agar tidak melanjutkan ke bagian bawah
// }
if(isset($_SESSION['admin'])){
    header("Location: dashboard.php");
    exit(); // Hentikan eksekusi skrip agar tidak melanjutkan ke bagian bawah
}else{
    header("Location: login.php");
}

?>