<?php
session_start();
require "connect.php";

$hostname = "localhost";
$username = "root";
$password = "";
$database_name = "logistics_company";

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $database_name: " . $e->getMessage());
}

function getWeeksInMonth($year, $month) {
  $firstDay = strtotime("$year-$month-01");
  $lastDay = strtotime("last day of", $firstDay);
  $weeks = [];

  $startOfWeek = $firstDay;
  while ($startOfWeek <= $lastDay) {
      $endOfWeek = strtotime("+6 days", $startOfWeek);
      if ($endOfWeek > $lastDay) {
          $endOfWeek = $lastDay;
      }
      $weeks[] = ['start' => date('Y-m-d', $startOfWeek), 'end' => date('Y-m-d', $endOfWeek)];
      $startOfWeek = strtotime("+1 day", $endOfWeek);
  }
  return $weeks;
}

function getPendapatan($pdo, $start, $end) {
  $stmt = $pdo->prepare('SELECT SUM(nominal) as pendapatan_kotor FROM transaction WHERE date_time BETWEEN :start AND :end AND status = 1');
  $stmt->execute(['start' => $start, 'end' => $end]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPengeluaran($pdo, $start, $end) {
  $stmt = $pdo->prepare('SELECT SUM(nominal) as pengeluaran FROM transaction WHERE date_time BETWEEN :start AND :end AND status = 2');
  $stmt->execute(['start' => $start, 'end' => $end]);
  return $stmt->fetch(PDO::FETCH_ASSOC);
}

$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');

if (isset($_GET['ajax'])) {
  $weeks = getWeeksInMonth($year, $month);
  $response = [];

  foreach ($weeks as $week) {
      $pendapatan_kotor = getPendapatan($pdo, $week['start'], $week['end']);
      $pengeluaran = getPengeluaran($pdo, $week['start'], $week['end']);
      $response[] = [
          'week' => [
              'start' => $week['start'],
              'end' => $week['end']
          ],
          'pendapatan_kotor' => $pendapatan_kotor['pendapatan_kotor'],
          'pengeluaran' => $pengeluaran['pengeluaran']
      ];
  }

  header('Content-Type: application/json');
  echo json_encode($response);
  exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <?php include('head.php'); ?>
    <title>TIP LOGISTICS | Transaction</title>
</head>
<link rel="stylesheet" href="assets/tesTrans.css">

<body>
    <?php
      include('navbar.php');
    ?>

    <div class="main-content">
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link" href="#weekly">Weekly</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#monthly">Monthly</a>
        </li>
      </ul>

      <div class="tab-content">
        <div class="tab-pane fade" id="weekly">
          <div class="container">
            <div class="header">
            <button class="nav-arrow" id="prev-month"><i class="fa-solid fa-angle-left"></i></button>
            <span id="current-month"><?= date('F Y', strtotime("$year-$month-01")) ?></span>
            <button class="nav-arrow" id="next-month"><i class="fa-solid fa-angle-right"></i></button>
          </div>
        <div id="buttons-container" class="buttons-container"></div>
        <div class="content" id="weeks-content">
            <!-- Content will be dynamically updated -->
        </div>
      </div>
      <div class="container">
        <!-- Your existing container div... -->

        <!-- Add a div to hold the transaction table -->
        <div id="transaction-table"></div>
      </div>

        
      <div class="tab-pane fade" id="monthly">
        <div class="year-header">
          <button id="prev-year">&lt;</button>
          <span id="selected-year"><?= $year ?></span>
          <button id="next-year">&gt;</button>
        </div>
        <!-- Calendar Popup -->
        <div id="calendar-popup" class="calendar-popup" style="display: none;">
            <div id="calendar"></div>
        </div>
      </div>
      
      <script>
        document.addEventListener('DOMContentLoaded', function() {
            var currentYear = <?= $year ?>;
            var currentMonth = <?= $month ?>;
            var previousHeaderContent = '';
            var isDetailView = false;

            function fetchData() {
                fetch(`transaction.php?ajax=1&year=${currentYear}&month=${currentMonth}`)
                    .then(response => response.json())
                    .then(data => {
                        var weeksContent = document.getElementById('weeks-content');
                        weeksContent.innerHTML = '';
                        data.forEach((weekData, index) => {
                            var pendapatanKotor = weekData.pendapatan_kotor ? parseInt(weekData.pendapatan_kotor).toLocaleString('id-ID') : '0';
                            var pengeluaran = weekData.pengeluaran ? parseInt(weekData.pengeluaran).toLocaleString('id-ID') : '0';

                            var weekHtml = `
                                <div class="week" id="week${index + 1}">
                                    <div class="week-header">
                                        <span>${new Date(weekData.week.start).getDate()} - ${new Date(weekData.week.end).getDate()} ${new Date(weekData.week.end).toLocaleString('default', { month: 'short' })}</span>
                                        <button class="dropdown-button"><i class="fa-solid fa-caret-down"></i></button>
                                    </div>
                                    <div class="week-details">
                                        <p><span>Pendapatan kotor:</span><span>Rp ${pendapatanKotor}</span></p>
                                        <p><span>Pengeluaran:</span><span>Rp ${pengeluaran}</span></p>
                                        <a href="#" class="detail-link" data-start="${weekData.week.start}" data-end="${weekData.week.end}">Detail</a>
                                    </div>
                                </div>`;
                            weeksContent.innerHTML += weekHtml;
                        });
                        document.getElementById('current-month').textContent = new Date(currentYear, currentMonth - 1).toLocaleString('default', { month: 'long', year: 'numeric' });
                        attachEventListeners();
                    });
            }

            function updateMonth() {
                fetchData();
            }

            function attachEventListeners() {
                var dropdownButtons = document.querySelectorAll('.dropdown-button');
                dropdownButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        var dropdownContent = this.parentElement.nextElementSibling;
                        dropdownContent.classList.toggle('show');
                        this.classList.toggle('rotate');
                    });
                });

                var detailLinks = document.querySelectorAll('.detail-link');
                detailLinks.forEach(function(link) {
                    link.addEventListener('click', function(event) {
                        event.preventDefault();
                        var startDate = this.getAttribute('data-start');
                        var endDate = this.getAttribute('data-end');
                        showDetailView(startDate, endDate);
                    });
                });
            }

            function showDetailView(startDate, endDate) {
                if (!isDetailView) {
                    previousHeaderContent = document.querySelector('.header').innerHTML;
                    document.querySelector('.header').innerHTML = `
                        <button class="buttons-container" id="back-button">Back</button>
                        <span id="current-week">${new Date(startDate).getDate()} - ${new Date(endDate).getDate()} ${new Date(endDate).toLocaleString('default', { month: 'long' })} ${new Date(endDate).getFullYear()}</span>`;
                    document.getElementById('weeks-content').innerHTML = '';
                    createDateButtons(startDate, endDate);
                    attachBackButtonListener();
                    isDetailView = true;
                }
            }

            function createDateButtons(startDate, endDate) {
                var buttonsContainer = document.getElementById('buttons-container');
                buttonsContainer.innerHTML = '';
                var start = new Date(startDate);
                var end = new Date(endDate);

                for (var d = start; d <= end; d.setDate(d.getDate() + 1)) {
                    var button = document.createElement('button');
                    button.innerText = d.getDate();
                    buttonsContainer.appendChild(button);
                }
            }

            function attachBackButtonListener() {
                document.getElementById('back-button').addEventListener('click', function() {
                    document.querySelector('.header').innerHTML = previousHeaderContent;
                    document.getElementById('buttons-container').innerHTML = '';
                    isDetailView = false;
                    fetchData();
                });
            }

            function attachDateButtonListeners() {
                var dateButtons = document.querySelectorAll('.date-button');
                dateButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        var date = button.innerText;
                        var startDate = new Date(currentYear, currentMonth - 1, date).toISOString().split('T')[0];
                        var endDate = new Date(currentYear, currentMonth - 1, date).toISOString().split('T')[0];
                        fetchTransactionData(startDate, endDate);
                    });
                });
            }

            function fetchTransactionData(startDate, endDate) {
                fetch(`fetch.php?start=${startDate}&end=${endDate}`)
                    .then(response => response.json())
                    .then(data => {
                        populateTransactionTable(data);
                    })
                    .catch(error => {
                        console.error('Error fetching transaction data:', error);
                    });
            }

            function populateTransactionTable(data) {
                var table = `
                    <table>
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th>Pendapatan</th>
                                <th>Pengeluaran</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                data.forEach(function(transaction) {
                    table += `
                        <tr>
                            <td>${transaction.kategori}</td>
                            <td>${transaction.keterangan}</td>
                            <td>${transaction.pendapatan}</td>
                            <td>${transaction.pengeluaran}</td>
                        </tr>
                    `;
                });
                table += `
                        </tbody>
                    </table>
                `;
                document.getElementById('transaction-table').innerHTML = table;
            }

            document.getElementById('prev-month').addEventListener('click', function() {
                if (currentMonth === 1) {
                    currentMonth = 12;
                    currentYear--;
                } else {
                    currentMonth--;
                }
                updateMonth();
            });

            document.getElementById('next-month').addEventListener('click', function() {
                if (currentMonth === 12) {
                    currentMonth = 1;
                    currentYear++;
                } else {
                    currentMonth++;
                }
                updateMonth();
            });

            document.getElementById('prev-year').addEventListener('click', function() {
                currentYear--;
                updateMonth();
            });

            document.getElementById('next-year').addEventListener('click', function() {
                currentYear++;
                updateMonth();
            });

            // Calendar Popup
            document.getElementById('selected-year').addEventListener('click', function() {
                var calendarPopup = document.getElementById('calendar-popup');
                if (calendarPopup.style.display === 'none') {
                    calendarPopup.style.display = 'block';
                    renderCalendar(currentYear);
                } else {
                    calendarPopup.style.display = 'none';
                }
            });

            // Calendar Selection
            function renderCalendar(year) {
                var calendarContainer = document.getElementById('calendar');
                var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                var calendarHTML = '<div class="year-selector"><select id="year-selector">';
                for (var i = year - 10; i <= year + 10; i++) {
                    calendarHTML += `<option value="${i}" ${i === year ? 'selected' : ''}>${i}</option>`;
                }
                calendarHTML += '</select></div>';
                calendarHTML += '<table><thead><tr><th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>May</th><th>Jun</th><th>Jul</th><th>Aug</th><th>Sep</th><th>Oct</th><th>Nov</th><th>Dec</th></tr></thead><tbody>';

                for (var i = 0; i < 4; i++) {
                    calendarHTML += '<tr>';
                    for (var j = 0; j < 3; j++) {
                        var monthIndex = i * 3 + j;
                        var monthName = months[monthIndex];
                        calendarHTML += `<td data-month="${monthIndex + 1}" data-year="${year}">${monthName}</td>`;
                    }
                    calendarHTML += '</tr>';
                }
                calendarHTML += '</tbody></table>';

                calendarContainer.innerHTML = calendarHTML;

                // Event listeners for each month
                var monthCells = document.querySelectorAll('#calendar td');
                monthCells.forEach(function(cell) {
                    cell.addEventListener('click', function() {
                        var selectedMonth = this.dataset.month;
                        var selectedYear = this.dataset.year;
                        currentMonth = selectedMonth;
                        currentYear = selectedYear;
                        document.getElementById('selected-year').innerText = currentYear;
                        document.getElementById('calendar-popup').style.display = 'none';
                        updateMonth();
                    });
                });

                // Event listener for year selection
                document.getElementById('year-selector').addEventListener('change', function() {
                    var selectedYear = parseInt(this.value);
                    renderCalendar(selectedYear);
                });
            }

            // Initial calendar render
             renderCalendar(currentYear);

            // Initial fetch
            fetchData();

            // Polling every 10 seconds (for example)
            setInterval(fetchData, 10000);

            attachDateButtonListeners();
        });
        
        // function toggleCheckbox(checkbox) {
        //     var checkboxes = document.getElementsByName('selected-items[]');
        //     checkboxes.forEach(function(item) {
        //         item.checked = checkbox.checked;
        //     });
        // }

        // function generateSchedule() {
        //     var selectedItems = [];
        //     var checkboxes = document.getElementsByName('selected-items[]');
        //     checkboxes.forEach(function(checkbox) {
        //         if (checkbox.checked) {
        //             selectedItems.push(checkbox.value);
        //         }
        //     });

        //     Perform further action with selectedItems array, e.g., generate schedule
        //     console.log(selectedItems); // Placeholder action for demonstration
        // }
          // JavaScript to handle click event on product details button
        document.addEventListener('DOMContentLoaded', function() {
          const productDetailsButtons = document.querySelectorAll('.product-details-btn');
          const modalBody = document.getElementById('productDetailsModalBody');

          productDetailsButtons.forEach(button => {
            button.addEventListener('click', function() {
              // Get the product ID from the data attribute
              const productId = this.getAttribute('data-product-id');
              // Show the modal
              const modal = new bootstrap.Modal(document.getElementById('productDetailsModal' + productId));
              modal.show();
            });
          });
        });
        $(document).ready(function() {
          $('#table-unscheduled').DataTable({
              "pageLength": 10,
              "autoWidth": true,
              "dom": '<"c8tableTools01"lfB><"c8tableBody"t><"c8tableTools02"ipr>'
          });
          $('#table-completed').DataTable({
              "pageLength": 10,
              "autoWidth": true,
              "dom": '<"c8tableTools01"lfB><"c8tableBody"t><"c8tableTools02"ipr>'
          });
          $('#table-on-going').DataTable({
              "pageLength": 10,
              "autoWidth": true,
              "dom": '<"c8tableTools01"lfB><"c8tableBody"t><"c8tableTools02"ipr>'
          });
          // Handle tab click event
          $('.nav-link').click(function(e) {
            e.preventDefault(); // Prevent default anchor behavior
            $('.nav-link').removeClass('active'); // Remove 'active' class from all tab links
            $(this).addClass('active'); // Add 'active' class to the clicked tab link
            
            var targetTab = $(this).attr('href'); // Get the target tab ID from the 'href' attribute
            $('.tab-pane').removeClass('show active'); // Hide all tab content
            $(targetTab).addClass('show active'); // Show the content of the target tab
          });

          // Check if the URL contains a tab parameter
          var urlParams = new URLSearchParams(window.location.search);
          var tabParam = urlParams.get('tab');

          // If the tab parameter is not explicitly set, add 'active' class to the "On Going" tab and tab pane
          if (!tabParam) {
            $('a[href="#weekly"]').addClass('active');
            $('#weekly').addClass('show active');
          }
        });

      </script>

      <?php
        include('footer.php');
      ?>
    </div>


</body>
</html>