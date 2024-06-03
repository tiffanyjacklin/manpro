<?php
session_start();
require "connect.php";
// set_time_limit(100);
$user_id = $_SESSION['user_id'];

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// ini_set('log_errors', 1);
// ini_set('error_log', 'path_to_error_log_file.log');

if (isset($_POST['item_ids_output']) && isset($_POST['selected_schedule'])) {
    $item_ids_output = $_POST['item_ids_output'];
    $selected_best = $_POST['selected_schedule'];
    
    echo $item_ids_output;
    echo $selected_best;
    $item_ids_by_trucks = json_decode($item_ids_output, true);

    // Ensure that the JSON structure is as expected
    if (!is_array($item_ids_by_trucks) || !isset($item_ids_by_trucks[0]) || !isset($item_ids_by_trucks[1]) || !isset($item_ids_by_trucks[2]) || !isset($item_ids_by_trucks[3])) {
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
        $is_selected_best = ($count == $selected_best);
        if (is_array($item_ids_by_truck)) {
            foreach ($item_ids_by_truck as $truck_id => $item_ids) {
                if (is_array($item_ids) && !in_array("", $item_ids)) {
                    $schedule_id_counter++;
                    echo $schedule_id_counter;
                }
                if (!empty($item_ids)) {
                    foreach ($item_ids as $item_id) {
                        $sql = $count == 1 ? "SELECT * FROM `item` WHERE `status` = 0 AND `id` = ?" : "SELECT * FROM `item` WHERE `id` = ?";
                        echo $sql; 
                        $stmt = mysqli_prepare($con, $sql);
                        mysqli_stmt_bind_param($stmt, "i", $item_id);
                        mysqli_stmt_execute($stmt);
                        $res = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($res) > 0) {
                            while ($row = mysqli_fetch_assoc($res)) {
                                $schedule_status = $is_selected_best ? 1 : 0; // Set the status based on whether it's the selected best schedule
                                $sql_insert_schedule = "INSERT INTO `schedule` (`status`, `id_schedule`, `id_barang`, `id_location_from`, `id_location_dest`, `schedule_status`) VALUES (?, ?, ?, ?, ?, ?)";
                                echo $sql; 
                                
                                $stmt_insert = mysqli_prepare($con, $sql_insert_schedule);
                                mysqli_stmt_bind_param($stmt_insert, "iiiiii", $schedule_status, $schedule_id_counter, $item_id, $row['id_location_from'], $row['id_location_to'], $count);
                                mysqli_stmt_execute($stmt_insert);
                                if ($is_selected_best) {
                                    $sql_update_status = "UPDATE `item` SET status = 1 WHERE `id` = ?";
                                    echo $sql_update_status; 
                                    $stmt_update_status = mysqli_prepare($con, $sql_update_status);
                                    mysqli_stmt_bind_param($stmt_update_status, "i", $item_id);
                                    mysqli_stmt_execute($stmt_update_status);
                                    mysqli_query($con, "INSERT INTO `log` (`id_admin`, `id_table`, `action`, `detail_action`, `timestamp`) VALUES (".$user_id.", 3, 4, 'ID Item: ".$item_id."', current_timestamp()); ");
                                    // $detail_action = "ID Item: " . $item_id;

                                    // $sql_insert_log_item = "INSERT INTO `log` (`id_admin`, `id_table`, `action`, `detail_action`, `timestamp`) VALUES (?, ?, ?, ?, current_timestamp())";
                                    // $stmt_insert_log_item = mysqli_prepare($con, $sql_insert_log_item);
                                    // mysqli_stmt_bind_param($stmt_insert_log_item, "iiis", $user_id, 3, 4, $detail_action);
                                    // mysqli_stmt_execute($stmt_insert_log_item);
                                   
                                    

                                    if (!in_array($truck_id, $updated_trucks)) {
                                        $sql_check_truck_schedule = "SELECT * FROM `schedule` s JOIN `truck_driver` td ON s.id_schedule = td.id WHERE s.`id_barang` = ?";
                                        echo $sql_check_truck_schedule; 
                                        $stmt_check_truck_schedule = mysqli_prepare($con, $sql_check_truck_schedule);
                                        mysqli_stmt_bind_param($stmt_check_truck_schedule, "i", $item_id);
                                        mysqli_stmt_execute($stmt_check_truck_schedule);
                                        $res_check_truck_schedule = mysqli_stmt_get_result($stmt_check_truck_schedule);

                                        if (mysqli_num_rows($res_check_truck_schedule) == 0) {
                                            $sql_update_truck = "UPDATE `truck` SET `truck_status` = 3, `id_location` = ? WHERE `id` = ?";
                                            echo $sql_update_truck; 
                                            $stmt_update_truck = mysqli_prepare($con, $sql_update_truck);
                                            mysqli_stmt_bind_param($stmt_update_truck, "ii", $row['id_location_from'], $trucks_ids[$truck_id]);
                                            mysqli_stmt_execute($stmt_update_truck);
                                            $updated_trucks[] = $truck_id;

                                            $sql_insert_truck_driver = "INSERT INTO `truck_driver` (`id`, `id_truck`) VALUES (?, ?)";
                                            echo $sql_insert_truck_driver; 
                                            $stmt_insert_truck_driver = mysqli_prepare($con, $sql_insert_truck_driver);
                                            mysqli_stmt_bind_param($stmt_insert_truck_driver, "ii", $schedule_id_counter, $trucks_ids[$truck_id]);
                                            mysqli_stmt_execute($stmt_insert_truck_driver);
                                            $schedule_ids[] = $schedule_id_counter;

                                            mysqli_query($con, "INSERT INTO `log` (`id_admin`, `id_table`, `action`, `detail_action`, `timestamp`) VALUES (".$user_id.", 2, 4, 'ID Schedule: ".$schedule_id_counter."', current_timestamp()); ");

                                            // $sql_insert_log_schedule = "INSERT INTO `log` (`id_admin`, `id_table`, `action`, `detail_action`, `timestamp`) VALUES (?, ?, ?, ?, current_timestamp())";
                                            // $stmt_insert_log_schedule = mysqli_prepare($con, $sql_insert_log_schedule);
                                            // mysqli_stmt_bind_param($stmt_insert_log_schedule, "iiis", $user_id, 2, 4, "ID Schedule: " . $schedule_id_counter);
                                            // mysqli_stmt_execute($stmt_insert_log_schedule);
                                        
                                        }
                                    }

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
    // header("Location: generate-schedule.php");
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
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link" href="#1best">First Best</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#2best">Second Best</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#3best">Third Best</a>
                    </li>
                </ul>
                

                <?php
                    $item_ids_output = exec("python ./mainGA.py");
                    // $item_ids_output = exec("python ./ScheduleAssign.py");
                    // echo $item_ids_output;
                    // echo "HAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA";
                    
                    $item_ids_by_trucks = json_decode($item_ids_output);
                    if (!is_array($item_ids_by_trucks) || !isset($item_ids_by_trucks[0]) || !isset($item_ids_by_trucks[1]) || !isset($item_ids_by_trucks[2]) || !isset($item_ids_by_trucks[3])) {
                        echo "Unexpected JSON structure.";
                        // exit;
                        $trucks_ids = $item_ids_by_trucks[1];
                        $max_index = 1;
                    }else{
                        $trucks_ids = $item_ids_by_trucks[3];
                        $max_index = 3;
                    }

                    for ($i = 0; $i < $max_index; $i++) {
                        echo '<div class="tab-content">
                        <div class="tab-pane fade' . ($i == 0 ? 'show active' : '') . '" id="'.($i+1).'best">';
                        $item_ids_by_truck = $item_ids_by_trucks[$i];
                        

                        // echo $item_ids_by_truck;
                        if (is_array($item_ids_by_truck) AND is_array($trucks_ids)) {
                            // echo '<pre>';
                            // var_dump($item_ids_by_truck);
                            // echo '</pre>';
                            // foreach ($trucks_ids as $truck) {
                            //     echo $truck;
                            // }
                            echo '<table class="table table-hover fixed-size-table table-generate" id="table-'.($i+1).'best">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th scope="col">#</th>';
                            echo '<th scope="col">Schedule ID</th>';
                            echo '<th scope="col">Product</th>';
                            echo '<th scope="col">Truck</th>';
                            echo '<th scope="col">Origin Address</th>';
                            echo '<th scope="col">Destination Address</th>';
                            echo '<th scope="col">Distances</th>';
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

                                                // Fetch origin and destination addresses
                                                $distance_sql = "SELECT * FROM `country_map` WHERE id_location_from = " . $row['id_location_from'] . " AND id_location_to = " . $row['id_location_to'];
                                                $distance_res = mysqli_query($con, $distance_sql);
                                                if (mysqli_num_rows($distance_res) > 0) {
                                                    while ($distance_row = mysqli_fetch_array($distance_res)) {
                                                        echo '<td>' . $distance_row['distance_m']/1000 . ' km</td>';
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
                            echo '</div>';
                            echo '</div>';
                        } 
                        
                    }
                    
                    ?>
                <div style="text-align: center;">
                    <form id="scheduleForm" action="generate-schedule.php" method="POST">
                        <input type="hidden" name="item_ids_output" value="<?php echo htmlspecialchars($item_ids_output); ?>">
                        <div class="schedule-row">
                        <div class="schedule-col">
                            <label for="selectedSchedule">Select Schedule to Save:</label>
                            <select class="form-control" id="selectedSchedule" name="selected_schedule">
                            <option value="1">First Best</option>
                            <option value="2">Second Best</option>
                            <option value="3">Third Best</option>
                            </select>
                        </div>
                        </div>
                        <div class="button-container">
                        <button type="button" class="btn btn-outline-info" onclick="window.location.href=window.location.href">Regenerate Schedule</button>
                        <button type="submit" class="btn btn-outline-info">Save Schedule</button>
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
            // $('#table-generate').DataTable({
            //     "pageLength": 10,
            //     "autoWidth": true,
            //     "dom": '<"generate1"lfB><"generateBody"t><"generate2"ipr>'
            // });
            $('#table-1best').DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"generate1"lfB><"generateBody"t><"generate2"ipr>'
            });
            $('#table-2best').DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"generate1"lfB><"generateBody"t><"generate2"ipr>'
            });
            $('#table-3best').DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"generate1"lfB><"generateBody"t><"generate2"ipr>'
            });

            // Handle tab click event
          $('.nav-link').click(function(e) {
            e.preventDefault(); // Prevent default anchor behavior
            $('.nav-link').removeClass('active'); // Remove 'active' class from all tab links
            $(this).addClass('active'); // Add 'active' class to the clicked tab link
            
            var targetTab = $(this).attr('href'); // Get the target tab ID from the 'href' attribute
            $('.tab-pane').removeClass('show active'); // Hide all tab content
            $(targetTab).addClass('show active'); // Show the content of the target tab
          });

          // Check if the URL contains a tab parameter
          var urlParams = new URLSearchParams(window.location.search);
          var tabParam = urlParams.get('tab');

          // If the tab parameter is not explicitly set, add 'active' class to the "On Going" tab and tab pane
         // If the tab parameter is set, open the corresponding tab
          
                $('a[href=\"#1best\"]').addClass('active');
                $('#1best').addClass('show active');

          
        });
    </script>

</body>
</html>