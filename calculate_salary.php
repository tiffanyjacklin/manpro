<?php
session_start();
require "connect.php";
$user_id = $_SESSION['user_id'];

// Define the rate per 10 km
$rate_per_10km = 8000;

// Retrieve distance logs for the previous month
$sql_distance = "SELECT * FROM `driver` WHERE total_distance != 0 AND `id` = 6 ORDER BY `id`;";
// $sql_distance = "SELECT * FROM `driver` WHERE total_distance != 0 AND id < 11 ORDER BY `id`;";
            
$res_distance = mysqli_query($con, $sql_distance);
if (mysqli_num_rows($res_distance) > 0) {
    while ($row = mysqli_fetch_array($res_distance)) {
        $driver_id = $row['id'];
        $total_distance = $row['total_distance'];
        $salary = ($total_distance / 10) * $rate_per_10km;
        
        $sql_insert_salary = "INSERT INTO `transaction` (`status`, `date_time`, `nominal`, `id_driver`) 
                            VALUES (2, current_timestamp(), ".$salary.", ".$driver_id.");";
        mysqli_query($con, $sql_insert_salary);     
        echo $sql_insert_salary;
        echo "<br>";
        $sql_update_distance = "UPDATE `driver` SET `total_distance` = 0 WHERE `id` = ".$driver_id.";";
        mysqli_query($con, $sql_update_distance);     

        mysqli_query($con, "INSERT INTO `log` (`id_admin`, `id_table`, `action`, `detail_action`, `timestamp`) VALUES ($user_id, 4, 5, 'ID Driver: ".$driver_id.", Salary: Rp".$salary."', current_timestamp()); ");
        
    }
}
header("Location: transaction.php");

?>
