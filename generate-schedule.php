<?php
session_start();
require "connect.php";


if (isset($_POST['item_ids_output'])) {
    $item_ids_output = $_POST['item_ids_output'];
        // $item_ids_output = exec("python ./mainGA.py");
    // echo $item_ids_output;
                
    $item_ids_by_trucks = json_decode($item_ids_output);
    // $item_ids_by_truck = $item_ids_by_trucks[0];
    $count = 1;

    foreach ($item_ids_by_trucks as $item_ids_by_truck) {
        if (is_array($item_ids_by_truck)) {
            
            $sql_id_schedule = "SELECT `id_schedule` FROM `schedule` ORDER BY `id` DESC LIMIT 1;"; 
            $res_id_schedule = mysqli_query($con, $sql_id_schedule);

            if ($res_id_schedule) {
                if (mysqli_num_rows($res_id_schedule) > 0) {
                    $row = mysqli_fetch_assoc($res_id_schedule);
                    $latest_id_schedule = $row['id_schedule'];
                } 
            }
            $schedule_id_counter = $latest_id_schedule;
            $updated_trucks = [];

            foreach ($item_ids_by_truck as $truck_id => $item_ids) {
                if (!in_array("", $item_ids)) {
                    $schedule_id_counter++;
                }
                if (!empty($item_ids)) {
                    foreach ($item_ids as $item_id) {
                        if ($count == 1){
                            $sql = "SELECT * FROM `item` WHERE `status` = 0 AND `id` = '".$item_id."';";    
                        }else{
                            // echo "GAMASUK TA IKI<br>";
                            $sql = "SELECT * FROM `item` WHERE `id` = '".$item_id."';";    
                        }
                        $res = mysqli_query($con, $sql);
                        

                        if (mysqli_num_rows($res) > 0) {
                            while ($row = mysqli_fetch_array($res)) {
                                if ($count == 1){
                                    $sql_insert = "INSERT INTO `schedule` (`status`, `id_schedule`, `id_barang`, `id_truk`, `id_location_from`, `id_location_dest`, `schedule_status`) VALUES (1, ".$schedule_id_counter.",  ".$item_id.", ".($truck_id+1).", ".$row['id_location_from'].", ".$row['id_location_to'].",1);"; 
                                    mysqli_query($con,$sql_insert);
        
                                    $sql_update_status = "UPDATE `item` SET status = 1 WHERE `id` = '".$item_id."';";
                                    mysqli_query($con,$sql_update_status);
        
                                    if (!in_array($truck_id, $updated_trucks)) {
                                        $sql_update_truck = "UPDATE `truck` SET id_location = ".$row['id_location_from']." WHERE `id` = '".($truck_id+1)."'";
                                        mysqli_query($con,$sql_update_truck);
                                        $updated_trucks[] = $truck_id;
                                    }
                                }
                                else{
                                    // echo "MASUK KOK";
                                    $sql_insert = "INSERT INTO `schedule` (`status`, `id_schedule`, `id_barang`, `id_truk`, `id_location_from`, `id_location_dest`, `schedule_status`) VALUES (0, ".$schedule_id_counter.",  ".$item_id.", ".($truck_id+1).", ".$row['id_location_from'].", ".$row['id_location_to'].",$count);"; 
                                    mysqli_query($con,$sql_insert);
                                }
                                
                            }
                        } 
                    }
                }
            }
            // $count++;
        } $count++;
        // echo $count;
    }
    header("Location: schedule.php");
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
            echo $item_ids_output;
            
            $item_ids_by_trucks = json_decode($item_ids_output);
            $item_ids_by_truck = $item_ids_by_trucks[0];
            
            if (is_array($item_ids_by_truck)) {
                // echo '<pre>';
                // var_dump($item_ids_by_truck);
                // echo '</pre>';

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
                                    $sql_truck = "SELECT * FROM `truck` WHERE id = ".($truck_id+1).";";
                                    $res_truck = mysqli_query($con, $sql_truck);
                                    if (mysqli_num_rows($res_truck) > 0) {
                                    while ($row_truck = mysqli_fetch_array($res_truck)) {
                                        echo '<td>
                                                    Truck ID:  ' . $row_truck['id'].'<br>
                                                    Unique number: ' . $row_truck['unique_number'].'<br>';
                
                                        $sql_driver = "SELECT * FROM `truck` t JOIN `truck_driver` td ON t.`id` = td.`id_truck`
                                                    JOIN `driver` d ON td.`id_driver` = d.`id` 
                                                    WHERE t.`id` = ".$row_truck['id']." ORDER BY td.`position`;";
                                        $res_driver = mysqli_query($con, $sql_driver);
                                        if (mysqli_num_rows($res_driver) > 0) {
                                        while ($row_driver = mysqli_fetch_array($res_driver)) {
                                            echo 'Driver ' . $row_driver['position'] . ': ' . $row_driver['driver_name'].'<br>';
                                        }
                                        }
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
                <form id="scheduleForm" action="generate-schedule.php" method="POST" style="display: none;">
                    <input type="hidden" name="item_ids_output" value="<?php echo htmlspecialchars($item_ids_output); ?>">
                </form>

                <div style="text-align: center;">
                    <!-- Modify the button to submit the form when clicked -->
                    <button type="button" class="btn btn-outline-info" onclick="window.location.href=window.location.href">Regenerate Schedule</button>
                    <button type="submit" form="scheduleForm" class="btn btn-outline-info">Save Schedule</button>
                </div>
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