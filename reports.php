<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';

$user_id = $_SESSION['user_id'];
$current_year = date('Y');

// Query untuk total pendapatan & pengeluaran semua perusahaan
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(date, '%m') AS month, 
           SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income, 
           SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense 
    FROM transactions 
    WHERE user_id = :user_id AND YEAR(date) = :year 
    GROUP BY month");
$stmt->execute(['user_id' => $user_id, 'year' => $current_year]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk Codetech.id
$stmtCodetech = $conn->prepare("
    SELECT DATE_FORMAT(date, '%m') AS month, 
           SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income, 
           SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense 
    FROM transactions 
    WHERE user_id = :user_id AND YEAR(date) = :year AND company = 'Codetech.id' 
    GROUP BY month");
$stmtCodetech->execute(['user_id' => $user_id, 'year' => $current_year]);
$dataCodetech = $stmtCodetech->fetchAll(PDO::FETCH_ASSOC);

// Query untuk Codejoki.id
$stmtCodejoki = $conn->prepare("
    SELECT DATE_FORMAT(date, '%m') AS month, 
           SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS income, 
           SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS expense 
    FROM transactions 
    WHERE user_id = :user_id AND YEAR(date) = :year AND company = 'Codejoki.id' 
    GROUP BY month");
$stmtCodejoki->execute(['user_id' => $user_id, 'year' => $current_year]);
$dataCodejoki = $stmtCodejoki->fetchAll(PDO::FETCH_ASSOC);

// Menyiapkan data untuk Chart.js
$months = [];
$incomes = [];
$expenses = [];
$incomesCodetech = [];
$expensesCodetech = [];
$incomesCodejoki = [];
$expensesCodejoki = [];

// Mengonversi data transaksi umum
foreach ($data as $row) {
    $months[] = date('F', mktime(0, 0, 0, $row['month'], 10));
    $incomes[] = $row['income'];
    $expenses[] = $row['expense'];
}

// Mengonversi data untuk Codetech.id
foreach ($dataCodetech as $row) {
    $incomesCodetech[] = $row['income'];
    $expensesCodetech[] = $row['expense'];
}

// Mengonversi data untuk Codejoki.id
foreach ($dataCodejoki as $row) {
    $incomesCodejoki[] = $row['income'];
    $expensesCodejoki[] = $row['expense'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Laporan Keuangan</title>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col lg:flex-row h-full lg:h-screen">
        <!-- Sidebar -->
        <div class="lg:w-1/4 w-full">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-6 bg-gray-100">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Laporan Keuangan</h1>

            <!-- Chart Keuangan Keseluruhan -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-bold mb-4">Keuangan Keseluruhan</h2>
                <canvas id="financialChart"></canvas>
            </div>

            <!-- Chart Codetech.id -->
            <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                <h2 class="text-xl font-bold mb-4">Keuangan Codetech.id</h2>
                <canvas id="codetechChart"></canvas>
            </div>

            <!-- Chart Codejoki.id -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-4">Keuangan Codejoki.id</h2>
                <canvas id="codejokiChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        const months = <?= json_encode($months) ?>;

        // Chart Keuangan Keseluruhan
        new Chart(document.getElementById('financialChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Pendapatan',
                        data: <?= json_encode($incomes) ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pengeluaran',
                        data: <?= json_encode($expenses) ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.7)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Chart Codetech.id
        new Chart(document.getElementById('codetechChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Pendapatan Codetech.id',
                        data: <?= json_encode($incomesCodetech) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pengeluaran Codetech.id',
                        data: <?= json_encode($expensesCodetech) ?>,
                        backgroundColor: 'rgba(255, 206, 86, 0.7)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Chart Codejoki.id
        new Chart(document.getElementById('codejokiChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Pendapatan Codejoki.id',
                        data: <?= json_encode($incomesCodejoki) ?>,
                        backgroundColor: 'rgba(153, 102, 255, 0.7)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Pengeluaran Codejoki.id',
                        data: <?= json_encode($expensesCodejoki) ?>,
                        backgroundColor: 'rgba(255, 159, 64, 0.7)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>
