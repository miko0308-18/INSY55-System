<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_admin();

$pageTitle = page_title(__('Analytics'));

$from = (string) get('from', date('Y-m-01'));
$to = (string) get('to', date('Y-m-d'));
$compareFrom = (string) get('compare_from', date('Y-m-01', strtotime('-1 month')));
$compareTo = (string) get('compare_to', date('Y-m-t', strtotime('-1 month')));
$group = (string) get('group', 'daily');
if (!in_array($group, ['daily', 'weekly', 'monthly'], true)) $group = 'daily';

function sales_aggregate(string $from, string $to, string $group): array {
    if ($group === 'monthly') {
        $expr = "DATE_FORMAT(sale_datetime, '%Y-%m')";
    } elseif ($group === 'weekly') {
        $expr = "YEARWEEK(sale_datetime, 1)";
    } else {
        $expr = "DATE(sale_datetime)";
    }
    $stmt = db()->prepare(
        "SELECT $expr AS label, COUNT(*) AS transactions, COALESCE(SUM(total_amount),0) AS total_sales
         FROM sales
         WHERE DATE(sale_datetime) BETWEEN ? AND ? AND status IN ('Completed','Edited')
         GROUP BY label ORDER BY label ASC"
    );
    $stmt->execute([$from, $to]);
    $rows = $stmt->fetchAll();
    $stmt2 = db()->prepare(
        "SELECT COUNT(*) AS transactions, COALESCE(SUM(total_amount),0) AS total_sales
         FROM sales WHERE DATE(sale_datetime) BETWEEN ? AND ? AND status IN ('Completed','Edited')"
    );
    $stmt2->execute([$from, $to]);
    $summary = $stmt2->fetch() ?: ['transactions' => 0, 'total_sales' => 0];
    return ['rows' => $rows, 'summary' => $summary];
}

$current = sales_aggregate($from, $to, $group);
$previous = sales_aggregate($compareFrom, $compareTo, $group);

$topProductsStmt = db()->prepare(
    "SELECT si.product_name_snapshot AS name, si.meat_type_snapshot AS meat_type,
            SUM(si.quantity) AS qty_sold, SUM(si.line_subtotal) AS sales_amount
     FROM sale_items si
     INNER JOIN sales s ON s.id = si.sale_id
     WHERE DATE(s.sale_datetime) BETWEEN ? AND ? AND s.status IN ('Completed','Edited')
     GROUP BY si.product_name_snapshot, si.meat_type_snapshot
     ORDER BY qty_sold DESC, sales_amount DESC
     LIMIT 10"
);
$topProductsStmt->execute([$from, $to]);
$topProducts = $topProductsStmt->fetchAll();

$topCategoriesStmt = db()->prepare(
    "SELECT COALESCE(c.name, 'Uncategorized') AS category,
            SUM(si.quantity) AS qty_sold, SUM(si.line_subtotal) AS sales_amount
     FROM sale_items si
     INNER JOIN sales s ON s.id = si.sale_id
     LEFT JOIN products p ON p.id = si.product_id
     LEFT JOIN product_categories c ON c.id = p.category_id
     WHERE DATE(s.sale_datetime) BETWEEN ? AND ? AND s.status IN ('Completed','Edited')
     GROUP BY category
     ORDER BY sales_amount DESC
     LIMIT 10"
);
$topCategoriesStmt->execute([$from, $to]);
$topCategories = $topCategoriesStmt->fetchAll();

$lowStock = db()->query(
    "SELECT name, stock_quantity, min_stock_level, unit
     FROM products WHERE status='Active' AND stock_quantity > 0 AND stock_quantity <= min_stock_level
     ORDER BY stock_quantity ASC LIMIT 10"
)->fetchAll();

$inventoryValue = db()->query(
    "SELECT COALESCE(SUM(price * stock_quantity),0) AS total_value,
            COALESCE(SUM(stock_quantity),0) AS total_units,
            COUNT(*) AS active_products
     FROM products WHERE status='Active'"
)->fetch() ?: [];

$salesSummary = $current['summary'];
$prevSummary = $previous['summary'];
$deltaSales = (float)$salesSummary['total_sales'] - (float)$prevSummary['total_sales'];
$deltaTx = (int)$salesSummary['transactions'] - (int)$prevSummary['transactions'];

$extraScripts = ['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'];
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Analytics')) ?></h1>
        <p class="subtitle">Descriptive analytics: trends, top products/categories, inventory value, and period comparison</p>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="<?= e($from) ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="<?= e($to) ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Compare from</label>
                <input type="date" name="compare_from" class="form-control" value="<?= e($compareFrom) ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Compare to</label>
                <input type="date" name="compare_to" class="form-control" value="<?= e($compareTo) ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Grouping</label>
                <select name="group" class="form-select">
                    <option value="daily" <?= $group==='daily'?'selected':'' ?>>Daily</option>
                    <option value="weekly" <?= $group==='weekly'?'selected':'' ?>>Weekly</option>
                    <option value="monthly" <?= $group==='monthly'?'selected':'' ?>>Monthly</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Total Sales (current)</div><div class="h4 mb-0"><?= e(money($salesSummary['total_sales'])) ?></div><div class="small <?= $deltaSales>=0?'text-success':'text-danger' ?>">Δ <?= e(money($deltaSales)) ?> vs compare</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Transactions (current)</div><div class="h4 mb-0"><?= (int)$salesSummary['transactions'] ?></div><div class="small <?= $deltaTx>=0?'text-success':'text-danger' ?>">Δ <?= $deltaTx ?> vs compare</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Inventory Value</div><div class="h4 mb-0"><?= e(money($inventoryValue['total_value'] ?? 0)) ?></div><div class="small text-muted"><?= (int)($inventoryValue['active_products'] ?? 0) ?> active products</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Compare Period Sales</div><div class="h4 mb-0"><?= e(money($prevSummary['total_sales'])) ?></div><div class="small text-muted"><?= (int)$prevSummary['transactions'] ?> transactions</div></div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-8">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Sales trend over time (<?= e($group) ?>)</h2>
            <canvas id="trendChart" height="100"></canvas>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Period comparison</h2>
            <canvas id="compareChart" height="180"></canvas>
            <p class="small text-muted mt-2 mb-0">Current: <?= e($from) ?>–<?= e($to) ?><br>Compare: <?= e($compareFrom) ?>–<?= e($compareTo) ?></p>
        </div></div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Top-selling meats/products</h2>
            <canvas id="topProductsChart" height="140"></canvas>
            <div class="table-responsive mt-2">
                <table class="table table-sm mb-0"><thead><tr><th>Product</th><th>Type</th><th>Qty</th><th>Sales</th></tr></thead><tbody>
                <?php foreach ($topProducts as $p): ?>
                    <tr><td><?= e($p['name']) ?></td><td><?= e((string)$p['meat_type']) ?></td><td><?= e((string)$p['qty_sold']) ?></td><td><?= e(money($p['sales_amount'])) ?></td></tr>
                <?php endforeach; if(!$topProducts): ?><tr><td colspan="4" class="text-muted">No data.</td></tr><?php endif; ?>
                </tbody></table>
            </div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <h2 class="h6">Best-selling categories</h2>
            <canvas id="categoriesChart" height="140"></canvas>
            <div class="table-responsive mt-2">
                <table class="table table-sm mb-0"><thead><tr><th>Category</th><th>Qty</th><th>Sales</th></tr></thead><tbody>
                <?php foreach ($topCategories as $c): ?>
                    <tr><td><?= e($c['category']) ?></td><td><?= e((string)$c['qty_sold']) ?></td><td><?= e(money($c['sales_amount'])) ?></td></tr>
                <?php endforeach; if(!$topCategories): ?><tr><td colspan="3" class="text-muted">No data.</td></tr><?php endif; ?>
                </tbody></table>
            </div>
        </div></div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h6">Low-stock products</h2>
        <div class="table-responsive">
            <table class="table table-sm mb-0"><thead><tr><th>Product</th><th>Stock</th><th>Min</th><th>Unit</th></tr></thead><tbody>
            <?php foreach ($lowStock as $p): ?>
                <tr><td><?= e($p['name']) ?></td><td><?= e((string)$p['stock_quantity']) ?></td><td><?= e((string)$p['min_stock_level']) ?></td><td><?= e($p['unit']) ?></td></tr>
            <?php endforeach; if(!$lowStock): ?><tr><td colspan="4" class="text-muted">No low-stock items.</td></tr><?php endif; ?>
            </tbody></table>
        </div>
    </div>
</div>

<script>
(function(){
  if (typeof Chart === 'undefined') return;
  const orange = '#e67e22';
  const orangeSoft = 'rgba(230,126,34,0.18)';
  const trend = document.getElementById('trendChart');
  if (trend) {
    new Chart(trend, {
      type: 'line',
      data: {
        labels: <?= json_encode(array_map(fn($r)=>(string)$r['label'],$current['rows'])) ?>,
        datasets: [{ label: 'Total Sales', data: <?= json_encode(array_map(fn($r)=>(float)$r['total_sales'],$current['rows'])) ?>, borderColor: orange, backgroundColor: orangeSoft, fill: true, tension: 0.25 }]
      },
      options: { responsive: true }
    });
  }
  const cmp = document.getElementById('compareChart');
  if (cmp) {
    new Chart(cmp, {
      type: 'bar',
      data: {
        labels: ['Total Sales', 'Transactions'],
        datasets: [
          { label: 'Current', data: [<?= (float)$salesSummary['total_sales'] ?>, <?= (int)$salesSummary['transactions'] ?>], backgroundColor: orange },
          { label: 'Compare', data: [<?= (float)$prevSummary['total_sales'] ?>, <?= (int)$prevSummary['transactions'] ?>], backgroundColor: '#95a5a6' }
        ]
      },
      options: { responsive: true }
    });
  }
  const top = document.getElementById('topProductsChart');
  if (top) {
    new Chart(top, {
      type: 'bar',
      data: {
        labels: <?= json_encode(array_map(fn($r)=>$r['name'],$topProducts)) ?>,
        datasets: [{ label: 'Qty sold', data: <?= json_encode(array_map(fn($r)=>(float)$r['qty_sold'],$topProducts)) ?>, backgroundColor: orange }]
      },
      options: { indexAxis: 'y', responsive: true }
    });
  }
  const cat = document.getElementById('categoriesChart');
  if (cat) {
    new Chart(cat, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode(array_map(fn($r)=>$r['category'],$topCategories)) ?>,
        datasets: [{ data: <?= json_encode(array_map(fn($r)=>(float)$r['sales_amount'],$topCategories)) ?>, backgroundColor: ['#e67e22','#d35400','#f39c12','#95a5a6','#7f8c8d','#34495e','#e74c3c','#2ecc71','#3498db','#9b59b6'] }]
      },
      options: { responsive: true }
    });
  }
})();
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
