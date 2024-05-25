<?php
session_start();
require "connect.php";

if (isset($_POST['id_barang'])) {
  
  $id_barang = $_POST['id_barang'];

  $sql_complete = "UPDATE `schedule` SET `status` = 2, `date_time` = current_timestamp() WHERE `id_barang` = '".$id_barang."';";
  mysqli_query($con, $sql_complete);
  
  $sql_item_complete = "UPDATE `item` SET `status` = 2, `order_completed` = current_timestamp() WHERE `id` = ".$id_barang.";";
  mysqli_query($con, $sql_item_complete);

  $sql_distance =  "SELECT t.`id` AS `id`, t.`fuel_capacity`, t.`fuel_now`, t.`km_per_liter`, f.`cost_per_liter`,  s.`id_location_from`, s.`id_location_dest`, c.`distance_m`, td.`id_driver1` AS `id_driver1`, td.`id_driver2` AS `id_driver2`
                    FROM `truck` t 
                    JOIN `fuel` f ON t.`id_fuel` = f.`id`
                    JOIN `truck_driver` td ON t.`id` = td.`id_truck`
                    JOIN `schedule` s ON td.`id` = s.`id_schedule`
                    JOIN `country_map` c ON (s.`id_location_from` = c.`id_location_from`) AND (s.`id_location_dest` = c.`id_location_to`)
                    WHERE s.`id_barang` = ".$id_barang."; ";
  $res_distance = mysqli_query($con, $sql_distance);
  if (mysqli_num_rows($res_distance) > 0) {
    while ($row_product = mysqli_fetch_array($res_distance)) {
      $id_truck = $row_product['id'];

      if (($row_product['fuel_now']-(($row_product['distance_m']/1000)/$row_product['km_per_liter'])) < 0){
        $fuel_now = ($row_product['fuel_now']-(($row_product['distance_m']/1000)/$row_product['km_per_liter'])) + $row_product['fuel_capacity'];
        $fuel_cost = ($row_product['fuel_capacity'] * $row_product['cost_per_liter']);

        $sql_fuel_transaction = "INSERT INTO `transaction` (`status`, `date_time`, `nominal`, `id_truck`) VALUES (2,current_timestamp(),".$fuel_cost.",".$row_product['id'].");";
        mysqli_query($con,$sql_fuel_transaction);

        $sql_truck_fuel = "UPDATE `truck` SET `id_location` = ".$row_product['id_location_from'].", `fuel_now` = ".$fuel_now.", `total_distance` = ".($row_product['distance_m']/1000)." WHERE `id` = ".$row_product['id'].";";   
      }else{
        $sql_truck_fuel = "UPDATE `truck` SET `id_location` = ".$row_product['id_location_from'].", `fuel_now` = ".($row_product['fuel_now']-(($row_product['distance_m']/1000)/$row_product['km_per_liter'])).", `total_distance` = ".($row_product['distance_m']/1000)." WHERE `id` = ".$row_product['id'].";";   
      }
      mysqli_query($con,$sql_truck_fuel);
      $sql_driver1_dist = "UPDATE `driver` SET `total_distance` = (`total_distance`+".($row_product['distance_m']/1000).") WHERE `id` = ".$row_product['id_driver1'].";";
      mysqli_query($con,$sql_driver1_dist);
      $sql_driver2_dist = "UPDATE `driver` SET `total_distance` = (`total_distance`+".($row_product['distance_m']/1000).") WHERE `id` = ".$row_product['id_driver2'].";";
      mysqli_query($con,$sql_driver2_dist);

      // Execute the SELECT query to check for NULL
      $select_query = "SELECT `id_location_from`
                        FROM `schedule` s JOIN `truck_driver` td ON s.`id_schedule` = td.`id`
                        WHERE td.`id_truck` = ".$id_truck." AND `status` = 1
                        ORDER BY `id_schedule`
                        LIMIT 1";
      $result = mysqli_query($con, $select_query);

      // Check if the SELECT query returned any rows
      if ($result && mysqli_num_rows($result) > 0) {
        // The SELECT query returned a non-NULL value
        // Proceed with the UPDATE query
        $sql_update_truck_location = "UPDATE `truck`
                      SET `id_location` = (
                          SELECT `id_location_from`
                          FROM `schedule` s JOIN `truck_driver` td ON s.`id_schedule` = td.`id`
                          WHERE td.`id_truck` = ".$id_truck." AND `status` = 1
                          ORDER BY `id_schedule`
                          LIMIT 1
                      )
                      WHERE id = $id_truck";
        mysqli_query($con, $sql_update_truck_location);
      }


      $sql_cek_masih_kirim = "SELECT * FROM `schedule` 
                              WHERE `id_schedule` = (SELECT DISTINCT `id_schedule` FROM `schedule`
                                                    WHERE `id_barang` = ".$id_barang." AND `schedule_status` = 1) 
                              AND `schedule_status` = 1 AND `status` = 1;";
      $res_cek_masih_kirim = mysqli_query($con, $sql_cek_masih_kirim);
      if (mysqli_num_rows($res_cek_masih_kirim) == 0) {
        $sql_truck_status_done = "UPDATE `truck` SET `truck_status` = 1 WHERE `id` = ".$id_truck.";";
        mysqli_query($con,$sql_truck_status_done);

        $sql_cek_total_distance = "SELECT `total_distance`  FROM `truck` 
                                  JOIN `truck_driver` ON `truck`.`id` = `truck_driver`.`id_truck`
                                  JOIN `schedule` ON `schedule`.`id_schedule` = `truck_driver`.`id`
                                  WHERE `schedule`.`id_barang`=".$id_barang.";";
        $res_cek_total_distance = mysqli_query($con, $sql_cek_total_distance);
        if (mysqli_num_rows($res_cek_total_distance) > 0) {
          $row = mysqli_fetch_assoc($res_cek_total_distance);
          $total_distance = $row['total_distance'];
          if ($total_distance >= 10000){
            $sql_input_maintenance_cost = "INSERT INTO `transaction` (`status`, `date_time`, `nominal`, `id_truck`) VALUES (2,current_timestamp(),1000000,".$id_truck.");"; 
            mysqli_query($con,$sql_input_maintenance_cost);
            $sql_truck_distance_new = "UPDATE `truck` SET `total_distance` = 0, `truck_status` = 3, WHERE `id` = ".$id_truck.";";
            mysqli_query($con,$sql_truck_distance_new);
          }

        }
      } 
    }
  }

  $sql_shipping_cost = "SELECT `shipping_cost` FROM `item` WHERE `id` = ".$id_barang.";";
  $res_shipping_cost = mysqli_query($con, $sql_shipping_cost);

  if (mysqli_num_rows($res_shipping_cost) > 0) {
    while ($row_shipping_cost = mysqli_fetch_array($res_shipping_cost)) {
    $sql_input_shipping_cost = "INSERT INTO `transaction` (`status`, `date_time`, `nominal`, `id_item`) VALUES (1,current_timestamp(),".$row_shipping_cost['shipping_cost'].",".$id_barang.");"; 
    mysqli_query($con,$sql_input_shipping_cost);
    }
  }

  
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
      <ul class="nav nav-tabs">
      
          <?php
              $nodriver_sql = "SELECT COUNT(*) FROM `truck_driver` WHERE `id_driver1` IS NULL OR `id_driver2` IS NULL;";
              $nodriver_res = mysqli_query($con, $nodriver_sql);
              if (mysqli_num_rows($nodriver_res) > 0) {
                // Loop through the fetched rows and display them
                while ($nodriver_row = mysqli_fetch_array($nodriver_res)) {
                  if ($nodriver_row['COUNT(*)'] > 0){
                    echo '<li class="nav-item">
                    <a class="nav-link" href="#assigndriver">Assign Driver
                    <span class="badge text-bg-custom">'.$nodriver_row['COUNT(*)'].'</span></a>
                    </li>';
                    
                  }
                }
              }
              ?>
          
        <li class="nav-item">
          <a class="nav-link" href="#on-going">On Going</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#completed">Completed</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#unscheduled">Unscheduled
              <?php
              $unschedule_sql = "SELECT COUNT(*) FROM `item` WHERE `status` = 0;";
              $unschedule_res = mysqli_query($con, $unschedule_sql);
              if (mysqli_num_rows($unschedule_res) > 0) {
                // Loop through the fetched rows and display them
                while ($unschedule_row = mysqli_fetch_array($unschedule_res)) {
                  if ($unschedule_row['COUNT(*)'] > 0){
                    echo '<span class="badge text-bg-custom">'.$unschedule_row['COUNT(*)'].'</span>';
                    
                  }
                }
              }
              ?>
            </a>
        </li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade" id="assigndriver">
          <button type="button" class="btn btn-outline-info" onclick="window.location.href='generate-driver.php'">Generate Driver</button>

          <?php
            $sql = "SELECT `schedule`.* FROM `schedule` JOIN `truck_driver` ON `schedule`.`id_schedule` = `truck_driver`.`id`
                    WHERE `truck_driver`.`id_driver1` IS NULL OR `truck_driver`.`id_driver2` IS NULL;";
            $res = mysqli_query($con, $sql);

            echo '<table class="table table-hover fixed-size-table table-assigndriver" id="table-assigndriver">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">ID</th>';
            echo '<th scope="col">Schedule ID</th>';
            echo '<th scope="col">Product</th>';
            echo '<th scope="col">Truck</th>';
            echo '<th scope="col">Origin Address</th>';
            echo '<th scope="col">Destination Address</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody class="table-group-divider">';
            // Check if there are items to display
            if (mysqli_num_rows($res) > 0) {
              // Loop through the fetched rows and display them
              while ($row = mysqli_fetch_array($res)) {
                  echo '<tr>';
                  echo '<th>' . $row['id'] . '</th>';
                  echo '<td>' . $row['id_schedule'] . '</td>';
                  $sql_product = "SELECT * FROM `item` WHERE id = ".$row['id_barang'].";";
                  $res_product = mysqli_query($con, $sql_product);
                  if (mysqli_num_rows($res_product) > 0) {
                    while ($row_product = mysqli_fetch_array($res_product)) {
                      echo '<td>';

                      echo '
                  <div class="modal" id="productDetailsModal'.$row['id_barang'].'" tabindex="-1">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title title-form">Product Details</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="productDetailsModalBody">';
                            $modal_sql = "SELECT * FROM `schedule` 
                                          LEFT JOIN `truck_driver` ON `schedule`.`id_schedule` = `truck_driver`.`id` 
                                          LEFT JOIN `item` ON `schedule`.`id_barang` = `item`.`id`
                                          LEFT JOIN `truck` ON `truck_driver`.`id_truck` = `truck`.`id`
                                          WHERE `id_schedule` IN (SELECT `id_schedule` FROM `schedule` WHERE `id_barang` = ".$row['id_barang'].")   
                                          ORDER BY `schedule`.`schedule_status` , `schedule`.`id_barang`;";
                            // $modal_sql = "SELECT *
                            //               FROM `schedule` JOIN `truck` ON `schedule`.`id_truk` = `truck`.`id` JOIN `item` ON `schedule`.`id_barang` = `item`.`id`
                            //               WHERE `id_schedule` IN (SELECT `id_schedule` FROM `schedule` WHERE `id_barang` = ".$row['id_barang'].")   
                            //               ORDER BY `schedule`.`schedule_status` , `schedule`.`id_barang`;";
                            
                            $modal_res = mysqli_query($con, $modal_sql);
                            $schedule_status_lama = 0;
                            $count = 1;
                            if (mysqli_num_rows($modal_res) > 0) {
                              while ($modal_row = mysqli_fetch_array($modal_res)) {
                                $plate = $modal_row['unique_number'];
                                if ($modal_row['schedule_status'] != $schedule_status_lama){
                                  if ($count != 1){
                                    // echo "<strong>Truck's Unique Number:".$plate."</strong><br>";

                                    echo '</tbody>
                                        </table>';
                                  }
                                  echo "<strong>Output GA Terbaik Ke-".$count.":</strong><br>";                                
                                  $count++;
                                  $schedule_status_lama = $modal_row['schedule_status'];
                                  echo '<table class="table table-hover fixed-size-table">
                                  <thead>
                                    <tr>
                                      <th scope="col">ID</th>
                                      <th scope="col">Product Name</th>
                                    </tr>
                                  </thead>
                                  <tbody>';
                                }
                                if ($modal_row['id_barang'] == $row['id_barang']){
                                  echo '<tr>';
                                  // Display the ID in the first column
                                  echo '<td><strong>' . $modal_row['id_barang'] . '</strong></td>';
                                  // Display the item name in the second column
                                  echo '<td><strong>' . $modal_row['item_name'] . '</strong></td>';
                                  // Close the row
                                  echo '</tr>';
                                }else{
                                  echo '<tr>';
                                  // Display the ID in the first column
                                  echo '<td>' . $modal_row['id_barang'] . '</td>';
                                  // Display the item name in the second column
                                  echo '<td>' . $modal_row['item_name'] . '</td>';
                                  // Close the row
                                  echo '</tr>';                                
                                }
                                
                              }
                            }

                  echo '</tbody>
                      </table>                   
                    </div>
                  </div>
                </div>
              </div>';

                      echo '
                              Product ID: '.$row_product['id'].' <br>
                              Product: '.$row_product['item_name'].' <br>
                              Dimension: ' . $row_product['panjang'] . 'cm x ' . $row_product['lebar'] . 'cm x ' . $row_product['tinggi'] . 'cm <br>
                              Order received: '.$row_product['order_received'].'<br>
                              <button type="button" class="btn btn-outline-info btn-sm product-details-btn" data-product-id="' . $row_product['id'] . '" style="margin-top:10px;">Show More</button>
                            </td>
                            ';     
                          
                      
                    }
                  }
                  
                  $sql_truck = "SELECT `truck`.`unique_number` AS `unique_number` 
                                FROM `schedule` 
                                JOIN `truck_driver` ON `schedule`.`id_schedule` = `truck_driver`.`id` 
                                JOIN `truck` ON `truck_driver`.`id_truck` = `truck`.`id`
                                WHERE `schedule`.`id_barang` = ".$row['id_barang']." ;";
                  // $sql_truck = "SELECT * FROM `truck` WHERE id = ".$row['id_truk'].";";
                  $res_truck = mysqli_query($con, $sql_truck);
                  if (mysqli_num_rows($res_truck) > 0) {
                    while ($row_truck = mysqli_fetch_array($res_truck)) {
                      echo '<td>
                              Unique number: ' . $row_truck['unique_number'].'<br>';
                      echo '</td>';                      
                    }
                  }
                  

                  // Fetch origin and destination addresses
                  $address_sql = "SELECT * FROM `location` WHERE id = " . $row['id_location_from'] . " OR id = " . $row['id_location_dest'];
                  $address_res = mysqli_query($con, $address_sql);
                  if (mysqli_num_rows($address_res) > 0) {
                      while ($address_row = mysqli_fetch_array($address_res)) {
                          echo '<td>' . $address_row['alamat'] . ',<br>' . $address_row['kelurahan_desa'] . ',<br>' . $address_row['kecamatan'] . ',<br>' . $address_row['kota_kabupaten'] . ',<br> Jawa Timur ' . $address_row['kode_pos'] . '</td>';
                      }
                  }
                  echo '</tr>';
              }
            } 

            echo '</tbody>';
            echo '</table>';
          ?>    
        </div>
        <div class="tab-pane fade" id="on-going">
        <?php
            $sql = "SELECT `schedule`.* FROM `schedule` JOIN `truck_driver` ON `schedule`.`id_schedule` = `truck_driver`.`id`
                    WHERE `schedule`.`date_time` IS NULL AND `schedule`.`schedule_status` = 1 AND (`truck_driver`.`id_driver1` IS NOT NULL OR `truck_driver`.`id_driver2` IS NOT NULL);";
            $res = mysqli_query($con, $sql);

            echo '<table class="table table-hover fixed-size-table table-on-going" id="table-on-going">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">Action</th>';
            echo '<th scope="col">ID</th>';
            echo '<th scope="col">Schedule ID</th>';
            echo '<th scope="col">Product</th>';
            echo '<th scope="col">Truck</th>';
            echo '<th scope="col">Origin Address</th>';
            echo '<th scope="col">Destination Address</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody class="table-group-divider">';
            // Check if there are items to display
            if (mysqli_num_rows($res) > 0) {
              // Loop through the fetched rows and display them
              while ($row = mysqli_fetch_array($res)) {
                  echo '<tr>';
                  echo '<td>
                          <form id="completeScheduleForm" action="schedule.php" method="POST" style="display: inline;">
                            <input type="hidden" name="id_barang" value="'.$row['id_barang'].'">
                            <button type="submit" class="btn btn-outline-info btn-sm">Complete</button>';
                  echo '
                  <div class="modal" id="productDetailsModal'.$row['id_barang'].'" tabindex="-1">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title title-form">Product Details</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="productDetailsModalBody">';
                            $modal_sql = "SELECT * FROM `schedule` 
                                          LEFT JOIN `truck_driver` ON `schedule`.`id_schedule` = `truck_driver`.`id` 
                                          LEFT JOIN `item` ON `schedule`.`id_barang` = `item`.`id`
                                          LEFT JOIN `truck` ON `truck_driver`.`id_truck` = `truck`.`id`
                                          WHERE `id_schedule` IN (SELECT `id_schedule` FROM `schedule` WHERE `id_barang` = ".$row['id_barang'].")   
                                          ORDER BY `schedule`.`schedule_status` , `schedule`.`id_barang`;";
                            // $modal_sql = "SELECT *
                            //               FROM `schedule` JOIN `truck` ON `schedule`.`id_truk` = `truck`.`id` JOIN `item` ON `schedule`.`id_barang` = `item`.`id`
                            //               WHERE `id_schedule` IN (SELECT `id_schedule` FROM `schedule` WHERE `id_barang` = ".$row['id_barang'].")   
                            //               ORDER BY `schedule`.`schedule_status` , `schedule`.`id_barang`;";
                            
                            $modal_res = mysqli_query($con, $modal_sql);
                            $schedule_status_lama = 0;
                            $count = 1;
                            if (mysqli_num_rows($modal_res) > 0) {
                              while ($modal_row = mysqli_fetch_array($modal_res)) {
                                $plate = $modal_row['unique_number'];
                                if ($modal_row['schedule_status'] != $schedule_status_lama){
                                  if ($count != 1){
                                    // echo "<strong>Truck's Unique Number:".$plate."</strong><br>";

                                    echo '</tbody>
                                        </table>';
                                  }
                                  echo "<strong>Output GA Terbaik Ke-".$count.":</strong><br>";                                
                                  $count++;
                                  $schedule_status_lama = $modal_row['schedule_status'];
                                  echo '<table class="table table-hover fixed-size-table">
                                  <thead>
                                    <tr>
                                      <th scope="col">ID</th>
                                      <th scope="col">Product Name</th>
                                    </tr>
                                  </thead>
                                  <tbody>';
                                }
                                if ($modal_row['id_barang'] == $row['id_barang']){
                                  echo '<tr>';
                                  // Display the ID in the first column
                                  echo '<td><strong>' . $modal_row['id_barang'] . '</strong></td>';
                                  // Display the item name in the second column
                                  echo '<td><strong>' . $modal_row['item_name'] . '</strong></td>';
                                  // Close the row
                                  echo '</tr>';
                                }else{
                                  echo '<tr>';
                                  // Display the ID in the first column
                                  echo '<td>' . $modal_row['id_barang'] . '</td>';
                                  // Display the item name in the second column
                                  echo '<td>' . $modal_row['item_name'] . '</td>';
                                  // Close the row
                                  echo '</tr>';                                
                                }
                                
                              }
                            }

                  echo '</tbody>
                      </table>                   
                    </div>
                  </div>
                </div>
              </div>';
                  echo'
                          </form>
                        </td>';
                  echo '<th>' . $row['id'] . '</th>';
                  echo '<td>' . $row['id_schedule'] . '</td>';
                  $sql_product = "SELECT * FROM `item` WHERE id = ".$row['id_barang'].";";
                  $res_product = mysqli_query($con, $sql_product);
                  if (mysqli_num_rows($res_product) > 0) {
                    while ($row_product = mysqli_fetch_array($res_product)) {
                      echo '<td>
                              Product ID: '.$row_product['id'].' <br>
                              Product: '.$row_product['item_name'].' <br>
                              Dimension: ' . $row_product['panjang'] . 'cm x ' . $row_product['lebar'] . 'cm x ' . $row_product['tinggi'] . 'cm <br>
                              Order received: '.$row_product['order_received'].'<br>
                              <button type="button" class="btn btn-outline-info btn-sm product-details-btn" data-product-id="' . $row_product['id'] . '" style="margin-top:10px;">Show More</button>
                            </td>
                            ';                      
                    }
                  }

                  $sql_truck = "SELECT `d1`.`driver_name` AS `driver1`, `d2`.`driver_name` as `driver2`, `truck`.`unique_number` AS `unique_number` 
                                FROM `schedule` 
                                JOIN `truck_driver` ON `schedule`.`id_schedule` = `truck_driver`.`id` 
                                JOIN `truck` ON `truck_driver`.`id_truck` = `truck`.`id`
                                LEFT JOIN `driver` `d1` ON `d1`.`id` = `truck_driver`.`id_driver1`
                                LEFT JOIN `driver` `d2` ON `d2`.`id` = `truck_driver`.`id_driver2`
                                WHERE `schedule`.`id_barang` = ".$row['id_barang']." ;";
                  // $sql_truck = "SELECT * FROM `truck` WHERE id = ".$row['id_truk'].";";
                  $res_truck = mysqli_query($con, $sql_truck);
                  if (mysqli_num_rows($res_truck) > 0) {
                    while ($row_truck = mysqli_fetch_array($res_truck)) {
                      echo '<td>
                              Unique number: ' . $row_truck['unique_number'].'<br>';
                      echo   'Driver 1: '.$row_truck['driver1'].'<br>';
                      echo   'Driver 2: '.$row_truck['driver2'].'<br>';
                      echo '</td>';                      

                      // $sql_driver = "SELECT * FROM `truck` t JOIN `truck_driver` td ON t.`id` = td.`id_truck`
                      //               JOIN `driver` d ON td.`id_driver` = d.`id` 
                      //               WHERE t.`id` = ".$row_truck['id']." ORDER BY td.`position`;";
                      // $res_driver = mysqli_query($con, $sql_driver);
                      // if (mysqli_num_rows($res_driver) > 0) {
                      //   while ($row_driver = mysqli_fetch_array($res_driver)) {
                      //     echo 'Driver ' . $row_driver['position'] . ': ' . $row_driver['driver_name'].'<br>';
                      //   }
                      // }
                    }
                  }
                  

                  // Fetch origin and destination addresses
                  $address_sql = "SELECT * FROM `location` WHERE id = " . $row['id_location_from'] . " OR id = " . $row['id_location_dest'];
                  $address_res = mysqli_query($con, $address_sql);
                  if (mysqli_num_rows($address_res) > 0) {
                      while ($address_row = mysqli_fetch_array($address_res)) {
                          echo '<td>' . $address_row['alamat'] . ',<br>' . $address_row['kelurahan_desa'] . ',<br>' . $address_row['kecamatan'] . ',<br>' . $address_row['kota_kabupaten'] . ',<br> Jawa Timur ' . $address_row['kode_pos'] . '</td>';
                      }
                  }
                  echo '</tr>';
              }
            } 

            echo '</tbody>';
            echo '</table>';
          ?>
        
        </div>
        
        

        <div class="tab-pane fade" id="completed">
        <?php
            $sql = "SELECT * FROM `schedule` WHERE `date_time` IS NOT NULL AND `schedule_status` = 1;";
            $res = mysqli_query($con, $sql);

            echo '<table class="table table-hover fixed-size-table table-completed"" id="table-completed">';
            echo '<thead>';
            echo '<tr>';
            echo '<th scope="col">ID</th>';
            echo '<th scope="col">Schedule ID</th>';
            echo '<th scope="col">Product</th>';
            echo '<th scope="col">Truck</th>';
            echo '<th scope="col">Origin Address</th>';
            echo '<th scope="col">Destination Address</th>';
            echo '<th scope="col">Completed</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody class="table-group-divider">';
            // Check if there are items to display
            if (mysqli_num_rows($res) > 0) {
              // Loop through the fetched rows and display them
              while ($row = mysqli_fetch_array($res)) {
                  echo '<tr>';
                  echo '<th>' . $row['id'] . '</th>';
                  echo '<td>' . $row['id_schedule'] . '</td>';
                  $sql_product = "SELECT * FROM `item` WHERE id = ".$row['id_barang'].";";
                  $res_product = mysqli_query($con, $sql_product);
                  if (mysqli_num_rows($res_product) > 0) {
                    while ($row_product = mysqli_fetch_array($res_product)) {
                      echo '<td>
                              Product ID: '.$row_product['id'].' <br>
                              Product: '.$row_product['item_name'].' <br>
                              Dimension: ' . $row_product['panjang'] . 'cm x ' . $row_product['lebar'] . 'cm x ' . $row_product['tinggi'] . 'cm <br>
                              Order received: '.$row_product['order_received'].'
                            </td>';                      
                    }
                  }
                  $sql_truck = "SELECT d1.driver_name AS driver1, d2.driver_name as driver2, `truck`.`unique_number` AS unique_number FROM `schedule` 
                                JOIN `truck_driver` ON `schedule`.`id_schedule` = `truck_driver`.`id` 
                                JOIN `truck` ON `truck_driver`.`id_truck` = `truck`.`id`
                                LEFT JOIN `driver` d1 ON d1.`id` = `truck_driver`.`id_driver1`
                                LEFT JOIN `driver` d2 ON d2.`id` = `truck_driver`.`id_driver2`
                                WHERE `schedule`.`id_barang` = ".$row['id_barang'].";";
                  $res_truck = mysqli_query($con, $sql_truck);
                  if (mysqli_num_rows($res_truck) > 0) {
                    while ($row_truck = mysqli_fetch_array($res_truck)) {
                      echo '<td>
                              Unique number: ' . $row_truck['unique_number'].'<br>';
                      echo   'Driver 1: '.$row_truck['driver1'].'<br>';
                      echo   'Driver 2: '.$row_truck['driver2'].'<br>';
                      echo '</td>';                      
                    }
                  }
                  

                  // Fetch origin and destination addresses
                  $address_sql = "SELECT * FROM `location` WHERE id = " . $row['id_location_from'] . " OR id = " . $row['id_location_dest'];
                  $address_res = mysqli_query($con, $address_sql);
                  if (mysqli_num_rows($address_res) > 0) {
                      while ($address_row = mysqli_fetch_array($address_res)) {
                          echo '<td>' . $address_row['alamat'] . ',<br>' . $address_row['kelurahan_desa'] . ',<br>' . $address_row['kecamatan'] . ',<br>' . $address_row['kota_kabupaten'] . ',<br> Jawa Timur ' . $address_row['kode_pos'] . '</td>';
                      }
                  }
                  echo '<td>' . $row['date_time'] . '</td>';
                  echo '</tr>';
              }
            }

            echo '</tbody>';
            echo '</table>';
          ?>
        </div>
        <div class="tab-pane fade" id="unscheduled">
          <?php
            $sql = "SELECT * FROM `item` WHERE `status` = 0;";
            $res = mysqli_query($con, $sql);
            $rowCount = mysqli_num_rows($res);
          ?>

          <?php if ($rowCount > 0) : ?>
              <button type="button" class="btn btn-outline-info" onclick="window.location.href='generate-schedule.php'">Generate Schedule</button>
          <?php else : ?>
              <button type="button" class="btn btn-outline-info" disabled>Generate Schedule</button>
          <?php endif; ?>
          <!-- <input type="checkbox" id="select-all" onclick="toggleCheckbox(this)">
          <label for="select-all">Select/Deselect All</label> -->

          <?php
          $sql = "SELECT * FROM `item` WHERE `status` = 0;";
          $res = mysqli_query($con, $sql);

          // Display table columns
          echo '<table class="table table-hover fixed-size-table table-unscheduled"" id="table-unscheduled">';
          echo '<thead>';
          echo '<tr>';
          // echo '<th scope="col">Select</th>'; // Checkbox column
          echo '<th scope="col">ID</th>';
          echo '<th scope="col">Product</th>';
          echo '<th scope="col">Dimension</th>';
          echo '<th scope="col">Order Received</th>';
          echo '<th scope="col">Origin Address</th>';
          echo '<th scope="col">Destination Address</th>';
          echo '</tr>';
          echo '</thead>';
          echo '<tbody class="table-group-divider">';

          // Check if there are items to display
          if (mysqli_num_rows($res) > 0) {
              // Loop through the fetched rows and display them
              while ($row = mysqli_fetch_array($res)) {
                  echo '<tr>';
                  // echo '<td><input type="checkbox" name="selected-items[]" value="' . $row['id'] . '"></td>'; // Checkbox for each row
                  echo '<th>' . $row['id'] . '</th>';
                  echo '<td>' . $row['item_name'] . '</td>';
                  echo '<td>' . $row['panjang'] . 'cm x ' . $row['lebar'] . 'cm x ' . $row['tinggi'] . 'cm</td>';
                  echo '<td>' . $row['order_received'] . '</td>';

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

          echo '</tbody>';
          echo '</table>';
          ?>
        </div>
      </div>
      <script>
        // function toggleCheckbox(checkbox) {
        //     var checkboxes = document.getElementsByName('selected-items[]');
        //     checkboxes.forEach(function(item) {
        //         item.checked = checkbox.checked;
        //     });
        // }

        // function generateSchedule() {
        //     var selectedItems = [];
        //     var checkboxes = document.getElementsByName('selected-items[]');
        //     checkboxes.forEach(function(checkbox) {
        //         if (checkbox.checked) {
        //             selectedItems.push(checkbox.value);
        //         }
        //     });

        //     Perform further action with selectedItems array, e.g., generate schedule
        //     console.log(selectedItems); // Placeholder action for demonstration
        // }
          // JavaScript to handle click event on product details button
        document.addEventListener('DOMContentLoaded', function() {
          const productDetailsButtons = document.querySelectorAll('.product-details-btn');
          const modalBody = document.getElementById('productDetailsModalBody');

          productDetailsButtons.forEach(button => {
            button.addEventListener('click', function() {
              // Get the product ID from the data attribute
              const productId = this.getAttribute('data-product-id');
              // Show the modal
              const modal = new bootstrap.Modal(document.getElementById('productDetailsModal' + productId));
              modal.show();
            });
          });
        });
        $(document).ready(function() {
          $('#table-unscheduled').DataTable({
              "pageLength": 10,
              "autoWidth": true,
              "dom": '<"c8tableTools01"lfB><"c8tableBody"t><"c8tableTools02"ipr>'
          });
          $('#table-completed').DataTable({
              "pageLength": 10,
              "autoWidth": true,
              "dom": '<"c8tableTools01"lfB><"c8tableBody"t><"c8tableTools02"ipr>'
          });
          $('#table-on-going').DataTable({
              "pageLength": 10,
              "autoWidth": true,
              "dom": '<"c8tableTools01"lfB><"c8tableBody"t><"c8tableTools02"ipr>'
          });
          $('#table-assigndriver').DataTable({
              "pageLength": 10,
              "autoWidth": true,
              "dom": '<"c8tableTools01"lfB><"c8tableBody"t><"c8tableTools02"ipr>'
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
          if (tabParam) {
              $('.nav-link[href="#' + tabParam + '"]').addClass('active');
              $('#' + tabParam).addClass('show active');
          } else {
              <?php
              $nodriver_sql = "SELECT COUNT(*) FROM `truck_driver` WHERE `id_driver1` IS NULL OR `id_driver2` IS NULL;";
              $nodriver_res = mysqli_query($con, $nodriver_sql);
              if (mysqli_num_rows($nodriver_res) > 0) {
                  while ($nodriver_row = mysqli_fetch_array($nodriver_res)) {
                      if ($nodriver_row['COUNT(*)'] > 0){
                          echo "$('a[href=\"#assigndriver\"]').addClass('active');\n";
                          echo "$('#assigndriver').addClass('show active');\n";
                      } else {
                          echo "$('a[href=\"#on-going\"]').addClass('active');\n";
                          echo "$('#on-going').addClass('show active');\n";
                      }
                  }
              }
              ?>
          }
        });

      </script>

      <?php
        include('footer.php');
      ?>
    </div>


</body>
</html>