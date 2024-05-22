<?php
session_start();
include "database.php";
require "connect.php";

$status_message = "";
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}
if (isset($_POST["change"])) {
        // Ambil data dari formulir
        // $unique_number = $_POST['unique_number'];
        // $id = $_POST['id'];
        $status = $_POST['status'];
        $id_fuel = $_POST['fuel'];
        $unique_number = $_POST['unique_number'];

        // Tentukan nilai id_fuel berdasarkan km_per_liter
        // $km_per_liter = $_POST['km_per_liter'];
        if ($id_fuel == 1) {
        //     $id_fuel = 1;
            $km_per_liter = 13.3;
        }else if ($id_fuel == 2){
            $km_per_liter = 14.63;
        }else{
            $id_fuel = 6;
            $km_per_liter = 7;

        }

        // Tentukan nilai status yang akan disimpan dalam database
        // $status_db = '';
        // if ($status == 'Available') {
        //     $status_db = 1;
        // } elseif ($status == 'Maintenance') {
        //     $status_db = 2;
        // } elseif ($status == 'Unavailable') {
        //     $status_db = 0;
        // }
        if ($status = 2){
            $sql_input_maintenance_cost = "INSERT INTO `transaction` (`status`, `date_time`, `nominal`, `id_truck`) VALUES (2,current_timestamp(),1000000,".$id.");"; 
            mysqli_query($con,$sql_input_maintenance_cost);
        }
        // Lakukan pembaruan status truk sesuai dengan nilai yang dipilih
        $sql = "UPDATE `truck` SET `truck_status` = '$status', `id_fuel` = '$id_fuel', `km_per_liter` = '$km_per_liter', `unique_number` = '$unique_number'  WHERE `id` = '$id'";
    
        if ($db->query($sql)) {
            $status_message = "Status truk berhasil diubah";
            header("Location: edit_status.php?id=".$id."");

        } else {
            $status_message = "Gagal mengubah status truk";
        }

        
        // } elseif ($km_per_liter == 7) {
        //     $id_fuel = 6;
        // }
    
        // Lakukan pembaruan id_fuel sesuai dengan nilai yang dipilih
        // $sql_id_fuel = "UPDATE truck SET id_fuel = '$id_fuel', km_per_liter = '$km_per_liter' WHERE id = '$id'";
    
        // if ($db->query($sql_id_fuel)) {
        //     $status_message = "Fuel truk berhasil diubah";

            // Lakukan pembaruan km_per_liter sesuai dengan nilai yang dipilih
            // $sql_km_per_liter = "UPDATE truck SET km_per_liter = '$km_per_liter' WHERE unique_number = '$unique_number'";
            // if ($db->query($sql_km_per_liter)) {
            //     $status_message .= " dan Km per liter berhasil diubah";
            // } else {
            //     $status_message .= " tetapi gagal mengubah Km per liter";
            // }
        // } else {
        //     $status_message = "Gagal mengubah Fuel truk";
        // }

}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Edit Truck</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
      <div class="container">
        <i><?= $status_message ?></i>
        <!-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="trucks.php">Truck</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Truck</li>
            </ol>
        </nav> -->
        <table class="table table-hover fixed-size-table table-generate" id="table-truck">
          <thead>
            <tr>
                <th>ID</th>
                <th>Nomor Unik</th>
                <th>Kapasitas (kg)</th>
                <th>Dimensi</th>
                <th>Status</th>
                <th>Kapasitas Bahan Bakar</th>
                <th>KM per Liter</th>
                <th>Jenis Bahan Bakar</th>
                <th>Lokasi</th>
                <!-- <th>Nama Driver 1</th>
                <th>Nama Driver 2</th> -->
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
                      LEFT JOIN location ON truck.id_location = location.id
                      WHERE truck.id = ".$id.";";
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
                echo "<td>{$row['capacity_kg']}</td>";
                echo "<td>" . $row['panjang'] . " cm x " . $row['lebar'] . " cm x " . $row['tinggi'] . " cm</td>";
                echo "<td>".$stat."</td>";
                echo "<td>{$row['fuel_capacity']}</td>";
                echo "<td>{$row['km_per_liter']}</td>";
                echo "<td>{$row['fuel_type']}</td>";
                echo "<td>{$row['alamat']}, <br>{$row['kota_kabupaten']}, <br>{$row['kecamatan']}, <br>{$row['kelurahan_desa']}, <br>Jawa Timur {$row['kode_pos']}</td>";
                // echo "<td>{$row['driver1_name']}</td>";
                // echo "<td>{$row['driver2_name']}</td>";
                echo "</tr>";
                
                $fuel_type = $row['id_fuel'];
                $unique_number = $row['unique_number'];
                if ($row['capacity_kg'] == 1000){
                    $type = 'CDD';
                } else {
                    $type = 'CDE';
                }
            }
            ?>
          </tbody>  
        </table>
        <div class="row g-3" style="padding-top: 20px;">
            <form action="edit_status.php" method="POST">
                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"> <!-- Include $id as a hidden input field -->
                <div class="row">
                    <div class="col-md-4">                    
                        <label for="unique_number" class="col-form-label">Change Truck's Unique Number</label>
                        <input name="unique_number" class="form-control" value="<?php echo $unique_number; ?>"/> <!-- Include $id as a hidden input field -->
                    </div>
                    
                    <?php
                        if ($status_lama != 3){
                            echo "<div class='col-md-4'>
                            <label for='status' class='col-form-label'>Change Truck's Status</label>
                            <select class='form-select' name='status'>
                                <option value='1' ";
                            if ($status_lama == 1) {
                                echo 'selected';
                            } 
                            echo " ?>Available</option>
                                <option value='2' ";
                            if ($status_lama == 2) {
                                echo 'selected';
                            } 
                            echo " ?>Maintenance</option>
                               <option value='0' "; 
                            if ($status_lama == 0) { 
                                echo 'selected';
                            } 
                            echo " ?>Unavailable</option>
                                </select>
                            </div>";
                        } else{
                            echo "<div class='col-md-4'>
                            <label for='status' class='col-form-label'>Change Truck's Status</label>
                                <select class='form-select' name='status' disabled>
                                    <option value='3' selected>Delivering</option>
                                </select>
                            </div>";
                        }
                    ?>
                    
                
                    <?php 
                     if ($type == 'CDD'){
                        echo '<div class="col-md-4">
                        <label for="fuel" class="col-form-label">Change Truck'."'".'.s Fuel</label>
                        <select class="form-select" name="fuel">';
                        $selected = '';
                        // Assume $con is your database connection
                        $fuel_sql = "SELECT * FROM `fuel` WHERE id = 1 OR id = 2";
                        $fuel_res = mysqli_query($con, $fuel_sql);
                        if ($fuel_res && mysqli_num_rows($fuel_res) > 0) {
                            while ($fuel_row = mysqli_fetch_assoc($fuel_res)) {
                                $selected = ($fuel_row['id'] == $fuel_type) ? 'selected' : ''; // Check if current option is selected
                                echo '<option value="' . $fuel_row['id'] . '" ' . $selected . '>' . $fuel_row['fuel_type'] . '</option>';
                            }
                        }
                     }
                     echo '</select>
                     </div>
                 </div>';
                    ?>
                        
                <div class="row justify-content-center" style="padding-top: 20px;">
                    <div class="col-md-6 text-center">
                        <button type="submit" name="change" class="btn btn-outline-info btn-block">Change</button>
                    </div>
                </div>
            </form>
        </div>

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