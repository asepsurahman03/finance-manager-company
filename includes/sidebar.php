<div id="sidebar" class="bg-gradient-to-b from-gray-800 to-gray-600 text-white w-full sm:w-64 flex flex-col relative">

    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-500 flex items-center justify-between">
        <h2 class="text-2xl font-bold">Finance Manager</h2>
        <!-- Mobile Toggle Button -->
        <button id="menuToggle" class="block sm:hidden focus:outline-none">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>
    </div>

    <!-- User Info -->
    <div id="userInfo" class="px-6 py-4 border-b border-gray-500">
        <?php if (isset($_SESSION['username'])): ?>
            <p class="mt-2 text-sm">
                Hallo, 
                <a href="profile.php" class="font-medium animate-pulse hover:text-gray-400"><?= htmlspecialchars($_SESSION['username']); ?></a>
            </p>
        <?php else: ?>
            <p class="mt-2 text-sm">Pengguna Tidak Terdaftar</p>
        <?php endif; ?>
    </div>

    <!-- Navigation Menu -->
    <nav id="menuContent" class="flex-1 px-4 py-4 space-y-2">
        <a href="dashboard.php" class="flex items-center px-4 py-2 bg-gray-700 rounded-lg hover:bg-gray-800 transition-all transform hover:scale-105 menu-link">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v18H3V3z"></path>
            </svg>
            Dashboard
        </a>
        <a href="add_transaction.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-all transform hover:scale-105 menu-link">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Data
        </a>
             <!-- New Menu Items -->
             <a href="codejoki-id.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-all transform hover:scale-105 menu-link">
            <!-- Gaming Icon -->
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2a10 10 0 11-10 10A10 10 0 0112 2z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h.01M15 13h.01M12 13h0M9 17a4 4 0 006 0"></path>
            </svg>
            Codejoki.id
        </a>

        <a href="codetech-id.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-all transform hover:scale-105 menu-link">
            <!-- Coding Icon -->
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 18l-6-6 6-6M8 6h8v12H8z"></path>
            </svg>
            Codetech.id
        </a>
        <a href="transactions.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-all transform hover:scale-105 menu-link">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
            </svg>
            Semua Data
        </a>
        <a href="reports.php" class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-700 transition-all transform hover:scale-105 menu-link">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18v6H3V3zm0 12h18v6H3v-6z"></path>
            </svg>
            Laporan
        </a>
   

    </nav>
</div>


<script>
    const menuToggle = document.getElementById('menuToggle');
    const menuContent = document.getElementById('menuContent');

    menuToggle.addEventListener('click', () => {
        menuContent.classList.toggle('hidden');
        menuContent.classList.toggle('block');
    });

    const sidebar = document.getElementById('sidebar');

// Set height dynamically
// document.addEventListener("DOMContentLoaded", () => {
//     sidebar.style.height = `${document.body.scrollHeight}px`;
// });

    // Ensure the sidebar layout adjusts based on screen size
    window.addEventListener("resize", () => {
        if (window.innerWidth >= 640) {
            menuContent.classList.remove('hidden');
            menuContent.classList.add('block');
        } else {
            menuContent.classList.add('hidden');
            menuContent.classList.remove('block');
        }
    });
</script>
