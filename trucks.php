<?php
session_start();
require "connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Trucks</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
      <div class="container">
        <!-- <h5 class="title-form" style="font-size: 40px; margin-top: 10px;">Daftar Truk</h5> -->

        <!-- Add Truck button with styling -->
        <button type="button" class="btn btn-outline-info" onclick="window.location.href='add_truck.php'" style="margin-top:20px;">Add Truck</button>

        <table class="table table-hover fixed-size-table table-generate" id="table-truck">
          <thead>
            <tr>
                <th>ID</th>
                <th>Unique Number</th>
                <th>Total Mileage (km)</th>
                <th>Capacity (kg)</th>
                <th>Dimensions</th>
                <th>Status</th>
                <th>Fuel Capacity</th>
                <th>KM per Liter</th>
                <th>Fuel Type</th>
                <th>Location</th>
                <!-- <th>Nama Driver 1</th>
                <th>Nama Driver 2</th> -->
                <th>Action</th>
            </tr>
          </thead>
          <tbody>  
          <?php
            // Koneksi ke database
            // $con = mysqli_connect("localhost", "root", "", "projek_manpro");

            // Query untuk mendapatkan data truk beserta informasi lainnya
            $query = "SELECT truck.*, fuel.fuel_type, fuel.cost_per_liter, location.alamat, location.kota_kabupaten, location.kecamatan, location.kelurahan_desa, location.kode_pos, location.latitude, location.longitude
                      FROM truck
                      LEFT JOIN fuel ON truck.id_fuel = fuel.id
                      LEFT JOIN location ON truck.id_location = location.id;";
                      
                      
                      // LEFT JOIN truck_driver ON truck.id = truck_driver.id_truck
                      // LEFT JOIN driver driver1 ON truck_driver.id_driver1 = driver1.id
                      // LEFT JOIN driver driver2 ON truck_driver.id_driver2 = driver2.id
                      // LEFT JOIN truck_driver td1 ON truck.id = td1.id_truck AND td1.position = '1'
                      // LEFT JOIN driver driver1 ON td1.id_driver = driver1.id
                      // LEFT JOIN truck_driver td2 ON truck.id = td2.id_truck AND td2.position = '2'
                      // LEFT JOIN driver driver2 ON td2.id_driver = driver2.id";
            $result = mysqli_query($con, $query);

            while ($row = mysqli_fetch_assoc($result)) {
                $status_lama = $row['truck_status'];
                $stat = "";
                if ($status_lama == 1){
                    $stat = "Available";
                }else if ($status_lama == 2){
                    $stat = "Maintenance";
                }else if ($status_lama == 3){
                    $stat = "Delivering";
                }else{
                    $stat = "Unavailable";
                }
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['unique_number']}</td>";
                echo "<td>{$row['total_distance']}</td>";
                echo "<td>{$row['capacity_kg']}</td>";
                echo "<td>" . $row['panjang'] . " cm x " . $row['lebar'] . " cm x " . $row['tinggi'] . " cm</td>";
                echo "<td>{$stat}</td>";
                echo "<td>{$row['fuel_capacity']}</td>";
                echo "<td>{$row['km_per_liter']}</td>";
                echo "<td>{$row['fuel_type']}</td>";
                echo "<td>{$row['alamat']}, <br>{$row['kota_kabupaten']}, <br>{$row['kecamatan']}, <br>{$row['kelurahan_desa']}, <br>Jawa Timur {$row['kode_pos']}</td>";
                // echo "<td>{$row['driver1_name']}</td>";
                // echo "<td>{$row['driver2_name']}</td>";
                echo '<td>
                <form id="TruckForm" action="edit_status.php" method="POST" style="display: inline;">
                  <input type="hidden" name="id" value="'.$row['id'].'">
                  <button type="submit" class="btn btn-outline-info btn-sm">Edit Truck</button>
                </form>
              </td>'; 
                // echo "<td><a href='edit_status.php?id={$row['id']}' class='button'>Edit Truck</a></td>";
                echo "</tr>";
            }
            ?>
          </tbody>  
        </table>

      </div>

      <script>
        $(document).ready(function() {
            $('#table-truck').DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"generate1"lfB><"generateBody"t><"generate2"ipr>'
            });
        });
    </script>
    <?php
      include('footer.php');
    ?>
    </div>


</body>
</html>
