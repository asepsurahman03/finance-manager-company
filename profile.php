<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require 'includes/db.php';

// Ambil ID pengguna dari sesi
$user_id = $_SESSION['user_id'];

// Ambil data pengguna berdasarkan user_id
$stmt_user = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt_user->execute(['user_id' => $user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Menangani perubahan password
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi password baru dan konfirmasi
    if ($new_password !== $confirm_password) {
        $error = "Password baru dan konfirmasi password tidak cocok.";
    } elseif (password_verify($current_password, $user['password'])) {
        // Update password jika valid
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt_update = $conn->prepare("UPDATE users SET password = :password WHERE id = :user_id");
        $stmt_update->execute(['password' => $hashed_password, 'user_id' => $user_id]);
        $success = "Password berhasil diperbarui.";
    } else {
        $error = "Password saat ini salah.";
    }
}

// Logout dan menghapus session
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Profile</title>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen flex-col md:flex-row">
        <!-- Sidebar -->
        <div class="w-full md:w-1/4 bg-white shadow-md md:h-full">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <header class="bg-white shadow p-4">
                <h1 class="text-xl font-bold text-gray-800">Profile</h1>
            </header>

            <!-- Profile Content -->
            <main class="flex-1 p-6 bg-gray-100">
                <!-- Profile Information Section -->
                <div class="bg-white p-6 rounded shadow mb-6">
                    <h2 class="text-xl font-bold mb-4">Informasi Akun</h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Username</label>
                        <p class="text-lg"><?= htmlspecialchars($user['username']) ?></p>
                    </div>
                </div>

                <!-- Password Change Section -->
                <div class="bg-white p-6 rounded shadow">
                    <h2 class="text-xl font-bold mb-4">Ganti Password</h2>
                    <?php if (isset($success)) : ?>
                        <div class="mb-4 text-green-600"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if (isset($error)) : ?>
                        <div class="mb-4 text-red-600"><?= $error ?></div>
                    <?php endif; ?>
                    <form action="" method="POST">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                            <input type="password" name="current_password" class="w-full p-2 border border-gray-300 rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                            <input type="password" name="new_password" class="w-full p-2 border border-gray-300 rounded" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="w-full p-2 border border-gray-300 rounded" required>
                        </div>
                        <button type="submit" name="change_password" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Ganti Password</button>
                    </form>

                    <!-- Logout -->
                    <form action="" method="POST" class="mt-6">
                        <button type="submit" name="logout" class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600">Logout</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
