<?php
session_start();
require "connect.php";


// Fetch the current user's data
$user_id = $_SESSION['user_id'];
$admin_sql = "SELECT * FROM admin WHERE id = ".$user_id.";";
$admin_result = mysqli_query($con, $admin_sql);
$manager = mysqli_fetch_assoc($admin_result);

$update_message = "";

// Fetch all drivers


// Handle form submission for updating driver details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_driver'])) {
    $driver_id = $_POST['driver_id'];

    $sql_cek_driver = "SELECT * FROM `driver` WHERE `id` = ".$driver_id.";";
    $res_cek_driver = mysqli_query($con, $sql_cek_driver);
    $row_cek_driver = mysqli_fetch_assoc($res_cek_driver);
    $changes = "";
    $driver_name = $_POST['driver_name'];
    if ($driver_name != $row_cek_driver['driver_name']){
      $changes = $changes + "Driver name: " + $driver_name;
    }
    $phone_number = $_POST['phone_number'];
    if ($phone_number != $row_cek_driver['phone_number']){
      $changes = $changes + "Phone number: " + $phone_number;
    }
    $experience = $_POST['experience'];
    if ($experience != $row_cek_driver['experience']){
      $changes = $changes + "Experience: " + $experience;
    }
    $driver_status = $_POST['driver_status'];
    if ($driver_status != $row_cek_driver['driver_status']){
      $driver_condition = ($condition == 1) ? "Working" : (($condition == 2) ? "On Leave" : "Quit");
      $changes = $changes + "Driver status: " + $driver_condition;
    }

    $sql = "UPDATE driver SET driver_name='$driver_name', phone_number='$phone_number', experience='$experience', driver_status='$driver_status' WHERE id='$driver_id'";

    if ($con->query($sql) === TRUE) {
        $update_message = "Driver details updated successfully";
        mysqli_query($con, "INSERT INTO `log` (`id_admin`, `id_table`, `action`, `detail_action`, `timestamp`) VALUES ($user_id, 4, 2, $changes, current_timestamp()); ");
    } else {
        $update_message = "Error updating driver: " . $con->error;
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Driver</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
      <div class="container">
        <?php if ($manager['position'] == 1): ?>
            <button type="button" class="btn btn-outline-info" onclick="window.location.href='calculate_salary.php'" style="margin-top:20px;">Pay Salary</button>
        <?php endif; ?> 
        <table class="table table-hover fixed-size-table table-generate" id="table-driver">
          <thead>
            <tr>
                <th>ID</th>
                <th>Driver Name</th>
                <th>Phone Number</th>
                <th>Total Distance</th>
                <th>Experience</th>
                <th>Driver Status</th>
                <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql_driver = "SELECT * FROM `driver`;";
            $result_driver = mysqli_query($con, $sql_driver);
            if (mysqli_num_rows($result_driver) > 0) {
                while($row = mysqli_fetch_assoc($result_driver)) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['driver_name']}</td>
                        <td>{$row['phone_number']}</td>
                        <td>{$row['total_distance']} km</td>
                        <td>{$row['experience']} year(s)</td>
                        <td>";
                          if ($row['driver_status'] == 1){
                            echo '<span class="badge rounded-pill text-bg-success">Working</span>';
                          } else if ($row['driver_status'] == 2){
                            echo '<span class="badge rounded-pill text-bg-warning">On Leave</span>';
                          } else{
                            echo '<span class="badge rounded-pill text-bg-dark">Quit</span>';
                          }

                          echo "</td>
                        <td>
                          <button class='btn btn-outline-info' onclick=\"showEditForm('{$row['id']}', '{$row['driver_name']}', '{$row['phone_number']}', '{$row['total_distance']}', '{$row['experience']}', '{$row['driver_status']}')\">Edit Driver</button>
                        </td>
                    </tr>";
                }
            }
            ?>
            </tbody>
        </table>
      </div>

      <!-- Edit Modal -->

      <div id="editModal" class="modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">Edit Driver</h5>
                <button type="button" class="btn-close" aria-label="Close"  onclick="document.getElementById('editModal').style.display='none'"></button>
            </div>
          <div class="modal-body">

            <!-- <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span> -->
            <form method="POST">
                <input type="hidden" id="driver_id" name="driver_id">
                <div class="col-md-12">
                    <label class="form-label" for="driver_name">Driver Name</label>
                    <input class="form-control" type="text" id="driver_name" name="driver_name" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label" for="phone_number">Phone Number</label>
                    <input class="form-control" type="text" id="phone_number" name="phone_number" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label" for="experience">Experience (year(s))</label>
                    <input class="form-control" type="number" id="experience" name="experience" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label" for="driver_status">Driver Status</label>
                    <select class="form-select" name='driver_status' id="driver_status">
                      <option value='1'>Working</option>
                      <option value='2'>On Leave</option>
                      <option value='3'>Quit</option>
                    </select>
                    <!-- <input class="form-control" type="text" id="driver_status" name="driver_status" required> -->
                </div>
                <div class="col-md-12 justify-content-center d-flex" style="padding-top: 20px;">
                    <button class="btn btn-outline-info" type="submit" name="update_driver">Update Driver</button>
                </div>
              </div>
            </form>
        </div>
      </div>
      </div>

    <?php if ($update_message != ""): ?>
    <script>
        alert("<?php echo $update_message; ?>");
    </script>
    <?php endif; ?>

    <script>
      $(document).ready(function() {
            $('#table-driver').DataTable({
                "pageLength": 10,
                "autoWidth": true,
                "dom": '<"generate1"lfB><"generateBody"t><"generate2"ipr>'
            });
      });
      function closeEditModal() {
        var modal = document.getElementById('editModal');
        var bsModal = bootstrap.Modal.getInstance(modal);
        bsModal.hide();
      }
      function showEditForm(id, name, phone, distance, experience, status) {
          document.getElementById('driver_id').value = id;
          document.getElementById('driver_name').value = name;
          document.getElementById('phone_number').value = phone;
          document.getElementById('experience').value = experience;
          document.getElementById('driver_status').value = status;
          document.getElementById('editModal').style.display = 'block';
      }
    </script>
    
    <?php
      include('footer.php');
    ?>
    </div>


</body>
</html>
<?php
$con->close();
?>