<?php
session_start();
require "connect.php";

$user_id = $_SESSION['user_id'];
$query = $con->prepare("SELECT * FROM admin WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$manager = $result->fetch_assoc();
if ($manager['position'] != 1){
  header("Location: dashboard.php");
  exit();
}

// $items_per_page = 20;  // Number of log entries per page

// // Determine the current page
// $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// $current_page = max($current_page, 1);  // Ensure current page is at least 1

// // Calculate the offset for the SQL query
// $offset = ($current_page - 1) * $items_per_page;

// Initialize empty filter condition
$filter_condition = "";

// Check if form is submitted with filters
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Store filter values in session variables
  $_SESSION['id_table'] = $_POST['id_table'] ?? '';
  $_SESSION['action'] = $_POST['action'] ?? '';
  $_SESSION['start_date'] = $_POST['start_date'] ?? '';
  $_SESSION['end_date'] = $_POST['end_date'] ?? '';

  // Handle date filter
  $start_date = $_SESSION["start_date"];
  $end_date = $_SESSION["end_date"];

  // If both start_date and end_date are not empty, add the date range condition
  if (!empty($start_date) && !empty($end_date)) {
      $end_date_adjusted = date('Y-m-d', strtotime($end_date . ' +1 day'));
      $filter_condition .= " AND timestamp >= '$start_date' AND timestamp <= '$end_date_adjusted'";
  } 
  // If only start_date is provided, set the condition to filter from start_date to current date
  elseif (!empty($start_date) && empty($end_date)) {
      $filter_condition .= " AND timestamp >= '$start_date'";
  } 
  // If only end_date is provided, set the condition to filter up to end_date from the beginning of time
  elseif (empty($start_date) && !empty($end_date)) {
      $end_date_adjusted = date('Y-m-d', strtotime($end_date . ' +1 day'));
      $filter_condition .= " AND timestamp <= '$end_date_adjusted'";
  }

  // Handle id_table filter
  $id_table = $_POST["id_table"];
  if (!empty($id_table)) {
      $filter_condition .= " AND id_table = $id_table";
  }
  // Handle action filter
  $action = $_POST["action"];
  if (!empty($action)) {
      $filter_condition .= " AND action = $action";
  }
}


$syarat = " WHERE 1=1 $filter_condition"; // Add the filter condition to the SQL query

// Fetch total number of logs for pagination
// $sql_count = "SELECT COUNT(*) as total FROM `log` $syarat;";
// $res_count = mysqli_query($con, $sql_count);
// $row_count = mysqli_fetch_assoc($res_count);
// $total_logs = $row_count['total'];

// Calculate total pages
// $total_pages = ceil($total_logs / $items_per_page);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Log</title>
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="main-content">
        <div class="container">
            <div class="title-form" style="padding-top: 20px; padding-bottom: 20px;">Log Activity</div>

              <form  id="filterForm" method="POST" action="" class="row g-3" style="padding-bottom: 20px;">
                <div class="row g-3 align-items-center">
                    <!-- Table Filter -->
                    <div class="col-md-2">
                      <label for="id_table" class="form-label">Table:</label>
                      <select class="form-select" name="id_table" id="id_table">
                          <option value="0" <?php if(isset($_POST['id_table']) && $_POST['id_table'] == '0') echo 'selected'; ?>>All Tables</option>
                          <option value="1" <?php if(isset($_POST['id_table']) && $_POST['id_table'] == '1') echo 'selected'; ?>>Item</option>
                          <option value="2" <?php if(isset($_POST['id_table']) && $_POST['id_table'] == '2') echo 'selected'; ?>>Truck</option>
                          <option value="3" <?php if(isset($_POST['id_table']) && $_POST['id_table'] == '3') echo 'selected'; ?>>Schedule</option>
                          <option value="4" <?php if(isset($_POST['id_table']) && $_POST['id_table'] == '4') echo 'selected'; ?>>Driver</option>
                          <option value="5" <?php if(isset($_POST['id_table']) && $_POST['id_table'] == '5') echo 'selected'; ?>>Profile</option>
                          <option value="6" <?php if(isset($_POST['id_table']) && $_POST['id_table'] == '6') echo 'selected'; ?>>Transaction</option>
                      </select>
                    </div>

                    <!-- Action Filter -->
                  <div class="col-md-2">
                      <label for="action" class="form-label">Action:</label>
                      <select class="form-select" name="action" id="action">
                          <option value="" <?php if(isset($_POST['action']) && $_POST['action'] == '') echo 'selected'; ?>>All Actions</option>
                          <option value="1" <?php if(isset($_POST['action']) && $_POST['action'] == '1') echo 'selected'; ?>>Add</option>
                          <option value="2" <?php if(isset($_POST['action']) && $_POST['action'] == '2') echo 'selected'; ?>>Edit</option>
                          <option value="3" <?php if(isset($_POST['action']) && $_POST['action'] == '3') echo 'selected'; ?>>Complete</option>
                          <option value="4" <?php if(isset($_POST['action']) && $_POST['action'] == '4') echo 'selected'; ?>>Generate</option>
                          <option value="5" <?php if(isset($_POST['action']) && $_POST['action'] == '5') echo 'selected'; ?>>Pay</option>
                          <option value="6" <?php if(isset($_POST['action']) && $_POST['action'] == '6') echo 'selected'; ?>>Login</option>
                      </select>
                  </div>

                    <!-- Date Filters -->
                  <div class="col-md-2">
                      <label for="start_date" class="form-label">Start Date:</label>
                      <input type="date" class="form-control" name="start_date" id="start_date" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
                  </div>
                  <div class="col-md-2">
                      <label for="end_date" class="form-label">End Date:</label>
                      <input type="date" class="form-control" name="end_date" id="end_date" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
                  </div>
                  <!-- Sorting Options -->
                  <div class="col-md-2">
                      <label for="sort_by" class="form-label">Sort By:</label>
                      <select class="form-select" name="sort_by" id="sort_by">
                          <option value="timestamp_desc" <?php if(isset($_POST['sort_by']) && $_POST['sort_by'] == 'timestamp_desc') echo 'selected'; ?>>Newest to Oldest</option>
                          <option value="timestamp_asc" <?php if(isset($_POST['sort_by']) && $_POST['sort_by'] == 'timestamp_asc') echo 'selected'; ?>>Oldest to Newest</option>
                      </select>
                  </div>
                    <!-- Clear Filter Button -->
                  <div class="col-md-1">
                      <label for="clearFilterBtn" class="form-label">Clear Filter:</label>
                      <button type="button" class="btn btn-transparent" id="clearFilterBtn"><i class="fa-solid fa-xmark"></i></button>
                  </div>
                </div>

              </form>



            <?php 
            // Fetch logs with applied filters and sorting
            $sort_by = $_POST['sort_by'] ?? 'timestamp_desc'; // Default sorting by timestamp in descending order
            switch($sort_by) {
                case 'timestamp_desc':
                    $sql_log = "SELECT * FROM `log` $syarat ORDER BY `timestamp` DESC;";
                    break;
                case 'timestamp_asc':
                    $sql_log = "SELECT * FROM `log` $syarat ORDER BY `timestamp` ASC;";
                    break;
                default:
                    $sql_log = "SELECT * FROM `log` $syarat ORDER BY `timestamp` DESC;";
            }
            $stmt_log = $con->prepare($sql_log);
            $stmt_log->execute();
            $res_log = $stmt_log->get_result();

            if ($res_log->num_rows > 0) {
                while ($row_log = $res_log->fetch_assoc()) {
                  $sql_admin = "SELECT * FROM `admin` WHERE `id` = ".$row_log['id_admin'].";";
                  $res_admin = mysqli_query($con, $sql_admin);
                  if (mysqli_num_rows($res_admin) > 0) {
                    $row_admin = mysqli_fetch_array($res_admin);
                    if ($row_log['id_table'] == 1) {
                      $sql_item = "SELECT * FROM `item` WHERE `id` = ".$row_log['id_item'].";";
                      $res_item = mysqli_query($con, $sql_item);
                      if (mysqli_num_rows($res_item) > 0) {
                        $row_item = mysqli_fetch_array($res_item);
                        $item_identity = "Item ID: " . $row_item['id'] . ", Item name: " . $row_item['item_name'];
                        if ($row_log['action'] == 1){
                          echo '<div class="alert alert-info" role="alert">
                                '.$row_admin['name'].' added new item `'.$item_identity.'` on '.$row_log['timestamp'].'
                                </div>';
                        }
                        else if ($row_log['action'] == 2){
                          echo '<div class="alert alert-danger" role="alert">
                                '.$row_admin['name'].' edited item `'.$item_identity.'` on '.$row_log['timestamp'].'. Edited data: '.$row_log['detail_action'].' 
                                </div>';
                        }
                        else if ($row_log['action'] == 3){
                          echo '<div class="alert alert-success" role="alert">
                                '.$row_admin['name'].' set item `'.$item_identity.'` as completed on '.$row_log['timestamp'].'. '.$row_log['detail_action'].'.
                                </div>';
                        }
                      }
                    }
                    else if ($row_log['id_table'] == 2) {
                      if ($row_log['action'] == 1){
                        echo '<div class="alert alert-info" role="alert">
                              '.$row_admin['name'].' added a new truck '.$row_log['detail_action'].' on '.$row_log['timestamp'].'
                              </div>';
                      }
                      else if ($row_log['action'] == 2){
                        echo '<div class="alert alert-danger" role="alert">
                                '.$row_admin['name'].' edited a truck`s detail(s) on '.$row_log['timestamp'].'. Edited data: '.$row_log['detail_action'].'
                              </div>';
                      }
                      else if ($row_log['action'] == 4){
                        echo '<div class="alert alert-warning" role="alert">
                                '.$row_admin['name'].' generated trucks for '.$row_log['detail_action'].' on '.$row_log['timestamp'].'
                              </div>';
                      }
                    }
                    else if ($row_log['id_table'] == 3) {
                      if ($row_log['action'] == 4){
                        echo '<div class="alert alert-warning" role="alert">
                                '.$row_admin['name'].' generated a new schedule for '.$row_log['detail_action'].' on '.$row_log['timestamp'].'
                              </div>';
                      }
                    }
                    else if ($row_log['id_table'] == 4) {
                      if ($row_log['action'] == 4){
                        echo '<div class="alert alert-warning" role="alert">
                                '.$row_admin['name'].' generated drivers for '.$row_log['detail_action'].'  on '.$row_log['timestamp'].'
                              </div>';
                      }
                      else if ($row_log['action'] == 5){
                        echo '<div class="alert alert-light" role="alert">
                                '.$row_admin['name'].' paid driver`s salary on '.$row_log['timestamp'].'. '.$row_log['detail_action'].'
                              </div>';
                      }
                    }
                    else if ($row_log['id_table'] == 5) {
                      if ($row_log['action'] == 1){
                        echo '<div class="alert alert-info" role="alert">
                              '.$row_admin['name'].' added a new admin '.$row_log['detail_action'].' on '.$row_log['timestamp'].'
                              </div>';
                      }
                      else if ($row_log['action'] == 2){
                        echo '<div class="alert alert-danger" role="alert">
                              '.$row_admin['name'].' edited profile on '.$row_log['timestamp'].'. Edited data: '.$row_log['detail_action'].'
                              </div>';
                      }
                      else if ($row_log['action'] == 6){
                        echo '<div class="alert alert-light" role="alert">
                                '.$row_admin['name'].' logged in on '.$row_log['timestamp'].'
                              </div>';
                      }
                    }
                    else if ($row_log['id_table'] == 6) {
                      $sql_item = "SELECT * FROM `item` WHERE `id` = ".$row_log['id_admin'].";";
                      $res_item = mysqli_query($con, $sql_item);
                      if (mysqli_num_rows($res_item) > 0) {
                        $row_item = mysqli_fetch_array($res_item);
                        if ($row_log['action'] == 1){
                          echo '<div class="alert alert-info" role="alert">
                                  '.$row_admin['name'].' added transaction for '.$row_log['detail_action'].' on '.$row_log['timestamp'].'
                                </div>';
                        }
                      }
                    }
                  }
                }
            } else {
              echo 'No Log';
            }

            // // Pagination links
            // echo '<nav aria-label="Page navigation">';
            // echo '<ul class="pagination">';
            // if ($current_page > 1) {
            //     echo '<li class="page-item"><a class="page-link" href="?page=' . ($current_page - 1) . '&' . http_build_query($_POST) . '&sort=' . ($_GET['sort'] ?? '') . '">Previous</a></li>';
            // }
            // for ($i = 1; $i <= $total_pages; $i++) {
            //     $active = ($i == $current_page) ? 'active' : '';
            //     echo '<li class="page-item ' . $active . '"><a class="page-link" href="?page=' . $i . '&' . http_build_query($_POST) . '&sort=' . ($_GET['sort'] ?? '') . '">' . $i . '</a></li>';
            // }
            // if ($current_page < $total_pages) {
            //     echo '<li class="page-item"><a class="page-link" href="?page=' . ($current_page + 1) . '&' . http_build_query($_POST) . '&sort=' . ($_GET['sort'] ?? '') . '">Next</a></li>';
            // }
            // echo '</ul>';
            // echo '</nav>';
            ?>
        </div>
        <script>
          document.querySelectorAll('#filterForm input, #filterForm select').forEach(input => {
            input.addEventListener('change', () => {
                document.getElementById('filterForm').submit();
            });
          });
          function clearFilter() {
            document.getElementById("id_table").selectedIndex = 0;
            document.getElementById("action").selectedIndex = 0;
            document.getElementById("start_date").value = "";
            document.getElementById("end_date").value = "";
            document.getElementById("filterForm").submit(); // Submit the form to apply changes
          }
          document.getElementById("clearFilterBtn").addEventListener("click", clearFilter);

          // Add click event listener to the Clear Filter button
    </script>
        <?php include('footer.php'); ?>
    </div>

</body>
</html>
