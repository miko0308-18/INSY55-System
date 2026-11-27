<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle = page_title(__('Dashboard'));
$pdo = db();
$nearDays = (int) app_config('near_expiry_days');

try {
    check_inventory_alerts();
} catch (Throwable $e) {
    // Alerts are best-effort
}

$todaySales = $pdo->query(
    "SELECT COALESCE(SUM(total_amount),0) AS total, COUNT(*) AS txn
     FROM sales WHERE DATE(sale_datetime) = CURDATE() AND status IN ('Completed','Edited')"
)->fetch();

$monthSales = $pdo->query(
    "SELECT COALESCE(SUM(total_amount),0) AS total, COUNT(*) AS txn
     FROM sales WHERE YEAR(sale_datetime)=YEAR(CURDATE()) AND MONTH(sale_datetime)=MONTH(CURDATE())
       AND status IN ('Completed','Edited')"
)->fetch();

$inventory = $pdo->query(
    "SELECT
        SUM(status='Active') AS active_count,
        SUM(status='Active' AND stock_quantity <= 0) AS out_count,
        SUM(status='Active' AND stock_quantity > 0 AND stock_quantity <= min_stock_level) AS low_count,
        COALESCE(SUM(CASE WHEN status='Active' THEN stock_quantity * price ELSE 0 END),0) AS inventory_value
     FROM products"
)->fetch();

$nearExpiry = $pdo->prepare(
    "SELECT COUNT(*) FROM products
     WHERE status='Active' AND expiration_date IS NOT NULL
       AND expiration_date >= CURDATE()
       AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)"
);
$nearExpiry->execute([$nearDays]);
$nearExpiryCount = (int) $nearExpiry->fetchColumn();

$lowItems = $pdo->query(
    "SELECT id, name, stock_quantity, min_stock_level, unit
     FROM products
     WHERE status='Active' AND stock_quantity <= min_stock_level
     ORDER BY stock_quantity ASC LIMIT 8"
)->fetchAll();

$recentSales = $pdo->query(
    "SELECT s.receipt_number, s.sale_datetime, s.total_amount, s.status, u.full_name
     FROM sales s
     INNER JOIN users u ON u.id = s.cashier_user_id
     WHERE s.status IN ('Completed','Edited')
     ORDER BY s.sale_datetime DESC LIMIT 8"
)->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Dashboard')) ?></h1>
        <p class="subtitle"><?= e(__('Welcome back')) ?>, <?= e(current_user()['full_name']) ?> (<?= e(current_user()['role_name']) ?>)</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stats-card d-flex justify-content-between align-items-start">
            <div>
                <div class="label"><?= e(__('Total Sales')) ?> (Today)</div>
                <div class="value"><?= e(money($todaySales['total'])) ?></div>
                <div class="small text-muted"><?= (int) $todaySales['txn'] ?> <?= e(__('Transactions')) ?></div>
            </div>
            <div class="icon-box"><i class="bi bi-cash-coin"></i></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stats-card d-flex justify-content-between align-items-start">
            <div>
                <div class="label"><?= e(__('Total Sales')) ?> (Month)</div>
                <div class="value"><?= e(money($monthSales['total'])) ?></div>
                <div class="small text-muted"><?= (int) $monthSales['txn'] ?> <?= e(__('Transactions')) ?></div>
            </div>
            <div class="icon-box"><i class="bi bi-graph-up-arrow"></i></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stats-card d-flex justify-content-between align-items-start">
            <div>
                <div class="label"><?= e(__('Active Products')) ?></div>
                <div class="value"><?= (int) $inventory['active_count'] ?></div>
                <div class="small text-muted">Value <?= e(money($inventory['inventory_value'])) ?></div>
            </div>
            <div class="icon-box"><i class="bi bi-box-seam"></i></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stats-card d-flex justify-content-between align-items-start">
            <div>
                <div class="label"><?= e(__('Low Stock Items')) ?></div>
                <div class="value"><?= (int) $inventory['low_count'] ?></div>
                <div class="small text-muted"><?= e(__('Out of Stock')) ?>: <?= (int) $inventory['out_count'] ?> · <?= e(__('Near Expiry')) ?>: <?= $nearExpiryCount ?></div>
            </div>
            <div class="icon-box"><i class="bi bi-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3"><?= e(__('Low Stock Items')) ?></h2>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead><tr><th>Product</th><th>Stock</th><th>Min</th></tr></thead>
                        <tbody>
                        <?php if (!$lowItems): ?>
                            <tr><td colspan="3" class="text-muted"><?= e(__('No records found.')) ?></td></tr>
                        <?php else: foreach ($lowItems as $item): ?>
                            <tr>
                                <td><?= e($item['name']) ?></td>
                                <td><span class="badge badge-soft-warning"><?= e($item['stock_quantity'] . ' ' . $item['unit']) ?></span></td>
                                <td><?= e($item['min_stock_level']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h2 class="h6 mb-3"><?= e(__('Sales History')) ?></h2>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead><tr><th>Receipt</th><th>Staff</th><th>Total</th><th>When</th></tr></thead>
                        <tbody>
                        <?php if (!$recentSales): ?>
                            <tr><td colspan="4" class="text-muted"><?= e(__('No records found.')) ?></td></tr>
                        <?php else: foreach ($recentSales as $sale): ?>
                            <tr>
                                <td><?= e($sale['receipt_number']) ?></td>
                                <td><?= e($sale['full_name']) ?></td>
                                <td><?= e(money($sale['total_amount'])) ?></td>
                                <td class="small text-muted"><?= e($sale['sale_datetime']) ?></td>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
