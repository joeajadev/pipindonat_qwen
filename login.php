<?php
require 'config.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $branch_id = $_SESSION['branch_id'];
    
    // Ambil harga
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $price = $stmt->fetchColumn();
    $total = $price * $quantity;

    $stmt = $pdo->prepare("INSERT INTO sales (branch_id, product_id, quantity, total_price, sale_date) VALUES (?, ?, ?, ?, CURDATE())");
    $stmt->execute([$branch_id, $product_id, $quantity, $total]);
    
    $success = "Transaksi berhasil disimpan!";
}

// Ambil produk
$products = $pdo->query("SELECT * FROM products")->fetchAll();

ob_start();
?>
<h2 class="text-3xl font-bold text-sky-800 mb-6">Point of Sales (Cabang: <?= $_SESSION['branch_id'] ? 'ID '.$_SESSION['branch_id'] : 'Pusat' ?>)</h2>

<?php if (isset($success)): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= $success ?></div>
<?php endif; ?>

<div class="bg-white p-6 rounded-lg shadow-md border border-sky-200">
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sky-800 font-semibold mb-2">Pilih Donat</label>
            <select name="product_id" class="w-full border border-sky-300 rounded p-3 focus:ring-2 focus:ring-sky-500" required>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?> - Rp <?= number_format($p['price'], 0, ',', '.') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sky-800 font-semibold mb-2">Jumlah</label>
            <input type="number" name="quantity" min="1" class="w-full border border-sky-300 rounded p-3 focus:ring-2 focus:ring-sky-500" required>
        </div>
        <button type="submit" class="bg-sky-600 text-white px-6 py-3 rounded-lg hover:bg-sky-700 font-bold w-full">Proses Transaksi</button>
    </form>
</div>
<?php
$content = ob_get_clean();
include 'layout.php';
?>