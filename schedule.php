<?php
session_start();
require "connect.php";

if (isset($_POST['id_barang'])) {
  
  $id_barang = $_POST['id_barang'];

  $sql_complete = "UPDATE `schedule` SET `status` = 2, `date_time` = current_timestamp() WHERE `id_barang` = '".$id_barang."';";
  mysqli_query($con, $sql_complete);

  
  $sql_item_complete = "UPDATE `item` SET `status` = 2, `order_completed` = current_timestamp() WHERE `id` = ".$id_barang.";";
  mysqli_query($con, $sql_item_complete);

  $sql_distance =  "SELECT t.`id`, t.`fuel_now`, t.`km_per_liter`, s.`id_location_from`, s.`id_location_dest`, c.`distance_m` FROM `truck` t 
                    JOIN `schedule` s ON t.`id` = s.`id_truk`
                    JOIN `country_map` c ON (s.`id_location_from` = c.`id_location_from`) AND (s.`id_location_dest` = c.`id_location_to`)
                    WHERE s.`id_barang` = ".$id_barang."; ";
  $res_distance = mysqli_query($con, $sql_distance);
  if (mysqli_num_rows($res_distance) > 0) {
    while ($row_product = mysqli_fetch_array($res_distance)) {
      $id_truck = $row_product['id'];
      $sql_truck_fuel = "UPDATE `truck` SET `id_location` = ".$row_product['id_location_from'].", `fuel_now` = ".($row_product['fuel_now']-(($row_product['distance_m']/1000)/$row_product['km_per_liter']))." WHERE `id` = ".$row_product['id'].";";   
      mysqli_query($con,$sql_truck_fuel);

    }
  }
  $sql_update_truck_location = "UPDATE `truck`
                                SET `id_location` = (
                                    SELECT `id_location_from`
                                    FROM `schedule`
                                    WHERE `id_truk` = ".$id_truck." AND `status` = 1
                                    ORDER BY `id_schedule`
                                    LIMIT 1
                                )
                                WHERE id = $id_truck";

  // Execute the SELECT query to check for NULL
  $select_query = "SELECT `id_location_from`
                    FROM `schedule`
                    WHERE `id_truk` = ".$id_truck." AND `status` = 1
                    ORDER BY `id_schedule`
                    LIMIT 1";
  $result = mysqli_query($con, $select_query);

  // Check if the SELECT query returned any rows
  if ($result && mysqli_num_rows($result) > 0) {
  // The SELECT query returned a non-NULL value
  // Proceed with the UPDATE query
  mysqli_query($con, $sql_update_truck_location);


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
        <li class="nav-item">
          <a class="nav-link" href="#on-going">On Going</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#completed">Completed</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#unscheduled">Unscheduled</a>
        </li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade" id="on-going">
        <?php
            $sql = "SELECT * FROM `schedule` WHERE `date_time` IS NULL;";
            $res = mysqli_query($con, $sql);

            echo '<table class="table table-hover fixed-size-table table-on-going"" id="table-on-going">';
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
                            <button type="submit" class="btn btn-outline-info btn-sm">Complete</button>
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
                              Order received: '.$row_product['order_received'].'
                            </td>';                      
                    }
                  }
                  $sql_truck = "SELECT * FROM `truck` WHERE id = ".$row['id_truk'].";";
                  $res_truck = mysqli_query($con, $sql_truck);
                  if (mysqli_num_rows($res_truck) > 0) {
                    while ($row_truck = mysqli_fetch_array($res_truck)) {
                      echo '<td>
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
                  $address_sql = "SELECT * FROM `location` WHERE id = " . $row['id_location_from'] . " OR id = " . $row['id_location_dest'];
                  $address_res = mysqli_query($con, $address_sql);
                  if (mysqli_num_rows($address_res) > 0) {
                      while ($address_row = mysqli_fetch_array($address_res)) {
                          echo '<td>' . $address_row['alamat'] . ',<br>' . $address_row['kelurahan_desa'] . ',<br>' . $address_row['kecamatan'] . ',<br>' . $address_row['kota_kabupaten'] . ',<br> Jawa Timur ' . $address_row['kode pos'] . '</td>';
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
            $sql = "SELECT * FROM `schedule` WHERE `date_time` IS NOT NULL;";
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
                  $sql_truck = "SELECT * FROM `truck` WHERE id = ".$row['id_truk'].";";
                  $res_truck = mysqli_query($con, $sql_truck);
                  if (mysqli_num_rows($res_truck) > 0) {
                    while ($row_truck = mysqli_fetch_array($res_truck)) {
                      echo '<td>
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
                  $address_sql = "SELECT * FROM `location` WHERE id = " . $row['id_location_from'] . " OR id = " . $row['id_location_dest'];
                  $address_res = mysqli_query($con, $address_sql);
                  if (mysqli_num_rows($address_res) > 0) {
                      while ($address_row = mysqli_fetch_array($address_res)) {
                          echo '<td>' . $address_row['alamat'] . ',<br>' . $address_row['kelurahan_desa'] . ',<br>' . $address_row['kecamatan'] . ',<br>' . $address_row['kota_kabupaten'] . ',<br> Jawa Timur ' . $address_row['kode pos'] . '</td>';
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
                          echo '<td>' . $address_row['alamat'] . ',<br>' . $address_row['kelurahan_desa'] . ',<br>' . $address_row['kecamatan'] . ',<br>' . $address_row['kota_kabupaten'] . ',<br> Jawa Timur ' . $address_row['kode pos'] . '</td>';
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
          if (!tabParam) {
            $('a[href="#on-going"]').addClass('active');
            $('#on-going').addClass('show active');
          }
        });

      </script>

      <?php
        include('footer.php');
      ?>
    </div>


</body>
</html>