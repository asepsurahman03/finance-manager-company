<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';

$user_id = $_SESSION['user_id'];

// Periksa apakah data yang diperlukan dikirimkan melalui POST
if (isset($_POST['id'], $_POST['date'], $_POST['type'], $_POST['description'], $_POST['amount'])) {
    $transaction_id = $_POST['id'];
    $date = $_POST['date'];
    $type = $_POST['type'];
    $description = $_POST['description'];
    $amount = $_POST['amount'];

    // Validasi input (misalnya, memastikan jumlah adalah angka positif)
    if ($amount <= 0) {
        die("Jumlah transaksi tidak valid!");
    }

    // Update transaksi di database
    $stmt = $conn->prepare("UPDATE transactions SET date = :date, type = :type, description = :description, amount = :amount WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        'date' => $date,
        'type' => $type,
        'description' => $description,
        'amount' => $amount,
        'id' => $transaction_id,
        'user_id' => $user_id
    ]);

    // Redirect ke halaman daftar transaksi setelah berhasil
    header("Location: transactions.php");
    exit();
} else {
    die("Data transaksi tidak lengkap!");
}
?>
