<?php
$hostname = "localhost";
$username = "root";
$password = "";
$database_name = "logistics_company"; // Ganti dengan nama database Anda

try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $database_name: " . $e->getMessage());
}

function getMonthlyData($month, $year, $pdo) {
    $data = [];

    // Query untuk pendapatan kotor
    $stmt = $pdo->prepare("SELECT SUM(nominal) as total FROM transaction WHERE status = 1 AND MONTH(date_time) = :month AND YEAR(date_time) = :year");
    $stmt->execute(['month' => $month, 'year' => $year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['gross_income'] = $row['total'] ?? 0;

    // Query untuk gaji
    $stmt = $pdo->prepare("SELECT SUM(nominal) as total FROM transaction WHERE status = 2 AND id_driver IS NOT NULL AND MONTH(date_time) = :month AND YEAR(date_time) = :year");
    $stmt->execute(['month' => $month, 'year' => $year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['salary'] = $row['total'] ?? 0;

    // Query untuk maintenance
    $stmt = $pdo->prepare("SELECT SUM(nominal) as total FROM transaction WHERE status = 2 AND id_truck IS NOT NULL AND MONTH(date_time) = :month AND YEAR(date_time) = :year");
    $stmt->execute(['month' => $month, 'year' => $year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['maintenance'] = $row['total'] ?? 0;

    // Hitung pendapatan bersih
    $data['net_income'] = $data['gross_income'] - ($data['salary'] + $data['maintenance']);

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
