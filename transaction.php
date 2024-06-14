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
    header('Content-Type: application/json');
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

    echo json_encode($response);
    exit;
}

// ambil data per bulan
function getMonthlyData($month, $year, $pdo) {
    $data = [];

    // Query untuk pendapatan kotor
    $stmt = $pdo->prepare("SELECT SUM(nominal) as total FROM transaction WHERE status = 1 AND MONTH(date_time) = :month AND YEAR(date_time) = :year");
    $stmt->execute(['month' => $month, 'year' => $year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['gross_income'] = $row['total'] ?? 0;

   // Query untuk bensin
   $stmt = $pdo->prepare("SELECT SUM(nominal) as total FROM transaction WHERE status = 2 AND id_truck IS NOT NULL AND nominal != 1000000 AND MONTH(date_time) = :month AND YEAR(date_time) = :year");
   $stmt->execute(['month' => $month, 'year' => $year]);
   $row = $stmt->fetch(PDO::FETCH_ASSOC);
   $data['gas'] = $row['total'] ?? 0;

    // Query untuk gaji
    $stmt = $pdo->prepare("SELECT SUM(nominal) as total FROM transaction WHERE status = 2 AND id_driver IS NOT NULL AND MONTH(date_time) = :month AND YEAR(date_time) = :year");
    $stmt->execute(['month' => $month, 'year' => $year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['salary'] = $row['total'] ?? 0;

   // Query untuk maintenance
    $stmt = $pdo->prepare("SELECT SUM(nominal) as total FROM transaction WHERE status = 2 AND id_truck IS NOT NULL AND nominal = 1000000 AND MONTH(date_time) = :month AND YEAR(date_time) = :year");
    $stmt->execute(['month' => $month, 'year' => $year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['maintenance'] = $row['total'] ?? 0;

    // Hitung pendapatan bersih
    $data['net_income'] = $data['gross_income'] - ($data['salary'] + $data['maintenance'] + $data['gas']);

    return $data;
}

// Ambil data tahunan
function getYearlyData($year, $pdo) {
    $yearly_data = [];
    for ($month = 1; $month <= 12; $month++) {
        $yearly_data[$month] = getMonthlyData($month, $year, $pdo);
    }
    return $yearly_data;
}

$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$yearly_data = getYearlyData($selectedYear, $pdo);

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    $year = $_GET['year'];
    $yearly_data = getYearlyData($year, $pdo);
    header('Content-Type: application/json');
    echo json_encode($yearly_data);
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
                <div class="detail-view" id="detail-view" style="display:none;">
                    <button id="back-button">Back</button>
                    <div class="date-range">
                        <span id="date-range-text"></span>
                    </div>
                    <div class="date-buttons" id="date-buttons">
                        <!-- Date buttons will be dynamically generated here -->
                    </div>
                    <div class="transaction-container" id="transaction-container" style="display:none;">
                        <div id="transaction-table">
                            <table id="transaction-data">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Keterangan</th>
                                        <th>Pendapatan</th>
                                        <th>Pengeluaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Isi tabel transaksi akan ditampilkan di sini -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>  
            </div>
            <div class="tab-pane fade" id="monthly">
                <div class="year-header">
                <button id="prev-year">&lt;</button>
                <span id="selected-year"><?= $year ?></span>
                <button id="next-year">&gt;</button>
                </div>
                <div id="calendar-popup" class="calendar-popup">
                
                </div>
                <table>
                    <thead>
                        <tr>
                            <th rowspan="2">Bulan</th>
                            <th rowspan="2">Pendapatan Kotor</th>
                            <th colspan="3">Pengeluaran</th>
                            <th rowspan="2">Pendapatan Bersih</th>
                        </tr>
                        <tr>
                            <th>Bensin</th>
                            <th>Gaji</th>
                            <th>Maintenance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($yearly_data as $month => $data): ?>
                            <tr>
                                <td><?= date('F', mktime(0, 0, 0, $month, 1)) ?></td>
                                <td><?= number_format($data['gross_income'], 2) ?></td>
                                <td><?= number_format($data['gas'], 2) ?></td>
                                <td><?= number_format($data['salary'], 2) ?></td>
                                <td><?= number_format($data['maintenance'], 2) ?></td>
                                <td><?= number_format($data['net_income'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><strong><?= number_format(array_sum(array_column($yearly_data, 'gross_income')), 2) ?></strong></td>
                            <td><strong><?= number_format(array_sum(array_column($yearly_data, 'gas')), 2) ?></strong></td>
                            <td><strong><?= number_format(array_sum(array_column($yearly_data, 'salary')), 2) ?></strong></td>
                            <td><strong><?= number_format(array_sum(array_column($yearly_data, 'maintenance')), 2) ?></strong></td>
                            <td><strong><?= number_format(array_sum(array_column($yearly_data, 'net_income')), 2) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div> 
        </div>
    </div>
      
      <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetchData();
            const now = new Date();
            // BULAN
            var currentYear = now.getFullYear();
            var currentMonth = now.getMonth() + 1;
            var previousHeaderContent = '';
            var isDetailView = false;

            let totalIncome = 0;
            let totalExpense = 0;
           
            // Mengambil data dari server
            function fetchData() {
                fetch(`transaction.php?ajax=1&year=${currentYear}&month=${currentMonth}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Data received:', data);  // Tambahkan ini untuk debug

                        var weeksContent = document.getElementById('weeks-content');
                        weeksContent.innerHTML = '';
                        data.forEach((weekData, index) => {
                            var pendapatanKotor = weekData.pendapatan_kotor ? parseInt(weekData.pendapatan_kotor).toLocaleString('id-ID') : '0';
                            var pengeluaran = weekData.pengeluaran ? parseInt(weekData.pengeluaran).toLocaleString('id-ID') : '0';

                            console.log(`Week ${index + 1}: Pendapatan Kotor = ${pendapatanKotor}, Pengeluaran = ${pengeluaran}`); // Tambahkan log ini untuk memastikan perhitungan benar
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
                        // var currentMonthElement = document.getElementById('current-month');         
                        // currentMonthElement.textContent = new Date(currentYear, currentMonth - 1).toLocaleString('default', { month: 'long', year: 'numeric' });
                        document.getElementById('current-month').textContent = new Date(currentYear, currentMonth - 1).toLocaleString('default', { month: 'long', year: 'numeric' });
                        attachEventListeners();
                        attachBackButtonListener();
                    });
            }
            // Mengupdate data bulan saat ini
            function updateMonth() {
                fetchData();
            }

            // Melampirkan event listener untuk dropdown dan detail link
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
                attachDateButtonListeners();
            }

             // Menampilkan tampilan detail untuk minggu yang dipilih
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

            // Menampilkan tabel transaksi
            var transactionContainer = document.getElementById('transaction-container');
            var table = document.getElementById('transaction-data');
            var tbody = table.querySelector('tbody');

            function showTransactionTable(data) {
                // console.log(data);
                // console.log("Displaying data in table:", transformedData);

                tbody.innerHTML = ''; // Kosongkan isi tabel sebelum menambahkan data baru
                // console.log(table)

                // Tambahkan data transaksi ke dalam tabel
                data.forEach(transaction => {
                    console.log(transaction);
                    var row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${transaction.kategori}</td>
                        <td>${transaction.keterangan}</td>
                        <td>${transaction.pendapatan}</td>
                        <td>${transaction.pengeluaran}</td>
                    `;
                    tbody.appendChild(row);
                    console.log(row);

                    totalIncome += parseFloat(transaction.pendapatan) || 0;
                    totalExpense += parseFloat(transaction.pengeluaran) || 0;
                });

                // Menambahkan baris total
                const totalRow = document.createElement('tr');
                totalRow.innerHTML = `
                    <td><strong>Total</strong></td>
                    <td></td>
                    <td>${totalIncome.toFixed(2)}</td>
                    <td>${totalExpense.toFixed(2)}</td>
                `;
                tbody.appendChild(totalRow);
                console.log(tbody);
                // Tampilkan div yang berisi tabel transaksi
                // console.log(transactionContainer);
                transactionContainer.style.display = 'block';
                console.table(data);
                attachBackButtonListener();
                
            }

            // Setelah mendapatkan data transaksi dari server
            function fetchTransactionData(date) {
                // fetch(`harian.php?ajax=1&date=${date}`)
                //     .then(response => response.json())
                //     .then(data => {
                //         console.log("Data transaksi:", data);
                //         var transformedData = data.map(transaction => ({
                //             kategori: transaction.kategori,
                //             keterangan: transaction.keterangan,
                //             pendapatan: transaction.pendapatan,
                //             pengeluaran: transaction.pengeluaran
                //     }));
                //     // Menampilkan tabel transaksi
                //     console.log(transformedData);
                //     showTransactionTable(transformedData);
                // });
                fetch(`harian.php?ajax=1&date=${date}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Data transaksi:", data);
                    if (Array.isArray(data)) {
                        var transformedData = data.map(transaction => ({
                            kategori: transaction.kategori,
                            keterangan: transaction.keterangan,
                            pendapatan: transaction.pendapatan,
                            pengeluaran: transaction.pengeluaran
                        }));
                        // Menampilkan tabel transaksi

                        console.log(transformedData);
                        showTransactionTable(transformedData);

                        document.getElementById('detail-view').style.display = 'block';
                        document.getElementById('transaction-container').style.display = 'block';
                        document.querySelector('.container').style.display = 'none';
                    } else {
                        console.error("Unexpected data format:", data);
                    }
                })
                .catch(error => {
                    console.error('Error fetching transaction data:', error);
                });
                 
            }

             // Membuat tombol-tombol tanggal untuk minggu yang dipilih
            function createDateButtons(startDate, endDate) {
                var buttonsContainer = document.getElementById('buttons-container');
                buttonsContainer.innerHTML = '';
                var start = new Date(startDate);
                var end = new Date(endDate);

                for (var d = start; d <= end; d.setDate(d.getDate() + 1)) {
                    var button = document.createElement('button');
                    button.innerText = d.getDate();
                    button.classList.add('date-button');
                    button.setAttribute('data-date', d.toISOString().split('T')[0]); // Adding data-date attribute
                    buttonsContainer.appendChild(button);

                    button.addEventListener('click', function() {
                        // Show the transaction table
                        var selectedDate = this.getAttribute('data-date');
                        console.log(selectedDate)
                        fetchTransactionData(selectedDate);
                        // Update the table content here if needed
                        // For now, it just displays the example row
                    });
                }
                // const buttons = document.querySelectorAll(".date-button");
                // buttons.forEach(button => {
                //     button.addEventListener("click", function() {
                //         const date = this.getAttribute("data-date");
                //         fetchTransactionData(date);
                //     });
                // });
            }
             // Melampirkan event listener untuk tombol back
            function attachBackButtonListener() {
                document.getElementById('back-button').addEventListener('click', function() {
                    // if(transactionContainer.style.display == 'none'){
                    //     document.querySelector('.header').innerHTML = previousHeaderContent;
                    //     document.getElementById('buttons-container').innerHTML = '';
                    //     document.getElementById('detail-view').style.display = 'none';
                    //     document.querySelector('.container').style.display = 'block';
                    //     isDetailView = false;
                    // }
                    // else{
                    //     transactionContainer.style.display = 'none';
                    //     document.getElementById('detail-view').style.display = 'block';
                    // }

                    document.querySelector('.header').innerHTML = previousHeaderContent;
                        document.getElementById('buttons-container').innerHTML = '';
                        document.getElementById('detail-view').style.display = 'none';
                        document.querySelector('.container').style.display = 'block';
                        isDetailView = false;

                    fetchData();
                    attachNavigationEventListeners();
                });
            }

        

            // Menambahkan event listeners untuk tombol navigasi bulan dan tahun
            function attachNavigationEventListeners() {
                // Event listener untuk tombol navigasi bulan sebelumnya
                document.getElementById('prev-month').addEventListener('click', function() {
                    if (currentMonth === 1) {
                        currentMonth = 12;
                        currentYear--;
                    } else {
                        currentMonth--;
                    }
                    updateMonth();
                    console.log("bulan"+currentMonth);
                });

                // Event listener untuk tombol navigasi bulan berikutnya
                document.getElementById('next-month').addEventListener('click', function() {
                    if (currentMonth === 12) {
                        currentMonth = 1;
                        currentYear++;
                    } else {
                        currentMonth++;
                    }
                    updateMonth();
                    console.log("bulan"+currentMonth);
                    
                });
            }

            // Melampirkan event listener untuk tombol tanggal
            function attachDateButtonListeners() {
                var dateButtons = document.querySelectorAll('.date-button');
                dateButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        var date = button.innerText;
                        var selectedDate = new Date(currentYear, currentMonth - 1, date+1).toISOString().split('T')[0];
                        // var okElement = document.createElement('div');
                        // okElement.textContent = 'ok';
                        // document.body.appendChild(okElement);
                        console.log(selectedDate);

                        fetchTransactionData(selectedDate);
                    });
                });
                
            }

    
            // Event listener untuk tombol navigasi bulan sebelumnya
            document.getElementById('prev-month').addEventListener('click', function() {
                if (currentMonth === 1) {
                    currentMonth = 12;
                    currentYear--;
                } else {
                    currentMonth--;
                }
                updateMonth();
                console.log("bulan"+currentMonth);
            });

            // Event listener untuk tombol navigasi bulan selanjutnya
            document.getElementById('next-month').addEventListener('click', function() {
                if (currentMonth === 12) {
                    currentMonth = 1;
                    currentYear++;
                } else {
                    currentMonth++;
                }
                updateMonth();
                console.log("bulan"+currentMonth);
            });

             //-----------------TAHUN-----------------------
             
            displaySelectedYear(currentYear);

            const calendarPopup = document.getElementById('calendar-popup');
            const selectedYearElement = document.getElementById('selected-year');
            let initialYear = new Date().getFullYear();

            function fetchDataYear() {
                fetch(`tahunan.php?ajax=1&year=${currentYear}&_=${new Date().getTime()}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Data received:', data);  // Log data yang diterima
                    updateTable(data);
                })
                .catch(error => console.error('Error:', error));
            }

            function updateTable(data) {
                const tableBody = document.querySelector('#monthly tbody');
                tableBody.innerHTML = '';

                const months = [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];

                let totalGrossIncome = 0;
                let totalSalary = 0;
                let totalGas = 0;
                let totalMaintenance = 0;
                let totalNetIncome = 0;

                for (let month = 1; month <= 12; month++) {
                    const monthData = data[month] || { gross_income: 0, gas:0, salary: 0, maintenance: 0, net_income: 0 };

                    totalGrossIncome += parseFloat(monthData.gross_income) || 0;
                    totalGas += parseFloat(monthData.gas) || 0;
                    totalSalary += parseFloat(monthData.salary) || 0;
                    totalMaintenance += parseFloat(monthData.maintenance) || 0;
                    totalNetIncome += parseFloat(monthData.net_income) || 0;

                    const row = document.createElement('tr');

                    const monthCell = document.createElement('td');
                    monthCell.textContent = months[month - 1];
                    row.appendChild(monthCell);

                    const grossIncomeCell = document.createElement('td');
                    grossIncomeCell.textContent = (parseFloat(monthData.gross_income) || 0).toFixed(2);
                    row.appendChild(grossIncomeCell);

                    const gasCell = document.createElement('td');
                    gasCell.textContent = (parseFloat(monthData.gas) || 0).toFixed(2);
                    row.appendChild(gasCell);

                    const salaryCell = document.createElement('td');
                    salaryCell.textContent = (parseFloat(monthData.salary) || 0).toFixed(2);
                    row.appendChild(salaryCell);

                    const maintenanceCell = document.createElement('td');
                    maintenanceCell.textContent = (parseFloat(monthData.maintenance) || 0).toFixed(2);
                    row.appendChild(maintenanceCell);

                    const netIncomeCell = document.createElement('td');
                    const netIncome = parseFloat(monthData.gross_income) - (parseFloat(monthData.salary) + parseFloat(monthData.maintenance));
                    netIncomeCell.textContent = (netIncome || 0).toFixed(2);
                    row.appendChild(netIncomeCell);

                    tableBody.appendChild(row);
                }

                const totalRow = document.createElement('tr');
                totalRow.innerHTML = `
                    <td><strong>Total</strong></td>
                    <td><strong>${totalGrossIncome.toFixed(2)}</strong></td>
                    <td><strong>${totalGas.toFixed(2)}</strong></td>
                    <td><strong>${totalSalary.toFixed(2)}</strong></td>
                    <td><strong>${totalMaintenance.toFixed(2)}</strong></td>
                    <td><strong>${totalNetIncome.toFixed(2)}</strong></td>
                `;
                tableBody.appendChild(totalRow);
            }
             
             // Generate year grid
             function generateYearGrid(centerYear) {
                calendarPopup.innerHTML = '';
                for (let year = centerYear - 6; year <= centerYear + 5; year++) {
                    const span = document.createElement('span');
                    span.textContent = year;
                    if (year === currentYear) {
                        span.classList.add('selected');
                    }
                    span.addEventListener('click', function() {
                        currentYear = year;
                        selectedYearElement.textContent = currentYear;
                        calendarPopup.style.display = 'none';
                        fetchDataYear();
                        // Add code here to update the view with the selected year
                    });
                    calendarPopup.appendChild(span);
                }
            }

            // Initial grid generation
            generateYearGrid(initialYear);


            // Event listener to show calendar popup when the year is clicked
            selectedYearElement.addEventListener('click', function() {
                calendarPopup.style.display = 'grid';
                generateYearGrid(currentYear);
                calendarPopup.style.left = selectedYearElement.getBoundingClientRect().left + 'px';
                calendarPopup.style.top = selectedYearElement.getBoundingClientRect().bottom + 'px';
            });

            // Hide the calendar popup if clicked outside
            document.addEventListener('click', function(event) {
                if (!calendarPopup.contains(event.target) && event.target !== selectedYearElement) {
                    calendarPopup.style.display = 'none';
                }
            });

            function displaySelectedYear(year) {
                var selectedYearElement = document.getElementById('selected-year');
                if (selectedYearElement) {
                    selectedYearElement.textContent = year;
                } else {
                    console.log("Selected year element not found!");
                }
            }

            document.getElementById('prev-year').addEventListener('click', function() {
                currentYear--;
                // Panggil fungsi untuk memperbarui tampilan atau data lainnya yang tergantung pada tahun
                displaySelectedYear(currentYear);
                fetchDataYear();
                console.log(currentYear);
            });

            // Event listener untuk tombol navigasi tahun berikutnya
            document.getElementById('next-year').addEventListener('click', function() {
                currentYear++;
                // Panggil fungsi untuk memperbarui tampilan atau data lainnya yang tergantung pada tahun
                displaySelectedYear(currentYear);
                fetchDataYear();
                console.log(currentYear);
            });

            // Initial fetch
            fetchData();
            // fetchDataYear();

            // Polling every 10 seconds (for example)
            // setInterval(fetchData, 10000);

            attachDateButtonListeners();
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