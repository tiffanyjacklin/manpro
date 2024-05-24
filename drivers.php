<?php
session_start();
// require "connect.php";

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "logistics_company";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$update_message = "";

// Handle form submission for updating driver details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_driver'])) {
    $driver_id = $_POST['driver_id'];
    $driver_name = $_POST['driver_name'];
    $phone_number = $_POST['phone_number'];
    $experience = $_POST['experience'];
    $driver_status = $_POST['driver_status'];

    $sql = "UPDATE driver SET driver_name='$driver_name', phone_number='$phone_number', experience='$experience', driver_status='$driver_status' WHERE id='$driver_id'";

    if ($conn->query($sql) === TRUE) {
        $update_message = "Driver details updated successfully";
    } else {
        $update_message = "Error updating driver: " . $conn->error;
    }
}

// Fetch all drivers
$sql = "SELECT * FROM driver ORDER BY id, driver_status;";
$result = $conn->query($sql);
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
        <button type="button" class="btn btn-outline-info" onclick="window.location.href='calculate_salary.php'" style="margin-top:20px;">Pay Salary</button>

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
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
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
$conn->close();
?>