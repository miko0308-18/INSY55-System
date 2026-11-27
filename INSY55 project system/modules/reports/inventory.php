<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_admin();

$pageTitle = page_title('Inventory Report');
$nearDays = (int) setting('near_expiry_days', 3);

$products = db()->query(
    "SELECT p.*, c.name AS category_name
     FROM products p
     LEFT JOIN product_categories c ON c.id = p.category_id
     ORDER BY p.status, p.name"
)->fetchAll();

$low = array_filter($products, fn($p) => $p['status'] === 'Active' && (float)$p['stock_quantity'] > 0 && (float)$p['stock_quantity'] <= (float)$p['min_stock_level']);
$out = array_filter($products, fn($p) => $p['status'] === 'Active' && (float)$p['stock_quantity'] <= 0);
$expired = array_filter($products, fn($p) => $p['status'] === 'Active' && !empty($p['expiration_date']) && strtotime($p['expiration_date']) < strtotime(date('Y-m-d')));
$near = array_filter($products, function ($p) use ($nearDays) {
    if ($p['status'] !== 'Active' || empty($p['expiration_date'])) return false;
    $exp = strtotime($p['expiration_date']);
    $today = strtotime(date('Y-m-d'));
    $limit = strtotime("+{$nearDays} days", $today);
    return $exp >= $today && $exp <= $limit;
});
$archived = array_filter($products, fn($p) => $p['status'] !== 'Active');

$movements = db()->query(
    "SELECT sm.*, p.name AS product_name, u.full_name AS performed_by_name
     FROM stock_movements sm
     INNER JOIN products p ON p.id = sm.product_id
     LEFT JOIN users u ON u.id = sm.performed_by
     ORDER BY sm.created_at DESC
     LIMIT 200"
)->fetchAll();

$categories = db()->query(
    "SELECT c.name, COUNT(p.id) AS product_count,
            COALESCE(SUM(CASE WHEN p.status='Active' THEN p.stock_quantity ELSE 0 END),0) AS active_stock
     FROM product_categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id, c.name
     ORDER BY c.name"
)->fetchAll();

function output_inventory_csv(array $products, array $movements): void {
    $filename = 'inventory_report_' . date('Ymd') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Inventory Report', date('Y-m-d H:i:s')]);
    fputcsv($out, []);
    fputcsv($out, ['Current Inventory']);
    fputcsv($out, ['Name','Category','Meat Type','Price','Unit','Stock','Min Stock','Expiration','Status']);
    foreach ($products as $p) {
        fputcsv($out, [$p['name'],$p['category_name'],$p['meat_type'],$p['price'],$p['unit'],$p['stock_quantity'],$p['min_stock_level'],$p['expiration_date'],$p['status']]);
    }
    fputcsv($out, []);
    fputcsv($out, ['Stock Movement History (latest 200)']);
    fputcsv($out, ['Date','Product','Type','Change','Before','After','Reference','Remarks','By']);
    foreach ($movements as $m) {
        fputcsv($out, [$m['created_at'],$m['product_name'],$m['movement_type'],$m['quantity_change'],$m['quantity_before'],$m['quantity_after'],$m['reference_type'] . ':' . $m['reference_id'],$m['remarks'],$m['performed_by_name']]);
    }
    fclose($out);
    exit;
}

function output_inventory_excel(array $products, array $movements): void {
    $filename = 'inventory_report_' . date('Ymd') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "<html><head><meta charset=\"UTF-8\"></head><body>";
    echo "<h2>Inventory Report (" . date('Y-m-d H:i:s') . ")</h2>";
    echo "<h3>Current Inventory</h3><table border='1'><tr><th>Name</th><th>Category</th><th>Type</th><th>Price</th><th>Unit</th><th>Stock</th><th>Min</th><th>Expiration</th><th>Status</th></tr>";
    foreach ($products as $p) {
        echo "<tr><td>" . e($p['name']) . "</td><td>" . e((string)$p['category_name']) . "</td><td>" . e($p['meat_type']) . "</td><td>{$p['price']}</td><td>" . e($p['unit']) . "</td><td>{$p['stock_quantity']}</td><td>{$p['min_stock_level']}</td><td>" . e((string)$p['expiration_date']) . "</td><td>" . e($p['status']) . "</td></tr>";
    }
    echo "</table>";
    echo "<h3>Stock Movement History (latest 200)</h3><table border='1'><tr><th>Date</th><th>Product</th><th>Type</th><th>Change</th><th>Before</th><th>After</th><th>Reference</th><th>Remarks</th><th>By</th></tr>";
    foreach ($movements as $m) {
        echo "<tr><td>" . e($m['created_at']) . "</td><td>" . e($m['product_name']) . "</td><td>" . e($m['movement_type']) . "</td><td>{$m['quantity_change']}</td><td>{$m['quantity_before']}</td><td>{$m['quantity_after']}</td><td>" . e($m['reference_type'] . ':' . $m['reference_id']) . "</td><td>" . e((string)$m['remarks']) . "</td><td>" . e((string)$m['performed_by_name']) . "</td></tr>";
    }
    echo "</table></body></html>";
    exit;
}

$download = (string) get('download', '');
if ($download === 'csv') output_inventory_csv($products, $movements);
if ($download === 'excel') output_inventory_excel($products, $movements);

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header no-print">
    <div>
        <h1>Inventory Report</h1>
        <p class="subtitle">Stock levels, alerts, movement history, categories, and archived items (Admin)</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-secondary" href="<?= e(base_url('modules/reports/sales.php')) ?>">Sales Report</a>
        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save as PDF</button>
        <a class="btn btn-outline-success" href="<?= e(base_url('modules/reports/inventory.php?download=csv')) ?>">Download CSV</a>
        <a class="btn btn-outline-success" href="<?= e(base_url('modules/reports/inventory.php?download=excel')) ?>">Download Excel</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Low stock</div><div class="h4 mb-0"><?= count($low) ?></div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Out of stock</div><div class="h4 mb-0"><?= count($out) ?></div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Near expiry</div><div class="h4 mb-0"><?= count($near) ?></div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Expired</div><div class="h4 mb-0"><?= count($expired) ?></div></div></div></div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h6">Current inventory list</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead><tr><th>Name</th><th>Category</th><th>Type</th><th>Price</th><th>Unit</th><th>Stock</th><th>Min</th><th>Expiration</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= e($p['name']) ?></td>
                        <td><?= e((string)$p['category_name']) ?></td>
                        <td><?= e($p['meat_type']) ?></td>
                        <td><?= e(money($p['price'])) ?></td>
                        <td><?= e($p['unit']) ?></td>
                        <td><?= e((string)$p['stock_quantity']) ?></td>
                        <td><?= e((string)$p['min_stock_level']) ?></td>
                        <td><?= e((string)($p['expiration_date'] ?? '—')) ?></td>
                        <td><?= e($p['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Low / out of stock</h2>
            <ul class="mb-0 small">
                <?php foreach (array_merge($low, $out) as $p): ?>
                    <li><?= e($p['name']) ?> — stock <?= e((string)$p['stock_quantity']) ?> / min <?= e((string)$p['min_stock_level']) ?></li>
                <?php endforeach; ?>
                <?php if (!$low && !$out): ?><li class="text-muted">None</li><?php endif; ?>
            </ul>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Near-expiry / expired</h2>
            <ul class="mb-0 small">
                <?php foreach ($near as $p): ?><li>[Near] <?= e($p['name']) ?> — <?= e((string)$p['expiration_date']) ?></li><?php endforeach; ?>
                <?php foreach ($expired as $p): ?><li>[Expired] <?= e($p['name']) ?> — <?= e((string)$p['expiration_date']) ?></li><?php endforeach; ?>
                <?php if (!$near && !$expired): ?><li class="text-muted">None</li><?php endif; ?>
            </ul>
        </div></div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Product categories</h2>
            <table class="table table-sm mb-0"><thead><tr><th>Category</th><th>Products</th><th>Active stock</th></tr></thead><tbody>
            <?php foreach ($categories as $c): ?>
                <tr><td><?= e($c['name']) ?></td><td><?= (int)$c['product_count'] ?></td><td><?= e((string)$c['active_stock']) ?></td></tr>
            <?php endforeach; ?>
            </tbody></table>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Archived / inactive products</h2>
            <ul class="mb-0 small">
                <?php foreach ($archived as $p): ?><li><?= e($p['name']) ?> (<?= e($p['status']) ?>)</li><?php endforeach; ?>
                <?php if (!$archived): ?><li class="text-muted">None</li><?php endif; ?>
            </ul>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h6">Stock movement history (latest 200)</h2>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Change</th><th>Before</th><th>After</th><th>Reference</th><th>Remarks</th><th>By</th></tr></thead>
                <tbody>
                <?php foreach ($movements as $m): ?>
                    <tr>
                        <td><?= e($m['created_at']) ?></td>
                        <td><?= e($m['product_name']) ?></td>
                        <td><?= e($m['movement_type']) ?></td>
                        <td><?= e((string)$m['quantity_change']) ?></td>
                        <td><?= e((string)$m['quantity_before']) ?></td>
                        <td><?= e((string)$m['quantity_after']) ?></td>
                        <td><?= e(trim(($m['reference_type'] ?? '') . ' ' . ($m['reference_id'] ?? ''))) ?></td>
                        <td><?= e((string)$m['remarks']) ?></td>
                        <td><?= e((string)$m['performed_by_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<p class="text-muted small mt-3 no-print">PDF export: use browser Print → Save as PDF. CSV/Excel downloads are provided for reporting needs.</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
