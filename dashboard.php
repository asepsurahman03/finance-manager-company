<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';

$user_id = $_SESSION['user_id'];

$filter_month = date('m');
$filter_year = date('Y');
$filter_date = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!empty($_GET['month']) && is_numeric($_GET['month'])) {
        $filter_month = $_GET['month'];
    }
    if (!empty($_GET['year']) && is_numeric($_GET['year'])) {
        $filter_year = $_GET['year'];
    }
    if (!empty($_GET['date']) && strtotime($_GET['date'])) {
        $filter_date = $_GET['date'];
    }
}

$condition = "user_id = :user_id";
$params = ['user_id' => $user_id];

if ($filter_date) {
    $condition .= " AND DATE(date) = :filter_date";
    $params['filter_date'] = $filter_date;
} else {
    $condition .= " AND MONTH(date) = :filter_month AND YEAR(date) = :filter_year";
    $params['filter_month'] = $filter_month;
    $params['filter_year'] = $filter_year;
}

try {
    $stmt_income = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE $condition AND type = 'income'");
    $stmt_income->execute($params);
    $total_income = $stmt_income->fetchColumn() ?? 0;

    $stmt_expense = $conn->prepare("SELECT SUM(amount) AS total FROM transactions WHERE $condition AND type = 'expense'");
    $stmt_expense->execute($params);
    $total_expense = $stmt_expense->fetchColumn() ?? 0;

    $stmt_transactions = $conn->prepare("SELECT * FROM transactions WHERE $condition ORDER BY date ASC");
    $stmt_transactions->execute($params);
    $transactions = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);

    $stmt_yearly = $conn->prepare("SELECT YEAR(date) AS year, SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense FROM transactions WHERE user_id = :user_id GROUP BY YEAR(date) ORDER BY year DESC");
    $stmt_yearly->execute(['user_id' => $user_id]);
    $yearly_data = $stmt_yearly->fetchAll(PDO::FETCH_ASSOC);

    $stmt_monthly = $conn->prepare("SELECT MONTH(date) AS month, SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) AS total_income, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) AS total_expense FROM transactions WHERE user_id = :user_id AND YEAR(date) = :filter_year GROUP BY MONTH(date) ORDER BY month ASC");
    $stmt_monthly->execute(['user_id' => $user_id, 'filter_year' => $filter_year]);
    $monthly_data = $stmt_monthly->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error in dashboard query: " . $e->getMessage());
    die("Terjadi kesalahan, silakan coba lagi.");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <title>Dashboard</title>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col lg:flex-row h-full lg:h-screen">
        <!-- Sidebar -->
        <div class="lg:w-1/4 w-full">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow p-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-sm text-gray-600 mt-2">Selamat datang, <?= htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>
            
            <!-- Profil Dropdown -->
            <div class="relative">
                <button onclick="toggleProfileMenu()" class="flex items-center text-gray-800 hover:text-blue-600 focus:outline-none">
                    <span class="mr-2"><?= htmlspecialchars($_SESSION['username']); ?></span>
                    <i class="fas fa-caret-down"></i> <!-- Icon dropdown -->
                </button>
                <!-- Dropdown Menu -->
                <div id="profileMenu" class="hidden absolute right-0 mt-2 w-48 bg-white shadow-lg rounded-lg">
                    <a href="profile.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Lihat Profil</a>
                    <a href="logout.php" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Logout</a>
                </div>
            </div>
        </header>
        
       

    <script>
        // JavaScript to toggle the profile dropdown menu visibility
        function toggleProfileMenu() {
            const menu = document.getElementById('profileMenu');
            menu.classList.toggle('hidden'); // Show or hide the menu when clicked
        }
    </script>

            <!-- Content -->
            <main class="flex-1 p-4 md:p-6 bg-gray-100">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-green-100 p-4 rounded shadow">
                        <h2 class="text-sm font-bold text-green-600">Pendapatan</h2>
                        <p class="text-2xl font-bold">Rp<?= number_format($total_income, 2) ?></p>
                    </div>
                    <div class="bg-red-100 p-4 rounded shadow">
                        <h2 class="text-sm font-bold text-red-600">Pengeluaran</h2>
                        <p class="text-2xl font-bold">Rp<?= number_format($total_expense, 2) ?></p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded shadow">
                        <h2 class="text-sm font-bold text-yellow-600">Saldo Total</h2>
                        <p class="text-2xl font-bold">Rp<?= number_format($total_income - $total_expense, 2) ?></p>
                    </div>
                </div>

                <!-- Monthly Data -->
                <div class="mt-12">
                    <h2 class="text-lg font-bold mb-4">Pendapatan dan Pengeluaran Per Bulan <?= $filter_year ?></h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($monthly_data as $data): ?>
                            <div class="bg-white shadow rounded p-4 border-t-4 border-purple-500 transform hover:scale-105">
                                <h3 class="text-xl font-bold"><?= date('F', mktime(0, 0, 0, $data['month'], 10)) ?> <?= $filter_year ?></h3>
                                <div class="flex justify-between mt-4">
                                    <div>
                                        <h4 class="text-sm font-semibold text-green-600">Pendapatan</h4>
                                        <p class="text-xl font-bold">Rp<?= number_format($data['total_income'], 2) ?></p>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-red-600">Pengeluaran</h4>
                                        <p class="text-xl font-bold">Rp<?= number_format($data['total_expense'], 2) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="mb-8 bg-white p-6 rounded-lg shadow-lg border border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Filter Pencarian</h2>
                    <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Month Selector -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Bulan</label>
                            <select name="month" class="w-full p-3 border-2 border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $filter_month == $m ? 'selected' : '' ?>>
                                        <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Year Selector -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Tahun</label>
                            <select name="year" class="w-full p-3 border-2 border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                                    <option value="<?= $y ?>" <?= $filter_year == $y ? 'selected' : '' ?>>
                                        <?= $y ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Date Input -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-2">Tanggal (Opsional)</label>
                            <input type="date" name="date" value="<?= $filter_date ?>" class="w-full p-3 border-2 border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <!-- Submit Button -->
                        <div class="col-span-1 sm:col-span-2 lg:col-span-3 mt-4">
                            <button type="submit" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                Cari
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Yearly Data -->
                <div class="mt-12">
                    <h2 class="text-lg font-bold mb-4">Pendapatan dan Pengeluaran Per Tahun</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($yearly_data as $data): ?>
                            <div class="bg-white shadow rounded p-4 border-t-4 border-blue-500 transform hover:scale-105">
                                <h3 class="text-xl font-bold"><?= $data['year'] ?></h3>
                                <div class="flex justify-between mt-4">
                                    <div>
                                        <h4 class="text-sm font-semibold text-green-600">Pendapatan</h4>
                                        <p class="text-xl font-bold">Rp<?= number_format($data['total_income'], 2) ?></p>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-red-600">Pengeluaran</h4>
                                        <p class="text-xl font-bold">Rp<?= number_format($data['total_expense'], 2) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                

            </main>
        </div>
    </div>
</body>
</html>
