<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';

$user_id = $_SESSION['user_id'];
$errors = [];

// Jika form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? null;
    $company = $_POST['company'] ?? null;
    $amount = $_POST['amount'] ?? null;
    $net_amount = $_POST['net_amount'] ?? null;
    $worker_amount = $_POST['worker_amount'] ?? null;
    $description = $_POST['description'] ?? null;
    $date = $_POST['date'] ?? null;

    // Validasi input
    if (!$type) $errors['type'] = "Jenis transaksi wajib diisi!";
    if (!$company || !in_array($company, ['Codejoki.id', 'Codetech.id'])) {
        $errors['company'] = "Perusahaan tidak valid.";
    }
    if (!is_numeric($amount) || $amount <= 0) {
        $errors['amount'] = "Jumlah harus berupa angka positif!";
    }
    if (!is_numeric($net_amount) || $net_amount < 0 || !is_numeric($worker_amount) || $worker_amount < 0) {
        $errors['amounts'] = "Jumlah bersih dan worker tidak boleh negatif.";
    }
    if ($net_amount + $worker_amount > $amount) {
        $errors['amounts'] = "Jumlah bersih dan worker tidak boleh lebih dari jumlah total.";
    }
    if (!$date || !strtotime($date)) {
        $errors['date'] = "Tanggal wajib diisi dan harus valid!";
    }

    // Jika tidak ada error, masukkan ke database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO transactions (user_id, type, company, amount, net_amount, worker_amount, description, date)
                VALUES (:user_id, :type, :company, :amount, :net_amount, :worker_amount, :description, :date)
            ");
            $stmt->execute([
                'user_id' => $user_id,
                'type' => $type,
                'company' => $company,
                'amount' => $amount,
                'net_amount' => $net_amount ?: 0, 
                'worker_amount' => $worker_amount ?: 0, 
                'description' => $description,
                'date' => $date
            ]);

            $_SESSION['success_message'] = "Transaksi berhasil ditambahkan!";

            // Arahkan ke halaman yang sesuai dengan perusahaan yang dipilih
            if ($company === 'Codejoki.id') {
                header("Location: codejoki-id.php");
            } elseif ($company === 'Codetech.id') {
                header("Location: codetech-id.php");
            } else {
                header("Location: transactions.php"); // Default jika perusahaan tidak sesuai
            }

            exit();
        } catch (PDOException $e) {
            error_log("Error inserting transaction: " . $e->getMessage());
            $errors['general'] = "Terjadi kesalahan saat menyimpan data. Coba lagi.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Tambah Transaksi</title>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col lg:flex-row h-full lg:h-screen">
        <!-- Sidebar -->
        <div class="lg:w-1/4 w-full">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-4 md:p-6 bg-gray-100">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Tambah Transaksi</h1>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 text-red-700 p-4 mb-6 rounded shadow-md">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="bg-white p-6 rounded-lg shadow-md">
                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Transaksi</label>
                    <select name="type" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="income" <?= (isset($type) && $type === 'income') ? 'selected' : '' ?>>Pendapatan</option>
                        <option value="expense" <?= (isset($type) && $type === 'expense') ? 'selected' : '' ?>>Pengeluaran</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Perusahaan</label>
                    <select name="company" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="Codejoki.id" <?= (isset($company) && $company === 'Codejoki.id') ? 'selected' : '' ?>>Codejoki.id</option>
                        <option value="Codetech.id" <?= (isset($company) && $company === 'Codetech.id') ? 'selected' : '' ?>>Codetech.id</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Total (Rp)</label>
                    <input type="number" name="amount" value="<?= htmlspecialchars($amount ?? '') ?>" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Bersih (Ke Kantong Anda)</label>
                    <input type="number" name="net_amount" value="<?= htmlspecialchars($net_amount ?? '') ?>" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah untuk Worker (Opsional)</label>
                    <input type="number" name="worker_amount" value="<?= htmlspecialchars($worker_amount ?? '') ?>" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" rows="4"><?= htmlspecialchars($description ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal</label>
                    <input type="date" name="date" value="<?= htmlspecialchars($date ?? '') ?>" class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                </div>

                <button type="submit" class="w-full py-3 bg-blue-600 text-white font-bold rounded-lg shadow-md hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
                    Tambah Transaksi
                </button>
            </form>
        </div>
    </div>
</body>
</html>
