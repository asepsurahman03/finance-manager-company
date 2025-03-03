<?php
include 'db.php';

// Ambil data ringkasan bulanan dari database
$income = $pdo->query("SELECT SUM(amount) AS total FROM transactions WHERE amount > 0")->fetch(PDO::FETCH_ASSOC)['total'];
$expense = $pdo->query("SELECT SUM(amount) AS total FROM transactions WHERE amount < 0")->fetch(PDO::FETCH_ASSOC)['total'];
$balance = $income + $expense;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ringkasan Bulanan dan Harian</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="bg-gray-800 text-gray-200 w-full md:w-64 h-screen">
      <div class="p-4">
        <h1 class="text-2xl font-bold text-center text-white">Admin Dashboard</h1>
      </div>
      <nav class="mt-6">
        <ul>
          <li class="my-2">
            <a href="index.php" class="block py-2 px-4 rounded hover:bg-gray-700 transition">
              🏠 Dashboard
            </a>
          </li>
          <li class="my-2">
            <a href="add-transaction.php" class="block py-2 px-4 rounded hover:bg-gray-700 transition">
              ➕ Tambah Transaksi
            </a>
          </li>
          <li class="my-2">
            <a href="transaction-history.php" class="block py-2 px-4 rounded hover:bg-gray-700 transition">
              📜 Riwayat Transaksi
            </a>
          </li>
        </ul>
      </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 bg-gray-100 p-6">
      <header class="bg-white shadow rounded-lg p-4 mb-6">
        <h1 class="text-3xl font-bold text-gray-700">Ringkasan Keuangan</h1>
      </header>
      <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white shadow rounded-lg p-4 text-center">
          <p class="text-gray-500">Total Pendapatan Bulanan</p>
          <p class="text-green-600 text-2xl font-bold">Rp<?= number_format($income, 0, ',', '.'); ?></p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
          <p class="text-gray-500">Total Pengeluaran Bulanan</p>
          <p class="text-red-600 text-2xl font-bold">Rp<?= number_format(abs($expense), 0, ',', '.'); ?></p>
        </div>
        <div class="bg-white shadow rounded-lg p-4 text-center">
          <p class="text-gray-500">Saldo Bulanan</p>
          <p class="text-gray-800 text-2xl font-bold">Rp<?= number_format($balance, 0, ',', '.'); ?></p>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
