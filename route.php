<?php
session_start();
require "connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Route</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
    
      <div class="container" style="padding-top: 20px;">
      <div class="title-form" style="padding-bottom: 10px;">On Delivery Route</div>

      <div class="col-md-12">
        <?php 
          $admin_name = $_SESSION['username'];
          $position = $_SESSION['position'];
          $query_id_truk = "";

          // if ($admin_name == "admin") {
          if ($position == 1 or $position == 2) {
            // Kondisi 1: login dengan username "admin"
            $query_id_truk = "SELECT DISTINCT `truck`.`id`, `truck`.`unique_number`, d1.`driver_name` AS driver1_name, d2.`driver_name` AS driver2_name, `schedule`.`id_schedule` FROM `truck` 
                            JOIN `truck_driver` ON `truck`.`id` = `truck_driver`.`id_truck` 
                            JOIN `schedule` ON `schedule`.`id_schedule` = `truck_driver`.`id` 
                            JOIN `driver` d1 ON d1.id = `truck_driver`.`id_driver1`
                            JOIN `driver` d2 ON d2.id = `truck_driver`.`id_driver2`
                            WHERE `schedule`.`status` = 1;";
          } else {
            // Kondisi 2: login dengan username == driver_name
            $query_id_truk = "SELECT DISTINCT `truck`.`id`, `truck`.`unique_number`, d1.`driver_name` AS driver1_name, d2.`driver_name` AS driver2_name, `schedule`.`id_schedule` FROM `truck` 
                              JOIN `truck_driver` ON `truck`.`id` = `truck_driver`.`id_truck` 
                              JOIN `schedule` ON `schedule`.`id_schedule` = `truck_driver`.`id` 
                              JOIN `driver` d1 ON d1.id = `truck_driver`.`id_driver1`
                              JOIN `driver` d2 ON d2.id = `truck_driver`.`id_driver2`
                              WHERE `schedule`.`status` = 1
                              AND (d1.`driver_name` = '$admin_name' OR d2.`driver_name` = '$admin_name');";
          }
          
          $res_id_truk = mysqli_query($con, $query_id_truk);
          $count = 1;
          $truck_list = [];
          $route_name = [];
          if (mysqli_num_rows($res_id_truk) > 0) {
            echo '<div class="accordion" id="accordionExample">';
            while ($row_id_truk = mysqli_fetch_array($res_id_truk)) {
              $truck_list[] = $row_id_truk['id'];
              $count_row = 0;
              $check = 0;
              echo '<div class="accordion-item">
                      <h2 class="accordion-header custom-accordion-header">
                        <button class="accordion-button '; 
                        if ($count != 1){
                          echo 'collapsed';
                        }           
                        echo ' " type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne'.$row_id_truk['id'].'" ';
                        if ($count == 1){
                          echo 'aria-expanded="true"';
                        } else {
                          echo 'aria-expanded="false"';
                        }
                        echo ' aria-controls="collapseOne'.$row_id_truk['id'].'">
                          Unique number: '.$row_id_truk['unique_number'].'<br><br>
                          Driver 1: '.$row_id_truk['driver1_name'].'<br>
                          Driver 2: '.$row_id_truk['driver2_name'].'
                        </button>
                      </h2>
                      <div id="collapseOne'.$row_id_truk['id'].'" class="accordion-collapse collapse '; 
                      if ($count == 1){
                        echo 'show';
                        $count = $count + 1;
                      } 
                      echo '" data-bs-parent="#accordionExample">
                        <div class="accordion-body" style="min-width: 100% !important; width: 100% !important;">
                          <div class="container">';
                      $origin = [];
                      $dest   = [];
                      $row_count_location = 0;    
                      $sql_location = 'SELECT `route`.*, CONCAT_WS(
                        ", ",
                        `location`.`alamat`, 
                        `location`.`kelurahan_desa`, 
                        `location`.`kecamatan`, 
                        `location`.`kota_kabupaten`, CONCAT("Jawa Timur ", 
                        `location`.`kode_pos`)
                        ) AS `location` FROM `route` JOIN `location` ON `route`.`id_location` = `location`.`id` WHERE `route`.`id_schedule` = '.$row_id_truk['id_schedule'].' ORDER BY `route`.`id`;';
                      $res_location = mysqli_query($con, $sql_location);
                      if (mysqli_num_rows($res_location) > 0){
                        while ($row_loc = mysqli_fetch_array($res_location)){
                          // $row_loc = mysqli_fetch_assoc($res_location);
                          
                            echo '<div class="title-dashboard4"';
                            if ($row_count_location != 0){
                                echo ' style="padding-top: 20px;"';
                            }
                            echo ' >
                              ID Location: '.$row_loc['id_location'].'<br>
                              Address: '.$row_loc['location'].'
                            </div>';
                      
                            echo '<table class="table table-hover fixed-size-table table-routes" id="table-routes'.$row_id_truk['id_schedule'].$row_loc['id_location'].'" style="width: 100%;">';
                            echo '<thead>';
                            echo '<tr>';
                            echo '<th scope="col">Delivery Status</th>';
                            echo '<th scope="col">Product</th>';
                            echo '<th scope="col">Origin Address</th>';
                            echo '<th scope="col">Destination Address</th>';
                            echo '<th scope="col">Order Received</th>';
                            echo '<th scope="col">Order Completed</th>';
                            echo '</tr>';
                            echo '</thead>';
                            echo '<tbody class="table-group-divider">';
                            array_push($route_name, $row_id_truk['id'].$row_loc['id_location']);
                            
                            $sql_each_item = 'SELECT `item`.*, CONCAT_WS(
                                              ", <br>",
                                              `l1`.`alamat`, 
                                              `l1`.`kelurahan_desa`, 
                                              `l1`.`kecamatan`, 
                                              `l1`.`kota_kabupaten`, CONCAT("Jawa Timur ", 
                                              `l1`.`kode_pos`)
                                              ) AS `location_from`,
                                              CONCAT_WS(
                                              ", <br>",
                                              `l2`.`alamat`,
                                              `l2`.`kelurahan_desa`,
                                              `l2`.`kecamatan`, 
                                              `l2`.`kota_kabupaten`, CONCAT("Jawa Timur ", 
                                              `l2`.`kode_pos`)
                                              ) AS `location_dest` FROM `item`
                                              JOIN `location` `l1` ON `l1`.`id` = `item`.`id_location_from`
                                              JOIN `location` `l2` ON `l2`.`id` = `item`.`id_location_to` 
                                              JOIN `schedule` ON `schedule`.`id_barang` = `item`.`id`
                                              WHERE `schedule`.`id_schedule` = '.$row_id_truk['id_schedule'].';';
                            $res_each_item = mysqli_query($con, $sql_each_item);
                            if (mysqli_num_rows($res_each_item) > 0){
                              while ($row_each_item = mysqli_fetch_array($res_each_item)){

                                // echo $row_each_item['id'].' '.$row_each_item['id_location_from'].' '.$row_each_item['id_location_to'].'<br>';
                                // echo count($origin)." ".$row_loc['id_location']." ".$row_each_item['id_location_from']." ".$row_each_item['id_location_to']."<br>";
                                if (count($origin) == 0 AND $row_each_item['id_location_from'] == $row_loc['id_location']){
                                  echo '<tr>';
                                  echo '<td><span class="badge rounded-pill text-bg-warning">Picked up at</span></td>';
                                  echo '<td>ORDER ID: '.$row_each_item['id'].'<br>
                                  '.$row_each_item['item_name'].'</td>';
                                  echo '<td>'.$row_each_item['location_from'].'</td>';
                                  echo '<td>'.$row_each_item['location_dest'].'</td>';
                                  echo '<td>'.$row_each_item['order_received'].'</td>';
                                  echo '<td>'.$row_each_item['order_completed'].'</td>';
                                  echo '</tr>';
                                  array_push($origin, $row_each_item['id']);
                                  
                                } else if ($row_each_item['id_location_from'] == $row_loc['id_location'] AND !in_array($row_each_item['id'], $origin)){
                                  echo '<tr>';
                                  echo '<td><span class="badge rounded-pill text-bg-warning">Picked up at</span></td>';
                                  echo '<td>ORDER ID: '.$row_each_item['id'].'<br>
                                  '.$row_each_item['item_name'].'</td>';
                                  echo '<td>'.$row_each_item['location_from'].'</td>';
                                  echo '<td>'.$row_each_item['location_dest'].'</td>';
                                  echo '<td>'.$row_each_item['order_received'].'</td>';
                                  echo '<td>'.$row_each_item['order_completed'].'</td>';
                                  echo '</tr>';
                                  array_push($origin, $row_each_item['id']);
                                }
                                else if (in_array($row_each_item['id'], $origin) && $row_each_item['id_location_to'] == $row_loc['id_location']){
                                  echo '<tr>';
                                  echo '<td><span class="badge rounded-pill text-bg-info">Delivered to</span></td>';
                                  echo '<td>ORDER ID: '.$row_each_item['id'].'<br>
                                  '.$row_each_item['item_name'].'</td>';
                                  echo '<td>'.$row_each_item['location_from'].'</td>';
                                  echo '<td>'.$row_each_item['location_dest'].'</td>';
                                  echo '<td>'.$row_each_item['order_received'].'</td>';
                                  echo '<td>'.$row_each_item['order_completed'].'</td>';
                                  echo '</tr>';
                                  array_push($dest, $row_each_item['id']);
                                }
                              }
                            }        
                            if (count($origin) == 0){
                                echo '<tr>';
                                echo '<td><span class="badge rounded-pill text-bg-warning">Truck Departing</span></td>';
                                echo '<td>-</td>';
                                echo '<td>-</td>';
                                echo '<td>-</td>';
                                echo '<td>-</td>';
                                echo '<td>-</td>';
                                echo '</tr>';
                                array_push($origin, $row_loc['id_location']);
                            }
                            echo '</tbody>
                            </table>';
                            $row_count_location++;
                          }
                        }
                        echo '
                          </div>
                        </div>
                      </div>
                    </div>';
            }
            echo ' </div>';
          } else {
            echo '<div class="title-dashboard4">There are no trucks making deliveries.</div>';
          }
        ?>
                    
                  </div>
    
      </div>

    <?php
      include('footer.php');
    ?>
    </div>
    <script>
      $(document).ready(function() {
        var route_name = <?php echo json_encode($route_name); ?>;
        route_name.forEach(function(truck_id) {
            $('#table-routes' + truck_id).DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"c8tableBody"t>',
                "createdRow": function(row, data, dataIndex) {
                  var status = $(row).attr('data-status'); // Use 'attr' instead of 'data'
                  if (status == 2) {
                      $(row).addClass('table-secondary');
                  } else if (status == 0) {
                      $(row).addClass('table-info');
                  } else if (status == 1) {
                      $(row).addClass('table-warning');
                  }
                }
            });
        });
      });
    </script>
</body>
</html>