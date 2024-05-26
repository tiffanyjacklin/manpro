<?php
session_start();
require "connect.php";

if (isset($_POST['item_ids_output'])) {
    $item_ids_output = $_POST['item_ids_output'];
    $item_ids_by_trucks = json_decode($item_ids_output, true);

    // Ensure that the JSON structure is as expected
    if (!is_array($item_ids_by_trucks) || !isset($item_ids_by_trucks[0]) || !isset($item_ids_by_trucks[3])) {
        echo "Unexpected JSON structure.";
        exit;
    }

    $trucks_ids = $item_ids_by_trucks[3];

    // Fetch the latest id_schedule
    $sql_id_schedule = "SELECT `id_schedule` FROM `schedule` ORDER BY `id` DESC LIMIT 1;";
    $res_id_schedule = mysqli_query($con, $sql_id_schedule);
    $latest_id_schedule = ($res_id_schedule && mysqli_num_rows($res_id_schedule) > 0) ? mysqli_fetch_assoc($res_id_schedule)['id_schedule'] : 0;

    $schedule_id_counter = $latest_id_schedule;
    $updated_trucks = [];
    $schedule_ids = [];
    $count = 1;

    foreach ($item_ids_by_trucks as $item_ids_by_truck) {
        if (is_array($item_ids_by_truck)) {
            foreach ($item_ids_by_truck as $truck_id => $item_ids) {
                if (is_array($item_ids) && !in_array("", $item_ids)) {
                    $schedule_id_counter++;
                }
                if (!empty($item_ids)) {
                    foreach ($item_ids as $item_id) {
                        $sql = $count == 1 ? "SELECT * FROM `item` WHERE `status` = 0 AND `id` = ?" : "SELECT * FROM `item` WHERE `id` = ?";
                        $stmt = mysqli_prepare($con, $sql);
                        mysqli_stmt_bind_param($stmt, "i", $item_id);
                        mysqli_stmt_execute($stmt);
                        $res = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($res) > 0) {
                            while ($row = mysqli_fetch_assoc($res)) {
                                if ($count == 1) {
                                    $sql_insert_schedule = "INSERT INTO `schedule` (`status`, `id_schedule`, `id_barang`, `id_location_from`, `id_location_dest`, `schedule_status`) VALUES (1, ?, ?, ?, ?, 1)";
                                    $stmt_insert = mysqli_prepare($con, $sql_insert_schedule);
                                    mysqli_stmt_bind_param($stmt_insert, "iiii", $schedule_id_counter, $item_id, $row['id_location_from'], $row['id_location_to']);
                                    mysqli_stmt_execute($stmt_insert);

                                    $sql_update_status = "UPDATE `item` SET status = 1 WHERE `id` = ?";
                                    $stmt_update_status = mysqli_prepare($con, $sql_update_status);
                                    mysqli_stmt_bind_param($stmt_update_status, "i", $item_id);
                                    mysqli_stmt_execute($stmt_update_status);

                                    if (!in_array($truck_id, $updated_trucks)) {
                                        $sql_check_truck_schedule = "SELECT * FROM `schedule` s JOIN `truck_driver` td ON s.id_schedule = td.id WHERE s.`id_barang` = ?";
                                        $stmt_check_truck_schedule = mysqli_prepare($con, $sql_check_truck_schedule);
                                        mysqli_stmt_bind_param($stmt_check_truck_schedule, "i", $item_id);
                                        mysqli_stmt_execute($stmt_check_truck_schedule);
                                        $res_check_truck_schedule = mysqli_stmt_get_result($stmt_check_truck_schedule);

                                        if (mysqli_num_rows($res_check_truck_schedule) == 0) {
                                            $sql_update_truck = "UPDATE `truck` SET `truck_status` = 3, `id_location` = ? WHERE `id` = ?";
                                            $stmt_update_truck = mysqli_prepare($con, $sql_update_truck);
                                            mysqli_stmt_bind_param($stmt_update_truck, "ii", $row['id_location_from'], $trucks_ids[$truck_id]);
                                            mysqli_stmt_execute($stmt_update_truck);
                                            $updated_trucks[] = $truck_id;

                                            $sql_insert_truck_driver = "INSERT INTO `truck_driver` (`id`, `id_truck`) VALUES (?, ?)";
                                            $stmt_insert_truck_driver = mysqli_prepare($con, $sql_insert_truck_driver);
                                            mysqli_stmt_bind_param($stmt_insert_truck_driver, "ii", $schedule_id_counter, $trucks_ids[$truck_id]);
                                            mysqli_stmt_execute($stmt_insert_truck_driver);
                                            $schedule_ids[] = $schedule_id_counter;
                                        }
                                    }
                                } else {
                                    $sql_insert = "INSERT INTO `schedule` (`status`, `id_schedule`, `id_barang`, `id_location_from`, `id_location_dest`, `schedule_status`) VALUES (0, ?, ?, ?, ?, ?)";
                                    $stmt_insert = mysqli_prepare($con, $sql_insert);
                                    mysqli_stmt_bind_param($stmt_insert, "iiiii", $schedule_id_counter, $item_id, $row['id_location_from'], $row['id_location_to'], $count);
                                    mysqli_stmt_execute($stmt_insert);
                                }
                            }
                        }
                    }
                }
            }
            $count++;
        }
    }
    // header("Location: schedule.php");
    header("Location: generate-driver.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Schedule</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
        <div class="container">
        <div class="generate-schedule">
        <?php
            $item_ids_output = exec("python ./mainGA.py");
            // $item_ids_output = exec("python ./ScheduleAssign.py");
            // echo $item_ids_output;
            // echo "HAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
            
            $item_ids_by_trucks = json_decode($item_ids_output);
            $item_ids_by_truck = $item_ids_by_trucks[0];
            $trucks_ids = $item_ids_by_trucks[3];
            
            if (is_array($item_ids_by_truck) AND is_array($trucks_ids)) {
                // echo '<pre>';
                // var_dump($item_ids_by_truck);
                // echo '</pre>';
                // foreach ($trucks_ids as $truck) {
                //     echo $truck;
                // }
                echo '<table class="table table-hover fixed-size-table table-generate" id="table-generate">';
                echo '<thead>';
                echo '<tr>';
                echo '<th scope="col">#</th>';
                echo '<th scope="col">Schedule ID</th>';
                echo '<th scope="col">Product</th>';
                echo '<th scope="col">Truck</th>';
                echo '<th scope="col">Origin Address</th>';
                echo '<th scope="col">Destination Address</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody class="table-group-divider">';
                $row_count = 1;
                $sql_id_schedule = "SELECT `id_schedule` FROM `schedule` ORDER BY `id` DESC LIMIT 1;"; 
                $res_id_schedule = mysqli_query($con, $sql_id_schedule);

                if ($res_id_schedule) {
                // Check if any rows were returned
                    if (mysqli_num_rows($res_id_schedule) > 0) {
                        // Fetch the result row as an associative array
                        $row = mysqli_fetch_assoc($res_id_schedule);
                        // Access the id_schedule value
                        $latest_id_schedule = $row['id_schedule'];
                        // Use the value as needed
                    } 
                }
                $schedule_id_counter = $latest_id_schedule;

                foreach ($item_ids_by_truck as $truck_id => $item_ids) {
                    if (!in_array("", $item_ids)) {
                        // Increment the schedule ID counter only if items exist for the truck
                        $schedule_id_counter++;
                    }
                    if (!empty($item_ids)) {
                        // Increment the schedule ID counter only if items exist for the truck
                    
                        
                        foreach ($item_ids as $item_id) {
                            // Fetch item details from the database
                            $sql = "SELECT * FROM `item` WHERE `status` = 0 AND `id` = '".$item_id."';";
                            $res = mysqli_query($con, $sql);
                            
                            if (mysqli_num_rows($res) > 0) {
                                while ($row = mysqli_fetch_array($res)) {
                                    
                                    echo '<tr>';
                                    echo '<td>' . $row_count . '</td>';
                                    $row_count = $row_count + 1;

                                    echo '<td>' . $schedule_id_counter . '</td>';
                                    echo '<td>
                                            Product ID: '.$row['id'].' <br>
                                            Product: '.$row['item_name'].' <br>
                                            Dimension: ' . $row['panjang'] . 'cm x ' . $row['lebar'] . 'cm x ' . $row['tinggi'] . 'cm <br>
                                            Order received: '.$row['order_received'].'
                                        </td>';
                                    
                                        
                                    $sql_truck = "SELECT * FROM `truck` WHERE id = ".($trucks_ids[$truck_id]).";";
                                    $res_truck = mysqli_query($con, $sql_truck);
                                    if (mysqli_num_rows($res_truck) > 0) {
                                    while ($row_truck = mysqli_fetch_array($res_truck)) {
                                        echo '<td>
                                                    Truck ID:  ' . $row_truck['id'].'<br>
                                                    Unique number: ' . $row_truck['unique_number'].'<br>';
                
                                        // $sql_driver = "SELECT * FROM `truck` t JOIN `truck_driver` td ON t.`id` = td.`id_truck`
                                        //             JOIN `driver` d ON td.`id_driver` = d.`id` 
                                        //             WHERE t.`id` = ".$row_truck['id']." ORDER BY td.`position`;";
                                        // $res_driver = mysqli_uery($con, $sql_driver);
                                        // if (mysqli_num_rows($res_driver) > 0) {
                                        // while ($row_driver = mysqli_fetch_array($res_driver)) {
                                        //     echo 'Driver ' . $row_driver['position'] . ': ' . $row_driver['driver_name'].'<br>';
                                        // }
                                        // }
                                        echo '</td>';                      
                                    }
                                    }
                                    // Fetch origin and destination addresses
                                    $address_sql = "SELECT * FROM `location` WHERE id = " . $row['id_location_from'] . " OR id = " . $row['id_location_to'];
                                    $address_res = mysqli_query($con, $address_sql);
                                    if (mysqli_num_rows($address_res) > 0) {
                                        while ($address_row = mysqli_fetch_array($address_res)) {
                                            echo '<td>' . $address_row['alamat'] . ',<br>' . $address_row['kelurahan_desa'] . ',<br>' . $address_row['kecamatan'] . ',<br>' . $address_row['kota_kabupaten'] . ',<br> Jawa Timur ' . $address_row['kode_pos'] . '</td>';
                                        }
                                    }
                                    echo '</tr>';
                                }
                            } 
                        }
                    }
                }

                echo '</tbody>';
                echo '</table>';
            } 
            ?>
            <div style="text-align: center;">
                <form id="scheduleForm" action="generate-schedule.php" method="POST">
                    <input type="hidden" name="item_ids_output" value="<?php echo htmlspecialchars($item_ids_output); ?>">

                    <div style="text-align: center;">
                        <!-- Modify the button to submit the form when clicked -->
                        <button type="button" class="btn btn-outline-info" onclick="window.location.href=window.location.href">Regenerate Schedule</button>
                        <button type="submit" form="scheduleForm" class="btn btn-outline-info">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    <?php
      include('footer.php');
    ?>
    </div>
    <script>
        $(document).ready(function() {
            $('#table-generate').DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"generate1"lfB><"generateBody"t><"generate2"ipr>'
            });
        });
    </script>

</body>
</html>