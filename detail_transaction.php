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

    <title>Detail Transaksi</title>
</head>
<body class="bg-gray-100">
    <div class="flex flex-col lg:flex-row h-screen">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 p-6 bg-gray-100">
            <h1 class="text-xl font-bold mb-6">Detail </h1>

            <!-- Transaction Detail -->
            <div class="bg-white p-6 rounded-lg shadow-md">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Tanggal</h2>
                    <p class="text-xl text-gray-600"><?= $transaction['date'] ?></p>
                </div>

                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Perusahaan</h2>
                    <p class="text-xl text-gray-600"><?= $transaction['company'] ?></p>
                </div>


                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Jenis</h2>
                    <p class="text-xl text-<?= $transaction['type'] == 'income' ? 'green' : 'red' ?>-600">
                        <?= ucfirst($transaction['type']) ?>
                    </p>
                </div>

                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Deskripsi</h2>
                    <p class="text-xl text-gray-600"><?= $transaction['description'] ?></p>
                </div>

                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Kantong</h2>
                    <p class="text-xl text-gray-600">Rp<?= number_format($transaction['net_amount'], 2) ?></p>
                </div>

                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Worker</h2>
                    <p class="text-xl text-gray-600">Rp<?= number_format($transaction['worker_amount'], 2) ?></p>
                </div>

                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-gray-700">Jumlah</h2>
                    <p class="text-xl text-gray-600">Rp<?= number_format($transaction['amount'], 2) ?></p>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-between">
                    <a href="transactions.php" class="text-blue-500 hover:text-blue-700 font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                    <a href="edit_transaction.php?id=<?= $transaction['id'] ?>" class="text-yellow-500 hover:text-yellow-700 font-semibold">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
