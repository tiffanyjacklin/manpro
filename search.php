<?php
session_start();
require "connect.php";

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $status_item = '';
    $cek_item = "SELECT * FROM `item` WHERE `id` = ".$id.";";

    $res_cek_item = mysqli_query($con, $cek_item);
    if (mysqli_num_rows($res_cek_item) > 0) {
        $cek_schedule = "SELECT * FROM `schedule` WHERE `id_barang` = ".$id.";";

        $res_cek_schedule = mysqli_query($con, $cek_schedule);
        if (mysqli_num_rows($res_cek_schedule) > 0) {
            $row_cek_schedule = mysqli_fetch_assoc($res_cek_schedule);

            $cek_driver = "SELECT * FROM `truck_driver` WHERE `id` = ".$row_cek_schedule['id_schedule'].";";
            $res_cek_driver = mysqli_query($con, $cek_driver);
            if (mysqli_num_rows($res_cek_driver) > 0) {
                $row_cek_driver = mysqli_fetch_assoc($res_cek_driver);
                if ((!isset($row_cek_driver['id_driver1'])) OR (!isset($row_cek_driver['id_driver2']))){
                    $query = "SELECT `item`.*, `truck`.*, 
                                CONCAT(
                                        COALESCE(l1.`alamat`, ''), ',\n', 
                                        COALESCE(l1.`kelurahan_desa`, ''), ',\n', 
                                        COALESCE(l1.`kecamatan`, ''), ',\n', 
                                        COALESCE(l1.`kota_kabupaten`, ''), ',\n', 
                                        'Jawa Timur ', COALESCE(l1.`kode_pos`, '')
                                ) AS location_from, 
                                CONCAT(
                                        COALESCE(l2.`alamat`, ''), ',\n', 
                                        COALESCE(l2.`kelurahan_desa`, ''), ',\n', 
                                        COALESCE(l2.`kecamatan`, ''), ',\n', 
                                        COALESCE(l2.`kota_kabupaten`, ''), ',\n', 
                                        'Jawa Timur ', COALESCE(l2.`kode_pos`, '')
                                ) AS location_to FROM `item` JOIN `schedule` ON `schedule`.`id_barang` = `item`.`id`
                                                    JOIN `truck_driver` ON `truck_driver`.`id` = `schedule`.`id_schedule`
                                                    JOIN `truck` ON `truck`.`id` = `truck_driver`.`id_truck`
                                                    JOIN `location` l1 ON l1.`id` = `item`.`id_location_from`
                                                    JOIN `location` l2 ON l2.`id` = `item`.`id_location_to`
                                                    WHERE `item`.`id`=".$id.";";

                    $res = mysqli_query($con, $query);
                    if (mysqli_num_rows($res) > 0) {
                        $row = mysqli_fetch_assoc($res);
                        $status_item = "No Driver Assigned";
                    }
                }else{
                    $query = "SELECT `item`.*, `truck`.*, d1.`driver_name` AS `driver1_name`, d1.`phone_number` AS `driver1_phone`, d2.`driver_name` AS `driver2_name`, d2.`phone_number` AS `driver2_phone`, 
                                CONCAT(
                                        COALESCE(l1.`alamat`, ''), ',\n', 
                                        COALESCE(l1.`kelurahan_desa`, ''), ',\n', 
                                        COALESCE(l1.`kecamatan`, ''), ',\n', 
                                        COALESCE(l1.`kota_kabupaten`, ''), ',\n', 
                                        'Jawa Timur ', COALESCE(l1.`kode_pos`, '')
                                ) AS location_from, 
                                CONCAT(
                                        COALESCE(l2.`alamat`, ''), ',\n', 
                                        COALESCE(l2.`kelurahan_desa`, ''), ',\n', 
                                        COALESCE(l2.`kecamatan`, ''), ',\n', 
                                        COALESCE(l2.`kota_kabupaten`, ''), ',\n', 
                                        'Jawa Timur ', COALESCE(l2.`kode_pos`, '')
                                ) AS location_to FROM `item` JOIN `schedule` ON `schedule`.`id_barang` = `item`.`id`
                                                    JOIN `truck_driver` ON `truck_driver`.`id` = `schedule`.`id_schedule`
                                                    JOIN `truck` ON `truck`.`id` = `truck_driver`.`id_truck`
                                                    JOIN `driver` d1 ON d1.`id` = `truck_driver`.`id_driver1`
                                                    JOIN `driver` d2 ON d2.`id` = `truck_driver`.`id_driver2`
                                                    JOIN `location` l1 ON l1.`id` = `item`.`id_location_from`
                                                    JOIN `location` l2 ON l2.`id` = `item`.`id_location_to`
                                                    WHERE `item`.`id`=".$id.";";

                    $res = mysqli_query($con, $query);
                    if (mysqli_num_rows($res) > 0) {
                        $row = mysqli_fetch_assoc($res);
                        if ($row['status'] == 2){
                            $status_item = "Delivered";
                        }
                        else if ($row['status'] == 1){
                            $status_item = "Delivering";
                        }

                    }
                }
            }
        }
        else{
            $query = "SELECT `item`.*,
                    CONCAT(
                            COALESCE(l1.`alamat`, ''), ',\n', 
                            COALESCE(l1.`kelurahan_desa`, ''), ',\n', 
                            COALESCE(l1.`kecamatan`, ''), ',\n', 
                            COALESCE(l1.`kota_kabupaten`, ''), ',\n', 
                            'Jawa Timur ', COALESCE(l1.`kode_pos`, '')
                    ) AS location_from, 
                    CONCAT(
                            COALESCE(l2.`alamat`, ''), ',\n', 
                            COALESCE(l2.`kelurahan_desa`, ''), ',\n', 
                            COALESCE(l2.`kecamatan`, ''), ',\n', 
                            COALESCE(l2.`kota_kabupaten`, ''), ',\n', 
                            'Jawa Timur ', COALESCE(l2.`kode_pos`, '')
                    ) AS location_to FROM `item` 
                                        JOIN `location` l1 ON l1.`id` = `item`.`id_location_from`
                                        JOIN `location` l2 ON l2.`id` = `item`.`id_location_to`
                                        WHERE `item`.`id`=".$id.";";

            $res = mysqli_query($con, $query);
            if (mysqli_num_rows($res) > 0) {
                $row = mysqli_fetch_assoc($res);
                $status_item = "Unscheduled";
            }
        }

    }
    else{
        $status_item = "Item Not Found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Search</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
    
      <div class="container">
        <div class="d-flex align-items-center" style="margin-top: 10px;">
            <button type="button" class="btn btn-outline-info me-2" onclick="window.location.href='dashboard.php'">Back</button>
            <div class="title-form" style="color: #052c65;"><i class="fa-solid fa-box-open me-2"></i><?php echo $id; ?></div>
        </div>

        <hr>

        <?php 
            if ($status_item == "Item Not Found"){
                echo '<div class="not-found">Item Not Found.</div>';
            }else{
                echo '<div class="col-md-12 d-flex justify-content-between">
                        <div class="col-md-4">
                            <div class="container">
                                <div class="status-search">
                                    <div class="row">
                                        <div class="statusnya">From<i class="fa-solid fa-box-open"></i></div>
                                        <div class="title-dashboard3">'.$row["location_from"].'</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="container">
                                <div class="status-search">
                                    <div class="row">
                                        <div class="statusnya">To<i class="fa-solid fa-truck-fast"></i></div>
                                        <div class="title-dashboard3">'.$row["location_to"].'</div>
                                    </div>
                                </div>
                            </div>
                        </div>';

                    if ($row['order_completed']){
                        echo '<div class="col-md-4">
                            <div class="container">
                                <div class="status-search">
                                    <div class="row">
                                        <div class="statusnya">Delivered On <i class="fa-solid fa-calendar-check"></i></div>
                                        <div class="title-dashboard3">'.$row["order_completed"].'</div>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }else{
                        echo '<div class="col-md-4">
                        <div class="container">
                            <div class="status-search">
                                <div class="row">
                                </div>
                            </div>
                        </div>
                    </div>';
                    }
                    
                    echo '</div>

                <hr>
                <div class="col-md-12 d-flex justify-content-between align-items-start">
                    <div class="col-md-4">
                        <div class="container">
                            <div class="title-item">'.$status_item.'</div>
                            <ul class="list-group list-group-flush">';
                            if ($status_item == 'Delivered'){
                                echo '<li class="list-group-item">
                                Order Completed
                                <div class="title-dashboard4">'.$row['order_completed'].'</div> 
                            </li>';
                            }
                            if ($status_item == 'Delivered' OR $status_item == 'Delivering'){
                                echo '<li class="list-group-item">
                                Schedule has been created. Item is being delivered.
                            </li>';
                            }
                                echo '
                                <li class="list-group-item">
                                    Order Received
                                    <div class="title-dashboard4">'.$row['order_received'].'</div> 
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="container">
                            <div class="title-dashboard4">Item Description</div>
                            <div class="search-font">'.$row['item_name'].'</div>
                            <div class="title-dashboard4">Weight</div>
                            <div class="search-font">'.$row['weight_kg'].' kg</div>
                            <div class="title-dashboard4">Item Dimension</div>
                            <div class="search-font"> ' . $row['panjang'] . 'cm x ' . $row['lebar'] . 'cm x ' . $row['tinggi'] . 'cm </div>
                            <div class="title-dashboard4">Sender`s Name</div>
                            <div class="search-font">'.$row['sender_name'].'</div>
                            <div class="title-dashboard4">Sender`s Phone Number</div>
                            <div class="search-font">'.$row['sender_phone_num'].'</div> 
                            <div class="title-dashboard4">Receiver`s Name</div>
                            <div class="search-font">'.$row['receiver_name'].'</div>
                            <div class="title-dashboard4">Receiver`s Phone Number</div>
                            <div class="search-font">'.$row['receiver_phone_num'].'</div>
                        </div>
                    </div>';
                if ($status_item == "Delivered" OR $status_item == "Delivering"){
                    echo '<div class="col-md-4">
                    <div class="container">
                        <div class="title-dashboard4">Truck</div>
                        <div class="search-font">'.$row['unique_number'].'</div>
                        <div class="title-dashboard4">Driver 1`s Name</div>
                        <div class="search-font">'.$row['driver1_name'].'</div>
                        <div class="title-dashboard4">Driver 1`s Phone Number</div>
                        <div class="search-font">'.$row['driver1_phone'].'</div>
                        <div class="title-dashboard4">Driver 2`s Name</div>
                        <div class="search-font">'.$row['driver2_name'].'</div>
                        <div class="title-dashboard4">Driver 2`s Phone Number</div>
                        <div class="search-font">'.$row['driver2_phone'].'</div>                        
                    </div>
                </div>';
                } else if ($status_item == "No Driver Assigned"){
                    echo '<div class="col-md-4">
                    <div class="container">
                        <div class="title-dashboard4">Truck</div>
                        <div class="search-font">'.$row['unique_number'].'</div>            
                    </div>
                </div>';
                }else{
                    echo '<div class="col-md-4">
                    <div class="container">
                        
                    </div>
                </div>';
                }
                echo '</div>';
            }
        ?>
      </div>

    <?php
      include('footer.php');
    ?>
    </div>


</body>
</html>