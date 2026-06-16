<?php
require 'config.php';
require 'db.php';

// Fungsi Algoritma Wagner-Whitin
function wagnerWhitin($demand, $setupCost, $holdingCost) {
    $n = count($demand);
    if ($n === 0) return [];

    // C[j] = biaya minimum untuk memenuhi permintaan dari periode 1 sampai j
    $C = array_fill(0, $n + 1, INF);
    $C[0] = 0;
    
    // best_t[j] = periode di mana produksi terakhir dilakukan untuk memenuhi permintaan sampai j
    $best_t = array_fill(0, $n + 1, 0);

    for ($j = 1; $j <= $n; $j++) {
        for ($i = 1; $i <= $j; $i++) {
            $cost = $C[$i - 1] + $setupCost;
            $cumulative_demand = 0;
            
            // Hitung biaya penyimpanan jika produksi dilakukan di periode i untuk memenuhi sampai j
            for ($k = $i; $k <= $j; $k++) {
                $cumulative_demand += $demand[$k];
                // Biaya simpan = jumlah unit * biaya simpan per unit * lama disimpan (k - i)
                $cost += $holdingCost * $cumulative_demand * ($k - $i);
            }

            if ($cost < $C[$j]) {
                $C[$j] = $cost;
                $best_t[$j] = $i;
            isikan
        }
    }

    // Backtracking untuk menemukan jadwal produksi optimal
    $production_schedule = array_fill(1, $n, 0);
    $j = $n;
    while ($j > 0) {
        $i = $best_t[$j];
        $qty = 0;
        for ($k = $i; $k <= $j; $k++) {
            $qty += $demand[$k];
        }
        $production_schedule[$i] = $qty;
        $j = $i - 1;
    }

    return [
        'min_cost' => $C[$n],
        'schedule' => $production_schedule
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_mrp'])) {
    $branch_id = $_POST['branch_id'];
    $product_id = $_POST['product_id'];

    // Ambil data forecast 7 hari ke depan
    $stmt = $pdo->prepare("
        SELECT forecast_date, predicted_qty 
        FROM forecasts 
        WHERE branch_id = ? AND product_id = ? AND forecast_date >= CURDATE()
        ORDER BY forecast_date ASC LIMIT 7
    ");
    $stmt->execute([$branch_id, $product_id]);
    $forecasts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $demand = array_column($forecasts, 'predicted_qty');
    // Pastikan array demand memiliki 7 elemen (isi 0 jika kurang)
    $demand = array_pad($demand, 7, 0);

    // Ambil parameter biaya dari produk
    $stmt = $pdo->prepare("SELECT setup_cost, holding_cost FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    $result = wagnerWhitin($demand, $product['setup_cost'], $product['holding_cost']);
}

$branches = $pdo->query("SELECT * FROM branches")->fetchAll();
$products = $pdo->query("SELECT * FROM products")->fetchAll();

ob_start();
?>
<h2 class="text-3xl font-bold text-sky-800 mb-6">MRP Produksi (Algoritma Wagner-Whitin)</h2>
<div class="bg-sky-50 border-l-4 border-sky-500 p-4 mb-6">
    <p class="text-sm text-sky-800">
        Algoritma ini menghitung jadwal produksi optimal untuk meminimalkan <strong>Biaya Setup</strong> dan <strong>Biaya Simpan (Holding Cost)</strong> berdasarkan data peramalan.
   .
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
                    <option value="<?= $p['id'] ?>"><?= $p['name'] ?> (Setup: Rp<?= number_format($p['setup_cost']) ?>, Simpan: Rp<?= number_format($p['holding_cost']) ?>/hari)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" name="run_mrp" class="bg-sky-600 text-white px-4 py-2 rounded hover:bg-sky-700 font-bold">Hitung MRP Optimal</button>
    </form>
</div>

<?php if (isset($result)): ?>
    <div class="bg-white p-6 rounded-lg shadow-md border border-sky-200">
        <h3 class="text-xl font-bold text-sky-800 mb-4">Hasil Perencanaan Produksi Optimal</h3>
        <p class="mb-4 text-sky-700">Total Biaya Minimum (Setup + Simpan): <strong>Rp <?= number_format($result['min_cost'], 0, ',', '.') ?></strong></p>
        
        <table class="w-full bg-white rounded-lg shadow overflow-hidden">
            <thead class="bg-sky-600 text-white">
                <tr>
                    <th class="p-3 text-left">Periode (Hari ke-)</th>
                    <th class="p-3 text-left">Tanggal Forecast</th>
                    <th class="p-3 text-left">Permintaan (Forecast)</th>
                    <th class="p-3 text-left bg-sky-700">Jadwal Produksi Optimal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['schedule'] as $day => $prod_qty): 
                    $f_date = $forecasts[$day-1]['forecast_date'] ?? '-';
                    $f_demand = $forecasts[$day-1]['predicted_qty'] ?? 0;
                    $row_class = $prod_qty > 0 ? 'bg-green-50 font-bold text-green-800' : 'text-gray-500';
                ?>
                    <tr class="border-b border-sky-100 <?= $row_class ?>">
                        <td class="p-3">Hari ke-<?= $day ?></td>
                        <td class="p-3"><?= $f_date ?></td>
                        <td class="p-3"><?= $f_demand ?> pcs</td>
                        <td class="p-3">
                            <?= $prod_qty > 0 ? "PRODUKSI: " . $prod_qty . " pcs 🏭" : "-" ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="text-sm text-gray-600 mt-4 italic">*Produksi hanya dilakukan pada hari yang ditandai untuk menghemat biaya setup, dengan jumlah yang mencakup permintaan hari itu dan hari-hari berikutnya hingga produksi berikutnya.</p>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
include 'layout.php';
?>