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
          $query_id_truk = "";

          if ($admin_name == "admin") {
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
                      echo'" data-bs-parent="#accordionExample">
                        <div class="accordion-body" style="min-width: 100% !important; width: 100% !important;">
                          <div class="container">';
                          $query_items = 'SELECT `schedule`.`id_barang`, `item`.`item_name`, `item`.`order_received`, `schedule`.`status`, 
                          CONCAT_WS(
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
                          ) AS `location_dest`, `item`.`order_completed` FROM `truck` JOIN `truck_driver` ON `truck`.`id` = `truck_driver`.`id_truck`
                            JOIN `schedule` ON `schedule`.`id_schedule` = `truck_driver`.`id` 
                            JOIN `item` ON `schedule`.`id_barang` = `item`.`id`
                            JOIN `location` `l1` ON `l1`.`id` = `schedule`.`id_location_from`
                            JOIN `location` `l2` ON `l2`.`id` = `schedule`.`id_location_dest` 
                            WHERE `schedule`.`id_schedule` = '.$row_id_truk['id_schedule'].'
                            ORDER BY `schedule`.`id`;';
                          echo '<table class="table table-hover fixed-size-table table-trucks" id="table-trucks'.$row_id_truk['id'].'">';
                          echo '<thead>';
                          echo '<tr>';
                          echo '<th scope="col">Delivery Sequence</th>';
                          echo '<th scope="col">Delivery Status</th>';
                          echo '<th scope="col">Product</th>';
                          echo '<th scope="col">Origin Address</th>';
                          echo '<th scope="col">Destination Address</th>';
                          echo '<th scope="col">Order Received</th>';
                          echo '<th scope="col">Order Completed</th>';
                          echo '</tr>';
                          echo '</thead>';
                          echo '<tbody class="table-group-divider">';
                          $res_items = mysqli_query($con, $query_items);
                          if (mysqli_num_rows($res_items) > 0) {
                            while ($row_items = mysqli_fetch_array($res_items)) {
                            echo '<tr class=" "';
                              if ($row_items['status'] == 2) {
                                  echo ' data-status="2">
                                  <td>'.($count_row+1).'</td>
                                  <td><span class="badge rounded-pill text-bg-dark">Completed</span></td>'; // Add gray background for status 2
                              } elseif ($row_items['status'] == 1 && $check == 0) {
                                  echo ' data-status="0">
                                  <td>'.($count_row+1).'</td>
                                  <td><span class="badge rounded-pill text-bg-success">Delivering</span></td>'; // Add pink background for status 1 and the first item
                                  $check++;
                                }else{
                                  echo ' data-status="1">
                                  <td>'.($count_row+1).'</td>
                                  <td><span class="badge rounded-pill text-bg-warning">Waiting</span></td>';
                                }
                                $count_row++;
                                
                              echo '<td>ORDER ID: '.$row_items['id_barang'].'<br>
                              '.$row_items['item_name'].'</td>';
                              echo '<td>'.$row_items['location_from'].'</td>';
                              echo '<td>'.$row_items['location_dest'].'</td>';
                              echo '<td>'.$row_items['order_received'].'</td>';
                              echo '<td>'.$row_items['order_completed'].'</td>';
                          echo '</tr>';
                                  }
                          }
                        echo '</tbody>
                        </table>
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
        var truck_list = <?php echo json_encode($truck_list); ?>;
        truck_list.forEach(function(truck_id) {
            $('#table-trucks' + truck_id).DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"c8tableTools01"lfB><"c8tableBody"t><"c8tableTools02"ipr>',
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