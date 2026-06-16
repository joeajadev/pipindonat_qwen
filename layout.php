<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Manajemen Donat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sky: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-sky-50 text-sky-900 font-sans">
    <div class="flex h-screen">
        <!-- Side Menu -->
        <aside class="w-64 bg-sky-700 text-white flex flex-col">
            <div class="p-6 text-2xl font-bold border-b border-sky-600">🍩 Donat Manager</div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="dashboard.php" class="block p-3 rounded hover:bg-sky-600 transition">📊 Dashboard</a>
                <a href="pos.php" class="block p-3 rounded hover:bg-sky-600 transition">🛒 Point of Sales</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="forecast.php" class="block p-3 rounded hover:bg-sky-600 transition">📈 Peramalan (ARIMAX)</a>
                    <a href="mrp.php" class="block p-3 rounded hover:bg-sky-600 transition">🏭 MRP (Wagner-Whitin)</a>
                <?php endif; ?>
            </nav>
            <div class="p-4 border-t border-sky-600">
                <p class="text-sm text-sky-100">Halo, <?= htmlspecialchars($_SESSION['username']) ?></p>
                <a href="logout.php" class="block mt-2 text-center bg-red-500 hover:bg-red-600 text-white py-2 rounded">Logout</a>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-8">
            <?= $content ?? '' ?>
        </main>
    </div>
</body>
</html>