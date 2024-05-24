<?php
session_start();
require "connect.php";

$ongkir = [];
$maintenance = [];

$sql_grafik = "WITH months AS (
                SELECT '2024-01' AS month
                UNION ALL SELECT '2024-02'
                UNION ALL SELECT '2024-03'
                UNION ALL SELECT '2024-04'
                UNION ALL SELECT '2024-05'
                UNION ALL SELECT '2024-06'
                UNION ALL SELECT '2024-07'
                UNION ALL SELECT '2024-08'
                UNION ALL SELECT '2024-09'
                UNION ALL SELECT '2024-10'
                UNION ALL SELECT '2024-11'
                UNION ALL SELECT '2024-12'
              )
              SELECT 
                m.month,
                SUM(CASE WHEN id_item IS NOT NULL THEN t.nominal ELSE NULL END) AS ongkir,
                SUM(CASE WHEN id_truck IS NOT NULL THEN t.nominal ELSE NULL END) AS maintenance,
                SUM(CASE WHEN id_driver IS NOT NULL THEN t.nominal ELSE NULL END) AS gaji
              FROM 
                months m
              LEFT JOIN 
                transaction t
              ON 
                DATE_FORMAT(t.date_time, '%Y-%m') = m.month
              GROUP BY 
                m.month
              ORDER BY 
                m.month;";
$res_grafik = mysqli_query($con, $sql_grafik);
if (mysqli_num_rows($res_grafik) > 0) {
  while ($row_grafik = mysqli_fetch_array($res_grafik)) {
    $ongkir[] = $row_grafik['ongkir']; 
    $maintenance[] = $row_grafik['maintenance']; 
    $gaji[] = $row_grafik['gaji']; 
  }
}


$sql_items = "SELECT
                COUNT(CASE WHEN `status` = 0 THEN 1 END) AS status_0_count,
                COUNT(CASE WHEN `status` = 1 THEN 1 END) AS status_1_count,
                COUNT(CASE WHEN `status` = 2 THEN 1 END) AS status_2_count
              FROM `item`;";
$res_items = mysqli_query($con, $sql_items);
if (mysqli_num_rows($res_items) > 0) {
  $row_items = mysqli_fetch_assoc($res_items);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Dashboard</title>
</head>
<body>
    <?php
      include('navbar.php');
    ?>

  <div class="main-content">
    <div class="container">
      <form class="d-flex" action="search.php" method="POST">
        <button class="btn btn-dashboard" type="submit" name="search">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
        <div class="vertical-line"></div>
        <!-- <div class="dashboard-input"> -->
          <input type="text" class="form-control" id="id" name="id" placeholder="Cari nomor resi di sini..." required>
        <!-- </div> -->
      </form>
      <div class="horizontal-line"></div>

      <div class="d-flex justify-content-between">
        <div class="col-mb-6 p-2">
          <div class="title-dashboard">Welcome Budak Korporat,</div>
          <div class="title-form">Dashboard</div>
        </div>
        <div class="col-mb-6 p-2 status-container" onclick="window.location.href = 'schedule.php'">
            <div class="status-item">
              <div class="row">
                <div class="statusnya">Unscheduled <i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="title-form"><?php echo $row_items['status_0_count']; ?></div>
              </div>
            </div>
            <div class="vertical-line"></div>
            <div class="status-item">
              <div class="row">
                <div class="statusnya">On-Going <i class="fa-solid fa-truck-fast"></i></div>
                <div class="title-form"><?php echo $row_items['status_1_count']; ?></div>
              </div>
            </div>
            <div class="vertical-line"></div>
            <div class="status-item">
              <div class="row">
                <div class="statusnya">Completed <i class="fa-solid fa-square-check"></i></div>
                <div class="title-form"><?php echo $row_items['status_2_count']; ?></div>
              </div>  
            </div>
        </div>
      </div>

      <div class="col-md-12 d-flex">
        <div class="row">
          <!-- GRAFIK & ADD ITEM-->
          <div class="col-md-8">
            <div class="card mb-3">
              <div class="card-body d-flex justify-content-between">
                <div class="container">
                  <div class="row align-items-center">
                      <div class="col">
                          <div><b>Financial Flow</b></div>
                      </div>
                      <div class="col-auto">
                          <div class="title-dashboard4">
                              <i class="fa-solid fa-circle" style="color: #68bcef"></i> Pemasukan 
                              <i class="fa-solid fa-circle" style="color: #fe9597"></i> Maintenance
                              <i class="fa-solid fa-circle" style="color: #ae66ff"></i> Gaji
                          </div>
                      </div>
                  </div>
                  
                  <canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-body d-flex justify-content-between">
                <div class="p-2">
                  <b>Click to add a new item easily from your dashboard</b><br>
                  <div class="title-dashboard">This will redirect you to a new page.</div>
                </div>
                <div class="p-2">
                  <button class="btn btn-info" onclick="redirectToNewPage()">Add Item</button>
                </div>
              </div>
            </div>

          </div>
          <!-- INFO TRUK -->
          <div class="col-md-4">
            <div class="card">
              <div class="card-body d-flex justify-content-between">
                <div class="row align-items-center" style="display: flex; flex-wrap: wrap; margin-right: -15px; margin-left: -15px;">
                  <div class="col-md-12">
                    <div style="padding-bottom: 10px;"><b>On Delivery</b></div>
                  </div>
                  <div class="col-md-12">
                      <?php 
                        $query_id_truk = "SELECT DISTINCT `truck`.`id`, `truck`.`unique_number` FROM `truck` 
                                                JOIN `truck_driver` ON `truck`.`id` = `truck_driver`.`id_truck` 
                                                JOIN `schedule` ON `schedule`.`id_schedule` = `truck_driver`.`id` 
                                                WHERE `schedule`.`status` = 1;";
                        $res_id_truk = mysqli_query($con, $query_id_truk);
                        $count = 1;
                        if (mysqli_num_rows($res_id_truk) > 0) {
                          echo '<div class="accordion" id="accordionExample">';
                          while ($row_id_truk = mysqli_fetch_array($res_id_truk)) {
                            // echo $count;
                            echo '<div class="accordion-item">
                                    <h2 class="accordion-header custom-accordion-header">
                                      <button class="accordion-button '; 
                                      if ($count != 1){
                                        echo 'collapsed';
                                      }           
                                      echo ' " type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne'.$row_id_truk['id'].'" ';
                                      if ($count == 1){
                                        echo 'aria-expanded="true"';
                                        // $count = $count + 1;
                                      } else {
                                        echo 'aria-expanded="false"';
                                      }
                                      echo ' aria-controls="collapseOne'.$row_id_truk['id'].'">
                                        '.$row_id_truk['unique_number'].'
                                      </button>
                                      
                                      </h2>
                                    <div id="collapseOne'.$row_id_truk['id'].'" class="accordion-collapse collapse '; 
                                    
                                    if ($count == 1){
                                      echo 'show';
                                      $count = $count + 1;
                                    } 
                                    echo'" data-bs-parent="#accordionExample">
                                      <div class="accordion-body" style="min-width: 100% !important; width: 100% !important;">
                                        <div class="container">
                                            <ul class="list-group list-group-flush">';
                                        $query_items = "SELECT `schedule`.`id_barang`, `item`.`item_name`, `item`.`order_received` FROM `truck` JOIN `truck_driver` ON `truck`.`id` = `truck_driver`.`id_truck`
                                        JOIN `schedule` ON `schedule`.`id_schedule` = `truck_driver`.`id` 
                                                  JOIN `item` ON `schedule`.`id_barang` = `item`.id
                                                  WHERE `schedule`.`status` = 1 AND `truck`.`id` = ".$row_id_truk['id']."
                                                  ORDER BY `schedule`.`id`;";
                                        $res_items = mysqli_query($con, $query_items);
                                        if (mysqli_num_rows($res_items) > 0) {
                                          while ($row_items = mysqli_fetch_array($res_items)) {
                                            echo '<li class="list-group-item">
                                                      ORDER ID: '.$row_items['id_barang'].'<br>
                                                      '.$row_items['item_name'].'
                                                      <div class="title-dashboard4">'.$row_items['order_received'].'</div> 
                                                  </li>';
                                                }
                                        }
                                      echo '</ul>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                              ';
                          }
                          echo ' </div>';
                        } else{
                          echo '<div class="title-dashboard4">There are no trucks making deliveries.</div>';
                        }

                      ?>
                    
                  </div>
                </div>
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>

      <script>
        function redirectToNewPage() {
          // Redirect to the new page when the "Add" button is clicked
            window.location.href = "add_item.php";
        }
        /* globals Chart:false */

        (() => {
  'use strict';

  const ongkir = <?php echo json_encode($ongkir); ?>;
  const maintenance = <?php echo json_encode($maintenance); ?>;
  const gaji = <?php echo json_encode($gaji); ?>;

  // Graphs
  const ctx = document.getElementById('myChart');
  // eslint-disable-next-line no-unused-vars
  const myChart = new Chart(ctx, {
    type: 'bar', // Change chart type to bar chart
    data: {
      labels: [
        'JAN',
        'FEB',
        'MAR',
        'APR',
        'MAY',
        'JUN',
        'JUL',
        'AUG',
        'SEP',
        'OCT',
        'NOV',
        'DEC'
      ],
      datasets: [{
        label: 'Pemasukan', // Label for the first dataset
        data: ongkir,
        backgroundColor: '#68bcef', // Change bar color for income
        barPercentage: 0.75, // Adjust bar width (0.75 means 50% of the available space)
        categoryPercentage: 0.75, // Adjust space between bars (0.75 means 50% of the available space)
      }, {
        label: 'Pengeluaran (Maintenance)', // Label for the second dataset
        data: maintenance,
        backgroundColor: '#fe9597', // Change bar color for income
        barPercentage: 0.75, // Adjust bar width (0.75 means 50% of the available space)
        categoryPercentage: 0.75, // Adjust space between bars (0.75 means 50% of the available space)
      }, {
        label: 'Pengeluaran (Gaji)', // Label for the second dataset
        data: gaji,
        backgroundColor: '#ae66ff', // Change bar color for income
        barPercentage: 0.75, // Adjust bar width (0.75 means 50% of the available space)
        categoryPercentage: 0.75, // Adjust space between bars (0.75 means 50% of the available space)
      }]
    },
    options: {
      scales: {
        x: {
          display: true, // Display x-axis
          grid: {
          display: false, // Hide vertical grid lines
        },
        ticks: {
          color: '#adb5bd', // Change color of the labels
        },
      },
      y: {
          display: false, // Hide y-axis labels
          grid: {
            display: true, // Display horizontal grid lines
          },
        },
      },
      plugins: {
        legend: {
          display: false, // Hide legend
        },
        tooltip: {
          boxPadding: 3,
        },
      },
      layout: {
        padding: {
          left: 0, // Reduce left padding to make bars slimmer
          right: 0, // Reduce right padding to make bars slimmer
          top: 0,
          bottom: 0,
        },
      },
      elements: {
        bar: {
          borderWidth: 1, // Adjust bar border width
          borderRadius: 1, // Adjust bar border radius
        },
      },
    },
  });
})();



      </script>
    <?php
      include('footer.php');
    ?>
  </div>


</body>
</html>