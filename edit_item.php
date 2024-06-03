<?php
session_start();
require "connect.php";
include "database.php";

$user_id = $_SESSION['user_id'];
if (isset($_POST["id_edit"])){
    $id_edit = $_POST["id_edit"];

}
if (isset($_POST["add"])){
    // $status = $_POST['status'];
    $id_edit = $_POST["id_edit"];
    $sql_cek_item = "SELECT * FROM `item` WHERE `id` = ".$id_edit.";";
    $res_cek_item = mysqli_query($con, $sql_cek_item);
    $row_cek_item = mysqli_fetch_assoc($res_cek_item);
    $changes = "";
    
    $item_name = $_POST['item_name'];
    if ($row_cek_item['item_name'] != $item_name){
        $changes = $changes . "Item`s name = " . $item_name . " ";
    }
    $panjang = $_POST['panjang'];
    if ($row_cek_item['panjang'] != $panjang){
        $changes = $changes . "Panjang = " . $panjang . " ";
    }
    $lebar = $_POST['lebar'];
    if ($row_cek_item['lebar'] != $lebar){
        $changes = $changes . "Lebar = " . $lebar . " ";
    }
    $tinggi = $_POST['tinggi'];
    if ($row_cek_item['tinggi'] != $tinggi){
        $changes = $changes . "Tinggi = " . $tinggi . " ";
    }
    $weight_kg = $_POST['weight_kg'];
    if ($row_cek_item['weight_kg'] != $weight_kg){
        $changes = $changes . "Weight = " . $weight_kg . " ";
    }
    $id_location_from = $_POST['id_location_from'];
    if ($row_cek_item['id_location_from'] != $id_location_from){
        $changes = $changes . "Origin Address ID = " . $id_location_from . " ";
    }
    $sender_name = $_POST['sender_name'];
    if ($row_cek_item['sender_name'] != $sender_name){
        $changes = $changes . "Sender`s name = " . $sender_name . " ";
    }
    $sender_phone_num = str_replace('-', '', $_POST['sender_phone_num']);
    if ($row_cek_item['sender_phone_num'] != $sender_phone_num){
        $changes = $changes . "Sender`s Phone Number = " . $sender_phone_num . " ";
    }
    $id_location_to = $_POST['id_location_to'];
    if ($row_cek_item['id_location_to'] != $id_location_to){
        $changes = $changes . "Destination Address ID = " . $id_location_to . " ";
    }
    $receiver_name = $_POST['receiver_name'];
    if ($row_cek_item['receiver_name'] != $receiver_name){
        $changes = $changes . "Receiver`s name = " . $receiver_name . " ";
    }
    $receiver_phone_num = str_replace('-', '', $_POST['receiver_phone_num']);
    if ($row_cek_item['receiver_phone_num'] != $receiver_phone_num){
        $changes = $changes . "Receiver`s Phone Number = " . $receiver_phone_num . " ";
    }
    $category;
    $ongkir_sementara;
    $ongkir;

    $volume = $panjang * $lebar * $tinggi / 6000;//MENGHITUNG VOLUMETRIK BENDA
    
    $cat_volume;
    $cat_berat;

    // PENGKATEGORIAN VOLUMETRIK
    if($volume <= 3){
        $cat_volume = 1;
    }else if($volume > 3 && $volume <=6){
        $cat_volume = 2;
    }else{
        $cat_volume = 3;
    }

    // PENGKATEGORIAN BERAT
    if($weight_kg <= 3){
        $cat_berat = 1;
    }else if($weight_kg > 3 && $weight_kg <=6){
        $cat_berat = 2;
    }else{
        $cat_berat = 3;
    }

    //MENENTUKAN CATEGORY
    if($cat_berat >= $cat_volume){
        $category = $cat_berat;
        // echo "betul";
    }else{
        $category = $cat_volume;
        // echo "lah";
    }

    //MENGHITUNG ONGKIR SEMENTARA
    

    $get_base_price = "SELECT base_price FROM category WHERE id = $category";
    $result = $db->query($get_base_price);
    
    if ($result->num_rows > 0) {
        // Fetch all rows as an associative array
        $rows = $result->fetch_all(MYSQLI_ASSOC);

        // Loop through the rows
        foreach ($rows as $row) {
            // Access the value from the column
            $base_price = $row["base_price"];
            // echo "base price: " . $base_price;
        }
    }
    $get_multiplier_per_kg = "SELECT multiplier_per_kg FROM category WHERE id = $category";
    $result = $db->query($get_multiplier_per_kg);
    if ($result->num_rows > 0) {
        // Fetch all rows as an associative array
        $rows = $result->fetch_all(MYSQLI_ASSOC);

        // Loop through the rows
        foreach ($rows as $row) {
            // Access the value from the column
            $multiplier_per_kg = $row["multiplier_per_kg"];
            // echo "multiplier: " . $multiplier_per_kg;
        }
    }

    if($category == 1){
        $ongkir_sementara = $weight_kg * $multiplier_per_kg;
    }else if($category == 2){
        $ongkir_sementara = $base_price;
    }else{
        $get_max_weight_kg_before = "SELECT max_weight_kg FROM category WHERE id = 2";
        $result = $db->query($get_max_weight_kg_before);
        if ($result->num_rows > 0) {
            // Fetch all rows as an associative array
            $rows = $result->fetch_all(MYSQLI_ASSOC);
    
            // Loop through the rows
            foreach ($rows as $row) {
                // Access the value from the column
                $max_weight_kg_before = $row["max_weight_kg"];
                // echo "max_weight sebelumnya: " . $max_weight_kg_before;
            }
        }
            $ongkir_sementara = ($weight_kg-$max_weight_kg_before) * $multiplier_per_kg + $base_price;
        }

    //MENGHITUNG ONGKIR TOTAL
    $get_distance = "SELECT distance_m FROM country_map WHERE id_location_from = $id_location_from AND id_location_to = $id_location_to";
    $result = $db->query($get_distance);
    if ($result->num_rows > 0) {
        // Fetch all rows as an associative array
        $rows = $result->fetch_all(MYSQLI_ASSOC);
    
        // Loop through the rows
        foreach ($rows as $row) {
            // Access the value from the column
            $distance_m = $row["distance_m"];
            // echo "distance: " . $distance_m;
        }
    }
    // echo "volume" . $volume;
    // echo "\ncat_volum" . $cat_volume;
    // echo "\nberat" . $weight_kg;
    // echo "\ncat_berat" . $cat_berat;
    // echo "\ncategory" . $category;
    // echo "\nongkir sem " .$ongkir_sementara;
    // echo "\ndistance " .$distance_m;
    $dist = round($distance_m/1000);
    // echo $dist;
    $ongkir = ($ongkir_sementara + round($distance_m/1000))*1000;
    // echo "ongkirnya " . $ongkir;


    $sql = "UPDATE `item` 
            SET `item_name` = '$item_name', 
                `panjang` = $panjang, 
                `lebar` = $lebar, 
                `tinggi` = $tinggi, 
                `weight_kg` = $weight_kg, 
                `category` = $category, 
                `shipping_cost` = $ongkir, 
                `id_location_from` = $id_location_from, 
                `sender_name` = '$sender_name', 
                `sender_phone_num` = '$sender_phone_num', 
                `id_location_to` = $id_location_to, 
                `receiver_name` = '$receiver_name', 
                `receiver_phone_num` = '$receiver_phone_num'
            WHERE `id` = ".$id_edit.";";

   if($db->query($sql)){
        echo "<div class='alert alert-info' role='alert'>
                Barang berhasil diperbarui.
              </div>";
        mysqli_query($con, "INSERT INTO `log` (`id_admin`, `id_table`, `action`, `id_item`, `detail_action`, `timestamp`) VALUES ($user_id, 1, 2, $id_edit, '".$changes."', current_timestamp());");
        
        echo "<script>
                setTimeout(function() {
                    $('.alert').fadeOut('slow');
                }, 5000); // 10 seconds
              </script>";
        header("Location: schedule.php");

    } else {
        echo "<div class='alert alert-danger' role='alert'>
                Barang gagal ditambahkan.
              </div>";
    }
    
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Edit Item</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
        <div class="container">
            <?php
                $sql_item = "SELECT * FROM `item` WHERE `id` = ".$id_edit.";";
                $res_item = mysqli_query($con, $sql_item);
                if (mysqli_num_rows($res_item) > 0) {
                    $row_item = mysqli_fetch_assoc($res_item);
            ?>
            <div class="mx-auto custom-col-woi" style="margin-bottom:20px;">
                <form class="row g-3" action="edit_item.php" method="POST">
                    <div class="col-12 d-flex justify-content-between" style="padding-top: 20px; padding-bottom: 20px;">
                        <button type="button" class="btn btn-outline-info" onclick="window.location.href='items.php'">Back</button>
                        <h5 class="title-form">Edit Item Form</h5>
                        <button class="btn btn-info" type="submit" name="add">Save</button>
                    </div>

                    <input type="hidden" name="id_edit" value="<?php echo $id_edit; ?>">
                    
                    <div class="col-md-8">
                        <label for="item_name" class="form-label">Nama Barang</label>
                        <input type="text" class="form-control" id="item_name" placeholder="Nama Barang" name="item_name" value="<?php echo $row_item['item_name'] ; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="weight_kg" class="form-label">Berat Barang (kg)</label>
                        <input type="number" class="form-control" id="weight_kg" placeholder="1" name="weight_kg" value="<?php echo $row_item['weight_kg'] ; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="panjang" class="form-label">Panjang Barang (cm)</label>
                        <input type="number" class="form-control" id="panjang" placeholder="1" name="panjang" value="<?php echo $row_item['panjang'] ; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="lebar" class="form-label">Lebar Barang (cm)</label>
                        <input type="number" class="form-control" id="lebar" placeholder="1" name="lebar" value="<?php echo $row_item['lebar'] ; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="tinggi" class="form-label">Tinggi Barang (cm)</label>
                        <input type="number" class="form-control" id="tinggi" placeholder="1" name="tinggi" value="<?php echo $row_item['tinggi'] ; ?>" required>
                    </div>

                    <div class="col-md-12">
                        <label for="id_location_from" class="form-label">Lokasi Pengirim</label>
                        <select class="form-select" id="id_location_from" name="id_location_from" required>
                        <option value="">Select Location</option>
                        <?php
                        // Assume $con is your database connection
                        $location_sql = "SELECT * FROM `location` ORDER BY `kota_kabupaten`, `alamat`";
                        $location_res = mysqli_query($con, $location_sql);
                        if ($location_res && mysqli_num_rows($location_res) > 0) {
                            while ($location_row = mysqli_fetch_assoc($location_res)) {
                                if ($row_item['id_location_from'] == $location_row['id']){
                                    echo '<option selected value="' . $location_row['id'] . '">' . $location_row['alamat'] . ', ' . $location_row['kelurahan_desa'] . ', ' . $location_row['kecamatan'] . ', ' . $location_row['kota_kabupaten'] . ', Jawa Timur ' . $location_row['kode_pos'] . '</option>';
                                }
                                else{
                                echo '<option value="' . $location_row['id'] . '">' . $location_row['alamat'] . ', ' . $location_row['kelurahan_desa'] . ', ' . $location_row['kecamatan'] . ', ' . $location_row['kota_kabupaten'] . ', Jawa Timur ' . $location_row['kode_pos'] . '</option>';
                                }
                            }
                        
                        }
                        ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="sender_name" class="form-label">Nama Pengirim</label>
                        <input type="text" class="form-control" id="sender_name" placeholder="Nama Pengirim" name="sender_name" value="<?php echo $row_item['sender_name'] ; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="sender_phone_num" class="form-label">Nomor Telepon Pengirim</label>
                        <input type="text" class="form-control" id="sender_phone_num" placeholder="08xx-xxxx-xxxx" name="sender_phone_num" value="<?php echo $row_item['sender_phone_num'] ; ?>" oninput="formatPhoneNumber(this)"  required>
                    </div>
                    <div class="col-md-12">
                        <label for="id_location_to" class="form-label">Lokasi Penerima</label>
                        <select class="form-select" id="id_location_to" name="id_location_to" required>
                        <option value="">Select Location</option>
                        <?php
                        // Assume $con is your database connection
                        $location_sql = "SELECT * FROM `location` ORDER BY `kota_kabupaten`, `alamat`";
                        $location_res = mysqli_query($con, $location_sql);
                        if ($location_res && mysqli_num_rows($location_res) > 0) {
                            while ($location_row = mysqli_fetch_assoc($location_res)) {
                                if ($row_item['id_location_to'] == $location_row['id']){
                                    echo '<option selected value="' . $location_row['id'] . '">' . $location_row['alamat'] . ', ' . $location_row['kelurahan_desa'] . ', ' . $location_row['kecamatan'] . ', ' . $location_row['kota_kabupaten'] . ', Jawa Timur ' . $location_row['kode_pos'] . '</option>';
                                }
                                else{
                                echo '<option value="' . $location_row['id'] . '">' . $location_row['alamat'] . ', ' . $location_row['kelurahan_desa'] . ', ' . $location_row['kecamatan'] . ', ' . $location_row['kota_kabupaten'] . ', Jawa Timur ' . $location_row['kode_pos'] . '</option>';
                                }
                            }
                        }
                        ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="receiver_name" class="form-label">Nama Penerima</label>
                        <input type="text" class="form-control" id="receiver_name" placeholder="Nama Penerima" name="receiver_name" value="<?php echo $row_item['receiver_name'] ; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="receiver_phone_num" class="form-label" >Nomor Telepon Penerima</label>
                        <input type="text" class="form-control" id="receiver_phone_num" placeholder="08xx-xxxx-xxxx" name="receiver_phone_num" value="<?php echo $row_item['receiver_phone_num'] ; ?>" oninput="formatPhoneNumber(this)" required>
                    </div>
                    
                </form>
            </div>
            <?php              
                }
            ?>
        </div>
    <script>
        function formatPhoneNumber(input) {
            // Remove all non-numeric characters
            var phoneNumber = input.value.replace(/\D/g, '');
            
            // Check if the input is not empty
            if(phoneNumber.length > 0) {
            // Insert hyphens at the appropriate positions
            if (phoneNumber.length > 4) {
                phoneNumber = phoneNumber.slice(0, 4) + '-' + phoneNumber.slice(4);
            }
            if (phoneNumber.length > 8) {
                phoneNumber = phoneNumber.slice(0, 9) + '-' + phoneNumber.slice(9);
            }
        }
            
            // Set the formatted value back to the input field
            input.value = phoneNumber;
        }
    </script>
    <?php
      include('footer.php');
    ?>
    </div>

</body>
</html>