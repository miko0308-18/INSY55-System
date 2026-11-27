<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_admin();

$pageTitle = page_title('Sales Report');
$from = (string) get('from', date('Y-m-01'));
$to = (string) get('to', date('Y-m-d'));
$group = (string) get('group', 'daily'); // daily, weekly, monthly
if (!in_array($group, ['daily', 'weekly', 'monthly'], true)) $group = 'daily';

$where = "WHERE DATE(s.sale_datetime) BETWEEN ? AND ? AND s.status IN ('Completed','Edited')";
$params = [$from, $to];

$summaryStmt = db()->prepare(
    "SELECT COUNT(*) AS transactions,
            COALESCE(SUM(total_amount),0) AS total_sales,
            COALESCE(SUM(subtotal),0) AS gross_subtotal,
            COALESCE(SUM(discount_amount),0) AS total_discounts,
            COALESCE(SUM(vat_amount),0) AS total_vat
     FROM sales s $where"
);
$summaryStmt->execute($params);
$summary = $summaryStmt->fetch() ?: [];

$byProductStmt = db()->prepare(
    "SELECT si.product_name_snapshot AS product_name,
            si.meat_type_snapshot AS meat_type,
            SUM(si.quantity) AS qty_sold,
            SUM(si.line_subtotal) AS sales_amount,
            COUNT(DISTINCT s.id) AS transactions
     FROM sale_items si
     INNER JOIN sales s ON s.id = si.sale_id
     $where
     GROUP BY si.product_name_snapshot, si.meat_type_snapshot
     ORDER BY qty_sold DESC, sales_amount DESC"
);
$byProductStmt->execute($params);
$byProduct = $byProductStmt->fetchAll();

$byStaffStmt = db()->prepare(
    "SELECT u.full_name AS staff_name,
            COUNT(*) AS transactions,
            COALESCE(SUM(s.total_amount),0) AS total_sales
     FROM sales s
     INNER JOIN users u ON u.id = s.cashier_user_id
     $where
     GROUP BY u.id, u.full_name
     ORDER BY total_sales DESC"
);
$byStaffStmt->execute($params);
$byStaff = $byStaffStmt->fetchAll();

if ($group === 'monthly') {
    $periodExpr = "DATE_FORMAT(s.sale_datetime, '%Y-%m')";
} elseif ($group === 'weekly') {
    $periodExpr = "YEARWEEK(s.sale_datetime, 1)";
} else {
    $periodExpr = "DATE(s.sale_datetime)";
}
$trendStmt = db()->prepare(
    "SELECT $periodExpr AS period_label,
            COUNT(*) AS transactions,
            COALESCE(SUM(s.total_amount),0) AS total_sales
     FROM sales s $where
     GROUP BY period_label
     ORDER BY period_label ASC"
);
$trendStmt->execute($params);
$trend = $trendStmt->fetchAll();

// CSV download
if ((string) get('download') === 'csv') {
    $filename = "sales_report_{$from}_to_{$to}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Sales Report', $from, 'to', $to]);
    fputcsv($out, []);
    fputcsv($out, ['Summary']);
    fputcsv($out, ['Transactions', (string) ($summary['transactions'] ?? 0)]);
    fputcsv($out, ['Total Sales', (string) ($summary['total_sales'] ?? 0)]);
    fputcsv($out, ['Gross Subtotal', (string) ($summary['gross_subtotal'] ?? 0)]);
    fputcsv($out, ['Discounts', (string) ($summary['total_discounts'] ?? 0)]);
    fputcsv($out, ['VAT', (string) ($summary['total_vat'] ?? 0)]);
    fputcsv($out, []);
    fputcsv($out, ['Sales by Product']);
    fputcsv($out, ['Product', 'Meat Type', 'Qty Sold', 'Sales Amount', 'Transactions']);
    foreach ($byProduct as $r) {
        fputcsv($out, [$r['product_name'], $r['meat_type'], $r['qty_sold'], $r['sales_amount'], $r['transactions']]);
    }
    fputcsv($out, []);
    fputcsv($out, ['Sales by Staff']);
    fputcsv($out, ['Staff', 'Transactions', 'Total Sales']);
    foreach ($byStaff as $r) {
        fputcsv($out, [$r['staff_name'], $r['transactions'], $r['total_sales']]);
    }
    fputcsv($out, []);
    fputcsv($out, ['Sales Trend (' . $group . ')']);
    fputcsv($out, ['Period', 'Transactions', 'Total Sales']);
    foreach ($trend as $r) {
        fputcsv($out, [$r['period_label'], $r['transactions'], $r['total_sales']]);
    }
    fclose($out);
    exit;
}

// Excel-friendly download (HTML table .xls; opens in Excel/LibreOffice)
if ((string) get('download') === 'excel') {
    $filename = "sales_report_{$from}_to_{$to}.xls";
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "<html><head><meta charset=\"UTF-8\"></head><body>";
    echo "<h2>Sales Report ({$from} to {$to})</h2>";
    echo "<h3>Summary</h3><table border='1'><tr><th>Transactions</th><th>Total Sales</th><th>Gross Subtotal</th><th>Discounts</th><th>VAT</th></tr>";
    echo "<tr><td>" . e((string)($summary['transactions'] ?? 0)) . "</td><td>" . e((string)($summary['total_sales'] ?? 0)) . "</td><td>" . e((string)($summary['gross_subtotal'] ?? 0)) . "</td><td>" . e((string)($summary['total_discounts'] ?? 0)) . "</td><td>" . e((string)($summary['total_vat'] ?? 0)) . "</td></tr></table>";
    echo "<h3>Sales by Product</h3><table border='1'><tr><th>Product</th><th>Meat Type</th><th>Qty Sold</th><th>Sales Amount</th><th>Transactions</th></tr>";
    foreach ($byProduct as $r) {
        echo "<tr><td>" . e($r['product_name']) . "</td><td>" . e((string)$r['meat_type']) . "</td><td>{$r['qty_sold']}</td><td>{$r['sales_amount']}</td><td>{$r['transactions']}</td></tr>";
    }
    echo "</table>";
    echo "<h3>Sales by Staff</h3><table border='1'><tr><th>Staff</th><th>Transactions</th><th>Total Sales</th></tr>";
    foreach ($byStaff as $r) {
        echo "<tr><td>" . e($r['staff_name']) . "</td><td>{$r['transactions']}</td><td>{$r['total_sales']}</td></tr>";
    }
    echo "</table>";
    echo "<h3>Sales Trend ({$group})</h3><table border='1'><tr><th>Period</th><th>Transactions</th><th>Total Sales</th></tr>";
    foreach ($trend as $r) {
        echo "<tr><td>" . e((string)$r['period_label']) . "</td><td>{$r['transactions']}</td><td>{$r['total_sales']}</td></tr>";
    }
    echo "</table></body></html>";
    exit;
}

$extraScripts = ['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'];
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header no-print">
    <div>
        <h1>Sales Report</h1>
        <p class="subtitle">Date-range summary, products sold, staff sales, and trends (Admin)</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a class="btn btn-outline-secondary" href="<?= e(base_url('modules/reports/inventory.php')) ?>">Inventory Report</a>
        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> Print / Save as PDF</button>
        <a class="btn btn-outline-success" href="<?= e(base_url('modules/reports/sales.php?from=' . urlencode($from) . '&to=' . urlencode($to) . '&group=' . urlencode($group) . '&download=csv')) ?>">Download CSV</a>
        <a class="btn btn-outline-success" href="<?= e(base_url('modules/reports/sales.php?from=' . urlencode($from) . '&to=' . urlencode($to) . '&group=' . urlencode($group) . '&download=excel')) ?>">Download Excel</a>
    </div>
</div>

<div class="card mb-3 no-print">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="<?= e($from) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="<?= e($to) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Trend grouping</label>
                <select name="group" class="form-select">
                    <option value="daily" <?= $group === 'daily' ? 'selected' : '' ?>>Daily</option>
                    <option value="weekly" <?= $group === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                    <option value="monthly" <?= $group === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Generate</button>
            </div>
        </form>
        <p class="text-muted small mt-2 mb-0">PDF: use browser Print → Save as PDF (no external PDF dependency). Payment-method filtering is not included.</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Total Sales</div><div class="h4 mb-0"><?= e(money($summary['total_sales'] ?? 0)) ?></div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Transactions</div><div class="h4 mb-0"><?= (int) ($summary['transactions'] ?? 0) ?></div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Discounts</div><div class="h4 mb-0"><?= e(money($summary['total_discounts'] ?? 0)) ?></div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">VAT</div><div class="h4 mb-0"><?= e(money($summary['total_vat'] ?? 0)) ?></div></div></div></div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h6">Sales trend (<?= e($group) ?>)</h2>
        <canvas id="salesTrendChart" height="90"></canvas>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h6">Sales by product</h2>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Product</th><th>Meat type</th><th>Qty sold</th><th>Sales amount</th><th>Transactions</th></tr></thead>
                        <tbody>
                        <?php if (!$byProduct): ?><tr><td colspan="5" class="text-muted">No data.</td></tr><?php endif; ?>
                        <?php foreach ($byProduct as $r): ?>
                            <tr>
                                <td><?= e($r['product_name']) ?></td>
                                <td><?= e((string) $r['meat_type']) ?></td>
                                <td><?= e((string) $r['qty_sold']) ?></td>
                                <td><?= e(money($r['sales_amount'])) ?></td>
                                <td><?= (int) $r['transactions'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h6">Sales by staff</h2>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Staff</th><th>Transactions</th><th>Total sales</th></tr></thead>
                        <tbody>
                        <?php if (!$byStaff): ?><tr><td colspan="3" class="text-muted">No data.</td></tr><?php endif; ?>
                        <?php foreach ($byStaff as $r): ?>
                            <tr>
                                <td><?= e($r['staff_name']) ?></td>
                                <td><?= (int) $r['transactions'] ?></td>
                                <td><?= e(money($r['total_sales'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function(){
  const ctx = document.getElementById('salesTrendChart');
  if (!ctx || typeof Chart === 'undefined') return;
  const labels = <?= json_encode(array_map(fn($r) => (string)$r['period_label'], $trend)) ?>;
  const totals = <?= json_encode(array_map(fn($r) => (float)$r['total_sales'], $trend)) ?>;
  new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{ label: 'Total Sales', data: totals, borderColor: '#e67e22', backgroundColor: 'rgba(230,126,34,0.15)', fill: true, tension: 0.25 }] },
    options: { responsive: true, plugins: { legend: { display: true } } }
  });
})();
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
