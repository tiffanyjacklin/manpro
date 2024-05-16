<?php
include "database.php";
require "connect.php";
session_start();

$add_message = "";

if (isset($_POST["add"])){
    // Check if all required fields are not empty
    // if (!empty($_POST['unique_number']) && !empty($_POST['capacity']) && !empty($_POST['panjang']) && !empty($_POST['lebar']) && !empty($_POST['tinggi']) && !empty($_POST['truck_status']) && !empty($_POST['fuel_capacity']) && !empty($_POST['km_per_liter']) && !empty($_POST['id_fuel']) && !empty($_POST['id_location'])) {
        // Assign POST values to variables
        $unique_number = $_POST['unique_number'];
        // $capacity = $_POST['capacity'];
        // $panjang = $_POST['panjang'];
        // $lebar = $_POST['lebar'];
        // $tinggi = $_POST['tinggi'];
        $truck_status = 1;
        $type = $_POST['type'];
        if ($type == 'CDD'){
            $id_fuel = 6;
            $capacity = 2000;
            $km_per_liter = 7;
            $fuel_capacity = 70;
            $fuel_now = 70;
            $panjang = 310;
            $lebar = 175;
            $tinggi = 185;
        } else{
            $id_fuel = 1;
            $capacity = 1000;
            $km_per_liter = 13.3;
            $fuel_capacity = 43;
            $fuel_now = 43;
            $panjang = 230;
            $lebar = 140;
            $tinggi = 124;
        }
        // $fuel_capacity = $_POST['fuel_capacity'];
        // $km_per_liter = $_POST['km_per_liter'];
        // $id_fuel = $_POST['id_fuel'];
        $id_location = $_POST['id_location'];
        // $driver1 = "";
        // $driver2 = "";
        // $driver1_sql = "SELECT id_driver1, COUNT(*) AS driver_count
        //                 FROM truck_driver
        //                 -- WHERE position = 1
        //                 GROUP BY id_driver
        //                 ORDER BY driver_count ASC
        //                 LIMIT 1;";
        // $driver1_res = mysqli_query($con, $driver1_sql);
        // if ($driver1_res && mysqli_num_rows($driver1_res) > 0) {
        //     while ($driver1_row = mysqli_fetch_assoc($driver1_res)) {
        //         $driver1 = $driver1_row['id_driver'];
        //     }
        // }
        // $driver2_sql = "SELECT id_driver2, COUNT(*) AS driver_count
        //                 FROM truck_driver
        //                 -- WHERE position = 2
        //                 GROUP BY id_driver
        //                 ORDER BY driver_count ASC
        //                 LIMIT 1;";
        // $driver2_res = mysqli_query($con, $driver2_sql);
        // if ($driver2_res && mysqli_num_rows($driver2_res) > 0) {
        //     while ($driver2_row = mysqli_fetch_assoc($driver2_res)) {
        //         $driver2 = $driver2_row['id_driver'];
        //     }
        // }
        
        // Create and execute the SQL query
        $sql = "INSERT INTO truck (unique_number, capacity_kg, panjang, lebar, tinggi, truck_status, fuel_capacity, fuel_now, km_per_liter, id_fuel, id_location) 
        VALUES ('$unique_number', '$capacity', $panjang, $lebar, $tinggi, $truck_status, $fuel_capacity, $fuel_now, $km_per_liter, $id_fuel, '$id_location')";
        if($db->query($sql)){
            $add_message = "Truck berhasil ditambahkan";
            // $id_sql = "SELECT id FROM `truck` ORDER BY `truck`.`id` DESC LIMIT 1;";
            // $id_res = mysqli_query($con, $id_sql);
            // if ($id_res && mysqli_num_rows($id_res) > 0) {
            //     while ($id_row = mysqli_fetch_assoc($id_res)) {
            //         $id_sc = $id_row['id_driver'];
            //     }
            // }
            // mysqli_query($con,"INSERT INTO `truck_driver` (`id_truck`, `id_schedule`, `id_driver1`, `id_driver2`) VALUES ($id_sc, $driver1, $driver2);");
            // mysqli_query($con,"INSERT INTO `truck_driver` (`id_truck`, `id_driver`, `position`) VALUES ($id_sc, $driver1, 1);");
            // mysqli_query($con,"INSERT INTO `truck_driver` (`id_truck`, `id_driver`, `position`) VALUES ($id_sc, $driver2, 2);");
            
            header("Location: trucks.php");
        } else {
            $add_message = "Data truck tidak masuk";
        } 
    // }  
    // } else {
    //     $add_message = "Semua field harus diisi";
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
        <i><?= $add_message ?></i>        
        <form class="row" action="add_truck.php" method="POST">

        <div class="col-12 d-flex justify-content-between" style="padding-top: 20px;">
                    <button type="button" class="btn btn-outline-info" onclick="window.location.href='items.php'">Back</button>
                    <h5 class="title-form">Add Truck Form</h5>
                    <button class="btn btn-info" type="submit" name="add">Add</button>
                </div>
        <h5 class="title-form" style="margin-top: 20px; margin-bottom: 20px;"><h5>
        <table class="table table-hover fixed-size-table table-truck-type" id="table-truck-type">
            <thead>
                <tr>
                    <th scope="col">Type</th>
                    <th scope="col">Dimension</th>
                    <th scope="col">Truck Capacity</th>
                    <th scope="col">Fuel Type</th>
                    <th scope="col">Fuel Capacity</th>
                    <th scope="col">km/L</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>CDD</td>
                    <td>P: 310 cm<br>
                        L: 175 cm<br>
                        T: 185 cm</td>
                    <td>2000 kg</td>
                    <td>Solar JBT</td>
                    <td>70 Liter</td>
                    <td>7</td>
                </tr>
                <tr>
                    <td>CDE</td>
                    <td>P: 230 cm<br>
                        L: 140 cm<br>
                        T: 124 cm</td>
                    <!-- <td>230 cm x 140 cm x 124 cm</td> -->
                    <td>1000 kg</td>
                    <td>Pertalite</td>
                    <td>43 Liter</td>
                    <td>13.3</td>
                </tr>
            </tbody>
        </table>
        <div class="mx-auto custom-col-woi" style="margin-bottom:20px; padding-top: 20px;">
            <div class="row g-3">        
        <!-- <div class="col-12 d-flex justify-content-between" style="padding-top: 20px; padding-bottom: 20px;">
                    <button type="button" class="btn btn-outline-info" onclick="window.location.href='items.php'">Back</button>
                    <h5 class="title-form">Add Truck Form</h5>
                    <button class="btn btn-info" type="submit" name="add">Add</button>
                </div> -->

                <div class="col-md-6">
                    <label for="unique_number" class="form-label">Unique Number</label>
                    <input type="text" class="form-control" id="unique_number" placeholder="XX 0000 XXX" name="unique_number" required oninput="formatUniqueNumber(this)">
                </div>
            
                <div class="col-md-6">
                    <label for="type" class="form-label">Truck's Type</label>
                    <select class="form-select" name="type" required>
                        <option value="">Select Truck's Type</option>
                        <option value="CDE">CDE</option>
                        <option value="CDD">CDD</option>
                    </select>
                </div>
                <!-- <div class="col-md-4">
                    <label for="panjang" class="form-label">Panjang (cm)</label>
                    <input type="number" class="form-control" id="panjang" placeholder="Panjang (cm)" name="panjang" required>
                </div>
                <div class="col-md-4">
                    <label for="lebar" class="form-label">Lebar (cm)</label>
                    <input type="number" class="form-control" id="lebar" placeholder="Lebar (cm)" name="lebar" required>
                </div>
                <div class="col-md-4">
                    <label for="tinggi" class="form-label">Tinggi (cm)</label>
                    <input type="number" class="form-control" id="tinggi" placeholder="Tinggi (cm)" name="tinggi" required>
                </div> -->
                
                <div class="col-md-12">
                    <label for="id_location" class="form-label">Truck Location</label>
                    <select class="form-select" id="id_location" name="id_location" required>
                    <option value="">Select Location</option>
                    <?php
                    // Assume $con is your database connection
                    $location_sql = "SELECT * FROM `location` ORDER BY `kota_kabupaten`, `alamat`";
                    $location_res = mysqli_query($con, $location_sql);
                    if ($location_res && mysqli_num_rows($location_res) > 0) {
                        while ($location_row = mysqli_fetch_assoc($location_res)) {
                            echo '<option value="' . $location_row['id'] . '">' . $location_row['alamat'] . ', ' . $location_row['kelurahan_desa'] . ', ' . $location_row['kecamatan'] . ', ' . $location_row['kota_kabupaten'] . ', Jawa Timur ' . $location_row['kode_pos'] . '</option>';
                        }
                    }
                    ?>
                    </select>
                </div>
                </div>
            </form>
        </div>

<!-- <script>
    function formatUniqueNumber(input) {
        // Remove any non-alphanumeric characters
        let value = input.value.replace(/[^a-zA-Z0-9]/g, '');

        // Convert to uppercase
        value = value.toUpperCase();

        // Apply formatting
        let formattedValue = '';
        for (let i = 0; i < value.length; i++) {
            // Insert space after 2nd and 6th character if they are not already spaces
            if ((i === 2 || i === 6) && value[i] !== ' ') {
                formattedValue += ' ';
            }

            // Check if current character is alphabet or number
            if (/[a-zA-Z]/.test(value[i])) {
                // Alphabet: limit to 2 characters
                if (formattedValue.replace(/[^a-zA-Z]/g, '').length < 2) {
                    formattedValue += value[i];
                }
            } else if (/[0-9]/.test(value[i])) {
                // Number: limit to 4 characters
                if (formattedValue.replace(/[^0-9]/g, '').length < 4) {
                    formattedValue += value[i];
                }
            }
        }

        // Update input value
        input.value = formattedValue;
    }
</script> -->



      </div>
      <script>
        $(document).ready(function() {
            $('#table-truck-type').DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"generateBody"t>'
            });
        });
    </script>
    <?php
      include('footer.php');
    ?>
    </div>

</body>
</html>