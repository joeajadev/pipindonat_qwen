<?php
require 'config.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['branch_id'] = $user['branch_id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Donat Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-sky-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96 border-t-4 border-sky-500">
        <h2 class="text-2xl font-bold text-sky-700 mb-6 text-center">🍩 Login Donat Shop</h2>
        <?php if (isset($error)): ?>
            <p class="text-red-500 text-sm mb-4 text-center"><?= $error ?></p>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sky-800 mb-2">Username</label>
                <input type="text" name="username" class="w-full border border-sky-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-sky-500" required>
            </div>
            <div class="mb-6">
                <label class="block text-sky-800 mb-2">Password</label>
                <input type="password" name="password" class="w-full border border-sky-300 rounded p-2 focus:outline-none focus:ring-2 focus:ring-sky-500" required>
            </div>
            <button type="submit" class="w-full bg-sky-600 text-white py-2 rounded hover:bg-sky-700 transition">Masuk</button>
        </form>
        <p class="text-xs text-gray-500 mt-4 text-center">Gunakan: admin / password123</p>
    </div>
</body>
</html>