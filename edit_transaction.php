<?php
// Pastikan ID diterima dan valid
if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];
    require 'includes/db.php';

    // Ambil detail transaksi berdasarkan ID
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = :id");
    $stmt->execute(['id' => $transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$transaction) {
        die("Transaksi tidak ditemukan!");
    }
} else {
    die("ID transaksi tidak diberikan!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <title>Edit Transaksi</title>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col md:flex-row h-screen">
        <!-- Sidebar -->
        <div class="w-full md:w-1/4 bg-white shadow-md">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6 bg-gray-100">
            <h1 class="text-2xl font-bold mb-6">Edit</h1>

            <!-- Edit Transaction Form -->
            <form action="update_transaction.php" method="POST">
                <input type="hidden" name="id" value="<?= $transaction['id'] ?>">

                <!-- Tanggal -->
                <div class="mb-6">
                    <label for="date" class="block text-lg font-semibold text-gray-700">Tanggal</label>
                    <input type="date" id="date" name="date" value="<?= $transaction['date'] ?>" class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <!-- Company -->
                <div class="mb-6">
                    <label for="company" class="block text-lg font-semibold text-gray-700">Company</label>
                    <input type="company" id="company" name="company" value="<?= $transaction['company'] ?>" class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <!-- Jenis Transaksi -->
                <div class="mb-6">
                    <label for="type" class="block text-lg font-semibold text-gray-700">Jenis </label>
                    <select id="type" name="type" class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        <option value="income" <?= $transaction['type'] == 'income' ? 'selected' : '' ?>>Pendapatan</option>
                        <option value="expense" <?= $transaction['type'] == 'expense' ? 'selected' : '' ?>>Pengeluaran</option>
                    </select>
                </div>

                <!-- Deskripsi -->
                <div class="mb-6">
                    <label for="description" class="block text-lg font-semibold text-gray-700">Deskripsi</label>
                    <textarea id="description" name="description" rows="4" class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required><?= $transaction['description'] ?></textarea>
                </div>

                <!-- Kantong -->
                <div class="mb-6">
                    <label for="net_amount" class="block text-lg font-semibold text-gray-700">Kantong</label>
                    <input type="number" id="net_amount" name="net_amount" value="<?= $transaction['net_amount'] ?>" class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <!-- Worker -->
                <div class="mb-6">
                    <label for="worker_amount" class="block text-lg font-semibold text-gray-700">Worker</label>
                    <input type="number" id="worker_amount" name="worker_amount" value="<?= $transaction['worker_amount'] ?>" class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <!-- Jumlah -->
                <div class="mb-6">
                    <label for="amount" class="block text-lg font-semibold text-gray-700">Jumlah</label>
                    <input type="number" id="amount" name="amount" value="<?= $transaction['amount'] ?>" class="w-full px-4 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 flex flex-col sm:flex-row justify-between gap-4">
                    <button type="submit" class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition duration-300 w-full sm:w-auto">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                    <a href="transactions.php" class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-300 w-full sm:w-auto">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
