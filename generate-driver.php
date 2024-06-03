<?php
session_start();
require "connect.php";
$user_id = $_SESSION['user_id'];

if (isset($_POST['item_ids_output'])) {
    $item_ids_output = $_POST['item_ids_output'];
    $item_ids_by_trucks = json_decode($item_ids_output, true);

    if (is_array($item_ids_by_trucks) && count($item_ids_by_trucks) > 0) {
        $truck_ids = $item_ids_by_trucks[0];

        $query = "SELECT DISTINCT * FROM `truck_driver` WHERE `id_truck` = ? AND id_driver1 IS NULL;";
        $stmt = mysqli_prepare($con, $query);

        foreach ($truck_ids as $index => $truck_id) {
            if (isset($item_ids_by_trucks[$index + 1]) && is_array($item_ids_by_trucks[$index + 1])) {
                $driver_ids = $item_ids_by_trucks[$index + 1];

                // Fetch item details from the database using a prepared statement
                mysqli_stmt_bind_param($stmt, "i", $truck_id);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);

                if ($res === false) {
                    echo "Error: " . mysqli_error($con);
                    exit;
                }

                if (mysqli_num_rows($res) > 0) {
                    while ($row = mysqli_fetch_assoc($res)) {
                        echo '<td>' . $row['id'] . '</td>';
                        $count = 1;
                        foreach ($driver_ids as $driver_id) {
                            $update_query = "UPDATE `truck_driver` SET id_driver".$count." = ? WHERE id = ?;";
                            $update_stmt = mysqli_prepare($con, $update_query);
                            mysqli_stmt_bind_param($update_stmt, "ii", $driver_id, $row['id']);
                            if (!mysqli_stmt_execute($update_stmt)) {
                                echo "Error updating driver $count: " . mysqli_error($con);
                                exit;
                            }
                            $count++;
                        }
                        mysqli_query($con, "INSERT INTO `log` (`id_admin`, `id_table`, `action`, `detail_action`, `timestamp`) VALUES ($user_id, 4, 4, 'ID Schedule: ".$row['id']."', current_timestamp()); ");

                    }
                } else {
                    echo "No matching truck_driver record found for truck ID: $truck_id";
                    exit;
                }
            } else {
                echo "Driver IDs not found for truck ID: $truck_id";
                exit;
            }
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Invalid item_ids_output data";
        exit;
    }

    // Redirect to schedule.php after processing
    header("Location: schedule.php");
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
            $item_ids_output = exec("python ./DriverAssign.py");
            // echo $item_ids_output;

            $item_ids_by_trucks = json_decode($item_ids_output, true);

            if (is_array($item_ids_by_trucks) && count($item_ids_by_trucks) > 0) {
                $truck_ids = $item_ids_by_trucks[0];

                echo '<table class="table table-hover fixed-size-table table-generate" id="table-generate">';
                echo '<thead>';
                echo '<tr>';
                echo '<th scope="col">#</th>';
                echo '<th scope="col">Schedule ID</th>';
                echo '<th scope="col">Truck</th>';
                echo '<th scope="col">Driver 1</th>';
                echo '<th scope="col">Driver 2</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody class="table-group-divider">';
                $row_count = 1;
                
                foreach ($truck_ids as $index => $truck_id) {
                    if (isset($item_ids_by_trucks[$index + 1]) && is_array($item_ids_by_trucks[$index + 1])) {
                        $driver_ids = $item_ids_by_trucks[$index + 1];
                        
                        // Fetch item details from the database
                        $sql = "SELECT DISTINCT * FROM `truck_driver` WHERE `id_truck` = ".$truck_id." AND id_driver1 IS NULL;";
                        $res = mysqli_query($con, $sql);
                        
                        if (mysqli_num_rows($res) > 0) {
                            while ($row = mysqli_fetch_array($res)) {
                                echo '<tr>';
                                echo '<td>' . $row_count . '</td>';
                                $row_count++;

                                // Assuming $schedule_id_counter is defined somewhere
                                echo '<td>' . $row['id'] . '</td>';
                                
                                // Display truck details
                                $sql_truck = "SELECT * FROM `truck` WHERE id = ".$truck_id.";";
                                $res_truck = mysqli_query($con, $sql_truck);
                                if (mysqli_num_rows($res_truck) > 0) {
                                    while ($row_truck = mysqli_fetch_array($res_truck)) {
                                        echo '<td>Truck ID: ' . $row_truck['id'] . '<br>Unique number: ' . $row_truck['unique_number'] . '</td>';
                                    }
                                }
                                
                                // Display driver details
                                foreach ($driver_ids as $driver_id) {
                                    $sql_driver = "SELECT * FROM `driver` WHERE id = " . $driver_id;
                                    $res_driver = mysqli_query($con, $sql_driver);
                                    if (mysqli_num_rows($res_driver) > 0) {
                                        while ($row_driver = mysqli_fetch_array($res_driver)) {
                                            echo '<td>Driver ID: ' . $row_driver['id'] . '<br>
                                            Driver Name: ' . $row_driver['driver_name'] . '<br>
                                            Driver Phone Number: ' . $row_driver['phone_number'] . '<br>
                                            Experience: ' . $row_driver['experience'] . ' year(s)</td>';
                                        }
                                    }
                                }
                                
                                echo '</tr>';
                            }
                        } 
                    }
                }

                echo '</tbody>';
                echo '</table>';
            } else {
                echo "No data available.";
            }
            ?>
            <div style="text-align: center;">
                <form id="scheduleForm" action="generate-driver.php" method="POST">
                    <input type="hidden" name="item_ids_output" value="<?php echo htmlspecialchars($item_ids_output); ?>">

                    <div style="text-align: center;">
                <!-- Modify the button to submit the form when clicked -->
                        <button type="button" class="btn btn-outline-info" onclick="window.location.href=window.location.href">Regenerate Driver</button>
                        <button type="submit" form="scheduleForm" class="btn btn-outline-info">Save Driver</button>
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