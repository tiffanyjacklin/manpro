<!-- <script>
    window.location.href = 'dashboard.php';
</script> -->

<?php
session_start();

if(isset($_POST['logout'])){
    session_destroy(); // Hapus semua data sesi
    header("Location: login.php");
    exit(); // Hentikan eksekusi skrip agar tidak melanjutkan ke bagian bawah
}
if(!isset($_SESSION['admin'])){
    header("Location: dashboard.php");
    exit(); // Hentikan eksekusi skrip agar tidak melanjutkan ke bagian bawah
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Index Page</title>
</head>
<body>
    <h2>Welcome to Admin Dashboard!</h2>
    <form method="post" action="">
        <input type="submit" name="logout" value="Log Out">
    </form>
</body>
</html>
