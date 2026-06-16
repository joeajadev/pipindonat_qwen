<?php
require 'config.php';
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_forecast'])) {
    $branch_id = $_POST['branch_id'];
    $product_id = $_POST['product_id'];
    $days_to_forecast = 7;

    // 1. Ambil data historis 30 hari terakhir (Komponen ARIMA)
    $stmt = $pdo->prepare("
        SELECT sale_date, SUM(quantity) as total_qty 
        FROM sales 
        WHERE branch_id = ? AND product_id = ? AND sale_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY sale_date ORDER BY sale_date ASC
    ");
    $stmt->execute([$branch_id, $product_id]);
    $historical = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $historical_qtys = array_column($historical, 'total_qty');
    $avg_historical = count($historical_qtys) > 0 ? array_sum($historical_qtys) / count($historical_qtys) : 10;

    // 2. Komponen Eksogen (X): Misal, faktor akhir pekan (Sabtu/Minggu = 1.5x, Hari biasa = 1.0x)
    $exogenous_factors = [1.0, 1.0, 1.0, 1.0, 1.0, 1.5, 1.5]; // Senin - Minggu

    // 3. Simulasi Peramalan (ARIMAX Sederhana: Base + Trend + Exogenous)
    $forecasts = [];
    for ($i = 1; $i <= $days_to_forecast; $i++) {
        $forecast_date = date('Y-m-d', strtotime("+$i days"));
        $day_of_week = date('w', strtotime($forecast_date)); // 0 = Minggu, 6 = Sabtu
        
        $exo_multiplier = $exogenous_factors[$day_of_week];
        // Rumus simulasi: Rata-rata historis * faktor eksogen + noise acak kecil
        $predicted_qty = round($avg_historical * $exo_multiplier + rand(-2, 2)); 
        
        $forecasts[] = [
            'date' => $forecast_date,
            'qty' => max(0, $predicted_qty) // Tidak boleh negatif
        ];

        // Simpan ke DB
        $stmt = $pdo->prepare("INSERT INTO forecasts (branch_id, product_id, forecast_date, predicted_qty) VALUES (?, ?, ?, ?)");
        $stmt->execute([$branch_id, $product_id, $forecast_date, max(0, $predicted_qty)]);
    }
}

$branches = $pdo->query("SELECT * FROM branches")->fetchAll();
$products = $pdo->query("SELECT * FROM products")->fetchAll();

ob_start();
?>
<h2 class="text-3xl font-bold text-sky-800 mb-6">Peramalan Produksi (Metode ARIMAX)</h2>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
    <p class="text-sm text-yellow-800">
        <strong>Catatan Teknis:</strong> Implementasi ARIMAX penuh memerlukan library Python (statsmodels). 
        Modul ini menggunakan <em>simulasi logika PHP</em> (Rata-rata Historis + Faktor Eksogen Harian) untuk demonstrasi.
    </p>
</div>

<div class="bg-white p-6 rounded-lg shadow-md border border-sky-200 mb-6">
    <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-sky-800 font-semibold mb-2">Cabang</label>
            <select name="branch_id" class="w-full border border-sky-300 rounded p-2" required>
                <?php foreach ($branches as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= $b['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sky-800 font-semibold mb-2">Produk</label>
            <select name="product_id" class="w-full border border-sky-300 rounded p-2" required>
                <?php foreach ($products as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="run_forecast" class="bg-sky-600 text-white px-4 py-2 rounded hover:bg-sky-700 font-bold">Jalankan Peramalan (7 Hari)</button>
    </form>
</div>

<?php if (isset($forecasts)): ?>
    <h3 class="text-xl font-bold text-sky-800 mb-4">Hasil Peramalan</h3>
    <table class="w-full bg-white rounded-lg shadow overflow-hidden">
        <thead class="bg-sky-600 text-white">
            <tr>
                <th class="p-3 text-left">Tanggal</th>
                <th class="p-3 text-left">Hari</th>
                <th class="p-3 text-left">Faktor Eksogen</th>
                <th class="p-3 text-left">Prediksi Jumlah Produksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($forecasts as $f): 
                $day_name = date('l', strtotime($f['date']));
                $is_weekend = in_array($day_name, ['Saturday', 'Sunday']) ? '1.5x (Akhir Pekan)' : '1.0x (Hari Biasa)';
            ?>
                <tr class="border-b border-sky-100 hover:bg-sky-50">
                    <td class="p-3"><?= $f['date'] ?></td>
                    <td class="p-3"><?= $day_name ?></td>
                    <td class="p-3"><?= $is_weekend ?></td>
                    <td class="p-3 font-bold text-sky-700"><?= $f['qty'] ?> pcs</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php
$content = ob_get_clean();
include 'layout.php';
?>