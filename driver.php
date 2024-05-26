<?php
// Database connection
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "logistics_company";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the current user's data
$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT * FROM admin WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$manager = $result->fetch_assoc();

// Fetch all drivers
$sql = "SELECT * FROM driver";
$result = $conn->query($sql);


$update_message = "";

// Handle form submission for updating driver details
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_driver'])) {
    $driver_id = $_POST['driver_id'];
    $driver_name = $_POST['driver_name'];
    $phone_number = $_POST['phone_number'];
    // $total_distance = $_POST['total_distance'];
    $experience = $_POST['experience'];
    $driver_status = $_POST['driver_status'];

    $sql = "UPDATE driver SET driver_name='$driver_name', phone_number='$phone_number', experience='$experience', driver_status='$driver_status' WHERE id='$driver_id'";

    if ($conn->query($sql) === TRUE) {
        $update_message = "Driver details updated successfully";
    } else {
        $update_message = "Error updating driver: " . $conn->error;
    }
}




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Driver</title>
    <style>
        /* body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        } */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            /* padding-top: 60px; */
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            text-align: center;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
    </style>
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
            
            <div class="title-form">Driver Details</div>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Driver Name</th>
                    <th>Phone Number</th>
                    <th>Total Distance</th>
                    <th>Experience</th>
                    <th>Driver Status</th>
                    <th>Actions</th>
                </tr>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['driver_name']}</td>
                            <td>{$row['phone_number']}</td>
                            <td>{$row['total_distance']}</td>
                            <td>{$row['experience']}</td>
                            <td>{$row['driver_status']}</td>
                            <td>
                                <button class='btn btn-outline-dark'  onclick=\"showEditForm('{$row['id']}', '{$row['driver_name']}', '{$row['phone_number']}', '{$row['experience']}', '{$row['driver_status']}')\">Edit</button>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No drivers found</td></tr>";
                }
                ?>
            </table>
        </div>

    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <form method="POST">
                <input type="hidden" id="driver_id" name="driver_id">
                <div class="form-group">
                    <label for="driver_name">Driver Name:</label>
                    <input class="form-control" type="text" id="driver_name" name="driver_name" required>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number:</label>
                    <input class="form-control" type="text" id="phone_number" name="phone_number" required>
                </div>
                <!-- <div class="form-group">
                    <label for="total_distance">Total Distance:</label>
                    <input class="form-control" type="text" id="total_distance" name="total_distance" required>
                </div> -->
                <div class="form-group">
                    <label for="experience">Experience:</label>
                    <input class="form-control" type="text" id="experience" name="experience" required>
                </div>
                <div class="form-group">
                    <label for="driver_status">Driver Status:</label>
                    <input class="form-control" type="text" id="driver_status" name="driver_status" required>
                </div>
                <div class="form-group">
                    <button class="btn  btn-outline-dark" type="submit" name="update_driver">Update Driver</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($update_message != ""): ?>
    <script>
        alert("<?php echo $update_message; ?>");
    </script>
    <?php endif; ?>

    <script>
        function showEditForm(id, name, phone, distance, experience, status) {
            document.getElementById('driver_id').value = id;
            document.getElementById('driver_name').value = name;
            document.getElementById('phone_number').value = phone;
            // document.getElementById('total_distance').value = distance;
            document.getElementById('experience').value = experience;
            document.getElementById('driver_status').value = status;
            document.getElementById('editModal').style.display = 'block';
        }
    </script>
     <?php
      include('footer.php');
    ?>
</body>
</html>

<?php
$conn->close();
?>
