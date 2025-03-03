<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';

$user_id = $_SESSION['user_id'];

// Ambil filter dari URL
$type_filter = $_GET['type'] ?? null;
$company_filter = $_GET['company'] ?? null;
$amount_filter = $_GET['amount'] ?? null;

// Validasi filter tipe transaksi
if ($type_filter && !in_array($type_filter, ['income', 'expense'])) {
    die("Filter tipe tidak valid.");
}

// Pagination setup
$perPage = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $perPage;

// Query utama dengan filter
$query = "SELECT * FROM transactions WHERE user_id = :user_id";
$params = [':user_id' => $user_id];

if ($type_filter) {
    $query .= " AND type = :type";
    $params[':type'] = $type_filter;
}

if (!empty($company_filter)) {
    $query .= " AND company = :company";
    $params[':company'] = $company_filter;
}

if ($amount_filter && in_array($amount_filter, ['net_amount', 'worker_amount'])) {
    $query .= " AND $amount_filter > 0";
}

$query .= " ORDER BY date DESC LIMIT " . intval($start) . ", " . intval($perPage);

try {
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error in transactions query: " . $e->getMessage());
    die("Terjadi kesalahan saat mengambil data transaksi.");
}

// Hitung total transaksi untuk pagination
$totalQuery = "SELECT COUNT(*) FROM transactions WHERE user_id = :user_id";
$totalParams = [':user_id' => $user_id];

if ($type_filter) {
    $totalQuery .= " AND type = :type";
    $totalParams[':type'] = $type_filter;
}

if (!empty($company_filter)) {
    $totalQuery .= " AND company = :company";
    $totalParams[':company'] = $company_filter;
}

try {
    $stmt = $conn->prepare($totalQuery);
    $stmt->execute($totalParams);
    $totalTransactions = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error in total query: " . $e->getMessage());
    die("Terjadi kesalahan saat menghitung total transaksi.");
}

$totalPages = ceil($totalTransactions / $perPage);

// Hitung total jumlah uang
$totalNetAmount = 0;
$totalWorkerAmount = 0;
$totalOverallAmount = 0;

foreach ($transactions as $transaction) {
    $totalNetAmount += $transaction['net_amount'];
    $totalWorkerAmount += $transaction['worker_amount'];
    $totalOverallAmount += $transaction['amount'];
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Daftar Pendapatan & Pengeluaran</title>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col lg:flex-row h-full lg:h-screen">
        <!-- Sidebar -->
        <div class="lg:w-1/4 w-full">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
<div class="flex-1 p-4 md:p-6 bg-gray-100">
            <h1 class="text-3xl font-bold mb-6">Daftar Pendapatan & Pengeluaran</h1>

            <!-- Filter -->
            <div class="mb-6 flex flex-wrap gap-4">
                <a href="transactions.php" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-300">Semua</a>
                <a href="?type=income" class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-300">Pendapatan</a>
                <a href="?type=expense" class="px-6 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300">Pengeluaran</a>
           

             <!-- Filter Perusahaan -->
    <select id="companyFilter" onchange="applyFilter()" class="border px-2 py-1 rounded-lg">
        <option value="">Pilih Perusahaan</option>
        <option value="codejoki.id">CodeJoki.id</option>
        <option value="codetech.id">Codetech.id</option>
        <!-- Tambahkan perusahaan lainnya -->
    </select>
</div>

<script>
    function applyFilter() {
        const company = document.getElementById('companyFilter').value;
        const urlParams = new URLSearchParams(window.location.search);
        if (company) {
            urlParams.set('company', company);
        } else {
            urlParams.delete('company');
        }
        window.location.search = urlParams.toString();
    }
</script>
            <!-- Transactions Table -->
            <div class="mt-6">
                <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                    <h2 class="text-xl font-bold">Pendapatan & Pengeluaran</h2>
                    
                    <div class="flex flex-wrap gap-4">
                        <!-- Month Filter -->
                        <select id="monthFilter" class="border px-2 py-1 rounded-lg">
                            <option value="">Pilih Bulan</option>
                            <option value="01">Januari</option>
                            <option value="02">Februari</option>
                            <option value="03">Maret</option>
                            <option value="04">April</option>
                            <option value="05">Mei</option>
                            <option value="06">Juni</option>
                            <option value="07">Juli</option>
                            <option value="08">Agustus</option>
                            <option value="09">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                        
                        <!-- Year Filter -->
                        <select id="yearFilter" class="border px-2 py-1 rounded-lg">
                            <option value="">Pilih Tahun</option>
                            <?php
                            $currentYear = date('Y');
                            for ($year = $currentYear + 5; $year >= $currentYear - 10; $year--) {
                                echo "<option value='$year'>$year</option>";
                            }
                            ?>
                        </select>

                        <!-- Filter Button -->
                        <button onclick="filterTransactions()" class="bg-green-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-green-600 transition duration-300">
                            Filter
                        </button>

                        <!-- Print Button -->
                        <button onclick="printTransactions()" class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow-md hover:bg-blue-600 transition duration-300">
                            <i class="fas fa-print mr-2"></i> Cetak
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table id="transactionsTable" class="w-full bg-white rounded-lg shadow-md">
                        <thead class="bg-gray-200 text-gray-600">
                            <tr>
                                <th class="px-4 py-2">Tanggal</th>
                                <th class="px-4 py-2">Perusahaan</th>
                                <th class="px-4 py-2">Deskripsi</th>
                                <th class="px-4 py-2">Jenis</th>
                                <th class="px-4 py-2">Kantong</th>
                                <th class="px-4 py-2">Worker</th>
                                <th class="px-4 py-2">Jumlah</th>
                                <th class="px-4 py-2 print:hidden">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $totalAmount = 0; 
                                if (count($transactions) > 0): 
                                    foreach ($transactions as $transaction): 
                                        if ($transaction['type'] == 'income') {
                                            $totalAmount += $transaction['amount'];
                                        } else {
                                            $totalAmount -= $transaction['amount'];
                                        }
                            ?>
                                        <tr>
                                            <td class="border px-4 py-2" data-date="<?= $transaction['date'] ?>" data-amount="<?= $transaction['amount'] ?>" data-type="<?= $transaction['type'] ?>">
                                                <?= $transaction['date'] ?>
                                            </td>
                                            <td class="border px-4 py-2"><?= $transaction['company'] ?></td>
                                            <td class="border px-4 py-2"><?= $transaction['description'] ?></td>
                                            <td class="border px-4 py-2 <?= $transaction['type'] == 'income' ? 'text-green-600' : 'text-red-600' ?>">
                                                <?= $transaction['type'] == 'income' ? 'Pendapatan' : 'Pengeluaran' ?>
                                            </td>
                                            <td class="border px-4 py-2"><?= number_format($transaction['net_amount'], 2) ?></td>
                                            <td class="border px-4 py-2"><?= number_format($transaction['worker_amount'], 2) ?></td>
                                            <td class="border px-4 py-2 <?= $transaction['type'] == 'income' ? 'text-green-600' : 'text-red-600' ?>">
                                                Rp<?= number_format($transaction['amount'], 2) ?>
                                            </td>
                                            <td class="border px-4 py-2 flex space-x-4 justify-center print:hidden">
                                                <a href="detail_transaction.php?id=<?= $transaction['id'] ?>" class="text-blue-500">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_transaction.php?id=<?= $transaction['id'] ?>" class="text-yellow-500">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="delete_transaction.php?id=<?= $transaction['id'] ?>" class="text-red-500" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                            <?php 
                                    endforeach; 
                                else: 
                            ?>
                                    <tr>
                                        <td colspan="5" class="border px-4 py-2 text-center">Tidak ada data Pendapatan & Pengeluaran</td>
                                    </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-center space-x-2">
                    <?php if ($totalPages > 1): ?>
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>&type=<?= $type_filter ?>&amount=<?= $amount_filter ?>" class="px-3 py-1 border rounded bg-gray-200 text-gray-600">
                                &laquo; Prev
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?= $i ?>&type=<?= $type_filter ?>&amount=<?= $amount_filter ?>" 
                            class="px-3 py-1 border rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-600' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>&type=<?= $type_filter ?>&amount=<?= $amount_filter ?>" class="px-3 py-1 border rounded bg-gray-200 text-gray-600">
                                Next &raquo;
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Total Section -->
                <div class="mt-6 p-4 bg-white rounded-lg shadow-md">
                    <h3 class="text-xl font-bold mb-4">Ringkasan Total</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-4 bg-blue-100 text-blue-700 rounded-lg shadow-md">
                            <h4 class="text-lg font-semibold">Total Kantong (Net Amount)</h4>
                            <p class="text-2xl font-bold">Rp<?= number_format($totalNetAmount, 2) ?></p>
                        </div>
                        <div class="p-4 bg-green-100 text-green-700 rounded-lg shadow-md">
                            <h4 class="text-lg font-semibold">Total Worker Amount</h4>
                            <p class="text-2xl font-bold">Rp<?= number_format($totalWorkerAmount, 2) ?></p>
                        </div>
                        <div class="p-4 bg-red-100 text-red-700 rounded-lg shadow-md">
                            <h4 class="text-lg font-semibold">Total Jumlah Keseluruhan</h4>
                            <p class="text-2xl font-bold">Rp<?= number_format($totalOverallAmount, 2) ?></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</body>
</html>



<script>
    function filterTransactions() {
        const month = document.getElementById('monthFilter').value;
        const year = document.getElementById('yearFilter').value;
        const rows = document.querySelectorAll('#transactionsTable tbody tr');

        let filteredTotal = 0;

        rows.forEach(row => {
            const date = row.querySelector('[data-date]').getAttribute('data-date');
            const amount = parseFloat(row.querySelector('[data-date]').getAttribute('data-amount'));
            const type = row.querySelector('[data-date]').getAttribute('data-type');
            const [rowYear, rowMonth] = date.split('-');

            if ((month && rowMonth !== month) || (year && rowYear !== year)) {
                row.style.display = 'none';
            } else {
                row.style.display = '';
                if (type === 'income') {
                    filteredTotal += amount;
                } else {
                    filteredTotal -= amount;
                }
            }
        });

        document.getElementById('totalAmount').textContent = `Rp${filteredTotal.toLocaleString('id-ID', { minimumFractionDigits: 2 })}`;
    }

    function printTransactions() {
        const month = document.getElementById('monthFilter').value;
        const year = document.getElementById('yearFilter').value;

        if (!month || !year) {
            alert("Silakan pilih bulan dan tahun terlebih dahulu!");
            return;
        }

        const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        const monthName = monthNames[parseInt(month, 10) - 1];
        const reportTitle = `    <h3 style="text-align: center; font-size: 1.5em; font-weight: bold; margin-bottom: 15px;">Laporan Pendapatan & Pengeluaran Keseluruhan Finance - Pada Bulan ${monthName} ${year}</h3>`;

        // Ambil elemen tabel yang terlihat (difilter)
        const visibleRows = Array.from(document.querySelectorAll('#transactionsTable tbody tr'))
                                .filter(row => row.style.display !== 'none');

        if (visibleRows.length === 0) {
            alert("Tidak ada data yang dapat dicetak!");
            return;
        }

        let printContents = `
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h2 { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                th { background-color: #f4f4f4; }
                .total-section { margin-top: 20px; text-align: right; font-size: 1.2em; font-weight: bold; }
            </style>
            <h2>${reportTitle}</h2>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Perusahaan</th>
                        <th>Deskripsi</th>
                        <th>Jenis</th>
                        <th>Kantong</th>
                        <th>Worker</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>`;

        let totalIncome = 0;
        let totalExpense = 0;
        let totalNet = 0;
        let totalWorker = 0;
        let totalOverall = 0;

        visibleRows.forEach(row => {
            const cells = row.querySelectorAll("td");
            const amount = parseFloat(row.querySelector('[data-amount]').getAttribute('data-amount')) || 0;
            const netAmount = parseFloat(cells[4].innerText.replace(/[^0-9.-]+/g, "")) || 0;
            const workerAmount = parseFloat(cells[5].innerText.replace(/[^0-9.-]+/g, "")) || 0;
            const type = row.querySelector('[data-type]').getAttribute('data-type');

            if (type === 'income') {
                totalIncome += amount;
            } else {
                totalExpense += amount;
            }

            totalNet += netAmount;
            totalWorker += workerAmount;
            totalOverall += amount;

            printContents += `<tr>${row.innerHTML}</tr>`;
        });

        printContents += `</tbody></table>`;
        printContents += `
            <div class="total-section" style="
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        ">
            <h4 style="text-align: center; font-size: 1.2em; font-weight: bold; margin-bottom: 15px;">Ringkasan Laporan Keuangan</h4>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="background-color: #e3fcef; padding: 15px; border-radius: 8px; text-align: center; color: #218838; font-weight: bold;">
                    <i class="fas fa-arrow-up" style="color: green; margin-right: 8px;"></i>
                    Total Pendapatan:
                    <p style="font-size: 1.2em; margin-top: 5px;">Rp${totalIncome.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</p>
                </div>

                <div style="background-color: #f8d7da; padding: 15px; border-radius: 8px; text-align: center; color: #c82333; font-weight: bold;">
                    <i class="fas fa-arrow-down" style="color: red; margin-right: 8px;"></i>
                    Total Pengeluaran:
                    <p style="font-size: 1.2em; margin-top: 5px;">Rp${totalExpense.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</p>
                </div>

                <div style="background-color: #d1ecf1; padding: 15px; border-radius: 8px; text-align: center; color: #0c5460; font-weight: bold;">
                    <i class="fas fa-wallet" style="color: #0c5460; margin-right: 8px;"></i>
                    Total Kantong:
                    <p style="font-size: 1.2em; margin-top: 5px;">Rp${totalNet.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</p>
                </div>

                <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; text-align: center; color: #856404; font-weight: bold;">
                    <i class="fas fa-user" style="color: #856404; margin-right: 8px;"></i>
                    Total Worker:
                    <p style="font-size: 1.2em; margin-top: 5px;">Rp${totalWorker.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</p>
                </div>

                <div style="background-color: #cce5ff; padding: 15px; border-radius: 8px; text-align: center; color: #004085; font-weight: bold;">
                    <i class="fas fa-calculator" style="color: #004085; margin-right: 8px;"></i>
                    Total Keseluruhan:
                    <p style="font-size: 1.4em; margin-top: 5px; font-weight: bold;">Rp${totalOverall.toLocaleString('id-ID', { minimumFractionDigits: 2 })}</p>
                </div>
            </div>
        </div>
        `;

        const originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }
</script>



            <!-- Income and Expense Extremes -->
            <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
                <div class="bg-green-100 p-6 rounded-lg shadow-md border-t-4 border-green-600">
                    <h3 class="text-2xl font-semibold text-green-600 mb-4">Pendapatan Terbesar dan Terkecil</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-arrow-up text-green-600 mr-2"></i>
                            <p class="text-gray-700">Pendapatan Terbesar: <span class="font-bold">Rp<?= number_format($income_data['max_income'], 2) ?></span></p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-arrow-down text-red-600 mr-2"></i>
                            <p class="text-gray-700">Pendapatan Terkecil: <span class="font-bold">Rp<?= number_format($income_data['min_income'], 2) ?></span></p>
                        </div>
                    </div>
                </div> -->

                <!-- Largest and Smallest Expense -->
                <!-- <div class="bg-red-100 p-6 rounded-lg shadow-md border-t-4 border-red-600">
                    <h3 class="text-2xl font-semibold text-red-600 mb-4">Pengeluaran Terbesar dan Terkecil</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <i class="fas fa-arrow-up text-red-600 mr-2"></i>
                            <p class="text-gray-700">Pengeluaran Terbesar: <span class="font-bold">Rp<?= number_format($expense_data['max_expense'], 2) ?></span></p>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-arrow-down text-green-600 mr-2"></i>
                            <p class="text-gray-700">Pengeluaran Terkecil: <span class="font-bold">Rp<?= number_format($expense_data['min_expense'], 2) ?></span></p>
                        </div>
                    </div>
                </div>
            </div> -->


        </div>
    </div>
</body>
</html>
