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


// Mengambil data untuk tabel
if (isset($_GET['ajax']) && isset($_GET['date'])) {
    header('Content-Type: application/json');

    $date = $_GET['date'];
    $sql = "
        SELECT 
            t.id,
            t.status,
            t.nominal,
            t.id_driver,
            t.id_truck,
            i.item_name,
            i.id AS item_id,
            d.driver_name,
            d.id AS driver_id,
            tr.unique_number,
            tr.id AS truck_id
        FROM 
            transaction t
        LEFT JOIN 
            item i ON t.id_item = i.id
        LEFT JOIN 
            driver d ON t.id_driver = d.id
        LEFT JOIN 
            truck tr ON t.id_truck = tr.id
        WHERE 
            DATE(t.date_time) = :date
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['date' => $date]);
    $data = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $kategori = '';
        $keterangan = '';
        $pendapatan = 0;
        $pengeluaran = 0;

        if ($row['status'] == 1) {
            $kategori = 'Pengiriman';
            $keterangan = $row['item_name'] . ' (' . $row['item_id'] . ')';
            $pendapatan = $row['nominal'];
        } elseif ($row['status'] == 2 && $row['id_driver'] != NULL) {
            $kategori = 'Gaji';
            $keterangan = $row['driver_name'] . ' (' . $row['driver_id'] . ')';
            $pengeluaran = $row['nominal'];
        } elseif ($row['status'] == 2 && $row['id_truck'] != NULL) {
            $kategori = 'Maintenance';
            $keterangan = $row['unique_number'] . ' (' . $row['truck_id'] . ')';
            $pengeluaran = $row['nominal'];
        }

        $data[] = [
            'kategori' => $kategori,
            'keterangan' => $keterangan,
            'pendapatan' => $pendapatan,
            'pengeluaran' => $pengeluaran
        ];
    }

    echo json_encode($data);
    exit;
}
?>