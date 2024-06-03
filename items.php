<?php
session_start();
require "connect.php";

$user_id = $_SESSION['user_id'];
$query = $con->prepare("SELECT * FROM admin WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$manager = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Items</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
    <div class="container">
        <div style="margin-top:20px;">
          <?php if ($manager['position'] == 2): ?>
            <button class="btn btn-outline-info" onclick="redirectToNewPage()">Add Item</button>
          <?php endif; ?>
        
          </div>
      <?php
      include "database.php";
      $sql = "SELECT * FROM item";
      $result = $db->query($sql);

      if ($result->num_rows > 0) {
          // Output table header

          echo "<table class='table table-hover fixed-size-table table-unscheduled' id='table-items'>
                <thead>
                  <tr>
                      <th>ID</th>
                      <th>Status</th>
                      <th>Item Name</th>
                      <th>Dimension</th>
                      <th>Weight (kg)</th>
                      <th>Category</th>
                      <th>Shipping Cost</th>
                      <th>Order Received</th>
                      <th>Order Completed</th>
                      <th>Location From</th>
                      <th>Sender Name</th>
                      <th>Sender Phone Num</th>
                      <th>Location To</th>
                      <th>Receiver Name</th>
                      <th>Receiver Phone Num</th>
                  </tr>
                </thead>
                <tbody>";
      
          // Output data of each row
          while ($row = $result->fetch_assoc()) {
              echo "<tr>
                      <td>".$row["id"]."</td>
                      <td>";
              if ($row["status"] == 0){
                // echo "Unscheduled";
                echo '<span class="badge rounded-pill text-bg-danger">Unscheduled</span>';
              }else if ($row["status"] == 1){
                // echo "On-going";
                echo '<span class="badge rounded-pill text-bg-warning">Ongoing</span>';
              }else {
                echo '<span class="badge rounded-pill text-bg-success">Done</span>';
                // echo "Done";
              }
              $sql_cek_bisa_edit = "SELECT * FROM `log` JOIN `item` ON `log`.`id_item` = `item`.`id` WHERE `item`.`status` = 0 AND `id_item` = ".$row['id']." AND `id_admin` = ".$user_id." ;";
              $res_cek_bisa_edit = mysqli_query($con, $sql_cek_bisa_edit);
              if (mysqli_num_rows($res_cek_bisa_edit) > 0) {
                echo '<form action="edit_item.php" method="POST" style="padding-top: 50px;">
                        <input type="hidden" name="id_edit" value="'.$row['id'].'">
                        <button class="btn btn-outline-info">Edit Item</button>
                      </form>';
                
              }
              
              
              echo "</td>
                      <td>".$row["item_name"]."</td>
                      <td>" . $row['panjang'] . "cm x " . $row['lebar'] . "cm x " . $row['tinggi'] . "cm </td>
                      <td>".$row["weight_kg"]."</td>
                      <td>".$row["category"]."</td>
                      <td>".$row["shipping_cost"]."</td>
                      <td>".$row["order_received"]."</td>
                      <td>".$row["order_completed"]."</td>";
                // Fetch origin and destination addresses
                $address_sql = "SELECT * FROM `location` WHERE id = " . $row['id_location_from'] . ";";
                $address_res = mysqli_query($con, $address_sql);
                if (mysqli_num_rows($address_res) > 0) {
                    while ($address_row = mysqli_fetch_array($address_res)) {
                        echo '<td>' . $address_row['alamat'] . ',<br>' . $address_row['kelurahan_desa'] . ',<br>' . $address_row['kecamatan'] . ',<br>' . $address_row['kota_kabupaten'] . ',<br> Jawa Timur ' . $address_row['kode_pos'] . '</td>';
                    }
                }
                echo "<td>".$row["sender_name"]."</td>
                      <td>".$row["sender_phone_num"]."</td>";
                // Fetch origin and destination addresses
                $address_sql = "SELECT * FROM `location` WHERE id = " . $row['id_location_to'].";";
                $address_res = mysqli_query($con, $address_sql);
                if (mysqli_num_rows($address_res) > 0) {
                while ($address_row = mysqli_fetch_array($address_res)) {
                    echo '<td>' . $address_row['alamat'] . ',<br>' . $address_row['kelurahan_desa'] . ',<br>' . $address_row['kecamatan'] . ',<br>' . $address_row['kota_kabupaten'] . ',<br> Jawa Timur ' . $address_row['kode_pos'] . '</td>';
                }
                }
              
              echo "<td>".$row["receiver_name"]."</td>
                    <td>".$row["receiver_phone_num"]."</td>
                </tr>";
          }
          echo "</tbody>
          </table>";
      }

  ?>

  <script>
      function redirectToNewPage() {
          // Redirect to the new page when the "Add" button is clicked
          window.location.href = "add_item.php";
      }
      function redirectToEditPage() {
          // Redirect to the new page when the "Add" button is clicked
          window.location.href = "edit_item.php";
      }
      $(document).ready(function() {
          $('#table-items').DataTable({
              "pageLength": 10,
              "autoWidth": true,
              "dom": '<"c8tableTools01"lfB><"c8tableBody"t><"c8tableTools02"ipr>'
          });
      });
  </script>
  </div>
  <?php
    include('footer.php');
  ?>
  </div>


</body>
</html>