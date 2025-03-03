<?php
if (isset($_GET['id'])) {
    $transaction_id = $_GET['id'];
    require 'includes/db.php';

    // Hapus transaksi berdasarkan ID
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = :id");
    $stmt->execute(['id' => $transaction_id]);

    header("Location: transactions.php"); // Redirect ke halaman daftar transaksi
    exit();
} else {
    die("ID transaksi tidak diberikan!");
}
