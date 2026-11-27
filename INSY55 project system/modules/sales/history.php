<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/sales_helper.php';

$pageTitle = page_title(__('Sales History'));

$status = trim((string) get('status', ''));
$q = trim((string) get('q', ''));
$from = trim((string) get('from', ''));
$to = trim((string) get('to', ''));

$sql = 'SELECT s.*, u.full_name AS cashier_name
        FROM sales s
        INNER JOIN users u ON u.id = s.cashier_user_id
        WHERE 1=1';
$params = [];

if (!is_admin()) {
    $sql .= ' AND s.cashier_user_id = ?';
    $params[] = (int) current_user()['id'];
}
if ($status !== '' && in_array($status, [SALE_COMPLETED, SALE_EDITED, SALE_VOIDED, SALE_DELETED], true)) {
    $sql .= ' AND s.status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $sql .= ' AND (s.receipt_number LIKE ? OR s.customer_name LIKE ?)';
    $params[] = '%' . $q . '%';
    $params[] = '%' . $q . '%';
}
if ($from !== '') {
    $sql .= ' AND DATE(s.sale_datetime) >= ?';
    $params[] = $from;
}
if ($to !== '') {
    $sql .= ' AND DATE(s.sale_datetime) <= ?';
    $params[] = $to;
}

$sql .= ' ORDER BY s.sale_datetime DESC, s.id DESC LIMIT 200';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Sales History')) ?></h1>
        <p class="subtitle">View receipts, correct sales, and review statuses</p>
    </div>
    <a class="btn btn-primary" href="<?= e(base_url('modules/sales/create.php')) ?>"><i class="bi bi-plus-circle"></i> <?= e(__('Record Sale')) ?></a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" value="<?= e($q) ?>" placeholder="Receipt # or customer">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <?php foreach ([SALE_COMPLETED, SALE_EDITED, SALE_VOIDED, SALE_DELETED] as $st): ?>
                        <option value="<?= e($st) ?>" <?= $status === $st ? 'selected' : '' ?>><?= e($st) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="from" class="form-control" value="<?= e($from) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="to" class="form-control" value="<?= e($to) ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-fill">Filter</button>
                <a href="<?= e(base_url('modules/sales/history.php')) ?>" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Date/Time</th>
                        <th>Cashier</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$sales): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No sales found.</td></tr>
                <?php endif; ?>
                <?php foreach ($sales as $sale): ?>
                    <?php
                    $badge = 'bg-secondary';
                    if ($sale['status'] === SALE_COMPLETED) $badge = 'bg-success';
                    elseif ($sale['status'] === SALE_EDITED) $badge = 'bg-info';
                    elseif ($sale['status'] === SALE_VOIDED) $badge = 'bg-warning text-dark';
                    elseif ($sale['status'] === SALE_DELETED) $badge = 'bg-danger';
                    ?>
                    <tr>
                        <td><a href="<?= e(base_url('modules/sales/receipt.php?id=' . $sale['id'])) ?>"><?= e($sale['receipt_number']) ?></a></td>
                        <td><?= e($sale['sale_datetime']) ?></td>
                        <td><?= e($sale['cashier_name']) ?></td>
                        <td><?= e($sale['customer_name'] ?: 'Walk-in') ?></td>
                        <td><?= e(money($sale['total_amount'])) ?></td>
                        <td><?= e($sale['payment_method']) ?></td>
                        <td><span class="badge <?= $badge ?>"><?= e($sale['status']) ?></span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="<?= e(base_url('modules/sales/receipt.php?id=' . $sale['id'])) ?>" title="Receipt"><i class="bi bi-receipt"></i></a>
                            <?php if (can_edit_sale($sale)): ?>
                                <a class="btn btn-sm btn-outline-primary" href="<?= e(base_url('modules/sales/create.php?id=' . $sale['id'])) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
                            <?php endif; ?>
                            <?php if (can_void_or_delete_sale($sale)): ?>
                                <form method="post" action="<?= e(base_url('modules/sales/actions.php')) ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $sale['id'] ?>">
                                    <input type="hidden" name="action" value="void">
                                    <input type="hidden" name="reason" value="Voided by Admin">
                                    <button type="submit" class="btn btn-sm btn-outline-warning" data-confirm="Void this sale and restore stock?" title="Void"><i class="bi bi-slash-circle"></i></button>
                                </form>
                                <form method="post" action="<?= e(base_url('modules/sales/actions.php')) ?>" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $sale['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="reason" value="Deleted by Admin">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Delete this sale record and restore stock? This cannot be undone." title="Delete"><i class="bi bi-trash"></i></button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
