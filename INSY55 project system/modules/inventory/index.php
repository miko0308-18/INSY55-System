<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle = page_title(__('Inventory'));
$q = trim((string) get('q', ''));
$status = (string) get('status', 'Active');
$filter = (string) get('filter', '');
$admin = is_admin();
$nearDays = (int) app_config('near_expiry_days');

$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN product_categories c ON c.id = p.category_id
        WHERE 1=1";
$params = [];

if ($status !== 'all') {
    $sql .= ' AND p.status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $sql .= ' AND (p.name LIKE ? OR p.meat_type LIKE ? OR p.barcode LIKE ? OR c.name LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
if ($filter === 'low') {
    $sql .= ' AND p.stock_quantity > 0 AND p.stock_quantity <= p.min_stock_level';
} elseif ($filter === 'out') {
    $sql .= ' AND p.stock_quantity <= 0';
} elseif ($filter === 'near') {
    $sql .= ' AND p.expiration_date IS NOT NULL AND p.expiration_date >= CURDATE() AND p.expiration_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)';
    $params[] = $nearDays;
} elseif ($filter === 'expired') {
    $sql .= ' AND p.expiration_date IS NOT NULL AND p.expiration_date < CURDATE()';
}
$sql .= ' ORDER BY p.name ASC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Inventory')) ?></h1>
        <p class="subtitle">Products, stock levels, and expiry tracking</p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($admin): ?>
            <a href="<?= e(base_url('modules/inventory/form.php')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> <?= e(__('Add Product')) ?></a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2" method="get">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="<?= e(__('Search')) ?> product..." value="<?= e($q) ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <?php foreach (['Active','Inactive','Archived','Deleted','all'] as $s): ?>
                        <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="filter" class="form-select">
                    <option value="">All stock states</option>
                    <option value="low" <?= $filter === 'low' ? 'selected' : '' ?>><?= e(__('Low Stock Items')) ?></option>
                    <option value="out" <?= $filter === 'out' ? 'selected' : '' ?>><?= e(__('Out of Stock')) ?></option>
                    <option value="near" <?= $filter === 'near' ? 'selected' : '' ?>><?= e(__('Near Expiry')) ?></option>
                    <option value="expired" <?= $filter === 'expired' ? 'selected' : '' ?>><?= e(__('Expired')) ?></option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" type="submit"><?= e(__('Search')) ?></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Expiry</th>
                <th><?= e(__('Status')) ?></th>
                <th><?= e(__('Actions')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$products): ?>
                <tr><td colspan="7" class="text-muted p-3"><?= e(__('No records found.')) ?></td></tr>
            <?php else: foreach ($products as $p): ?>
                <?php
                $stockClass = 'badge-soft-success';
                if ((float) $p['stock_quantity'] <= 0) $stockClass = 'badge-soft-danger';
                elseif ((float) $p['stock_quantity'] <= (float) $p['min_stock_level']) $stockClass = 'badge-soft-warning';
                $expBadge = '';
                if (!empty($p['expiration_date'])) {
                    if ($p['expiration_date'] < date('Y-m-d')) $expBadge = 'badge-soft-danger';
                    elseif ($p['expiration_date'] <= date('Y-m-d', strtotime('+' . $nearDays . ' days'))) $expBadge = 'badge-soft-warning';
                }
                ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= e(base_url($p['image_path'] ?: 'assets/images/placeholders/product-placeholder.svg')) ?>" width="36" height="36" alt="">
                            <div>
                                <div class="fw-semibold"><?= e($p['name']) ?></div>
                                <div class="small text-muted"><?= e((string) $p['meat_type']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= e((string) $p['category_name']) ?></td>
                    <td><?= e(money($p['price'])) ?> / <?= e($p['unit']) ?></td>
                    <td><span class="badge <?= $stockClass ?>"><?= e($p['stock_quantity']) ?></span></td>
                    <td><span class="<?= $expBadge ? 'badge ' . $expBadge : '' ?>"><?= e((string) $p['expiration_date']) ?></span></td>
                    <td><?= e($p['status']) ?></td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-success" href="<?= e(base_url('modules/inventory/stock.php?id=' . $p['id'])) ?>"><?= e(__('Add Stock')) ?></a>
                        <?php if ($admin): ?>
                            <a class="btn btn-sm btn-outline-primary" href="<?= e(base_url('modules/inventory/form.php?id=' . $p['id'])) ?>"><?= e(__('Edit')) ?></a>
                            <?php if ($p['status'] !== 'Archived'): ?>
                                <form class="d-inline" method="post" action="<?= e(base_url('modules/inventory/actions.php')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="archive">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button class="btn btn-sm btn-outline-warning" data-confirm="Archive this product?"><?= e(__('Archive')) ?></button>
                                </form>
                            <?php else: ?>
                                <form class="d-inline" method="post" action="<?= e(base_url('modules/inventory/actions.php')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="restore">
                                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                    <button class="btn btn-sm btn-outline-success"><?= e(__('Restore')) ?></button>
                                </form>
                            <?php endif; ?>
                            <form class="d-inline" method="post" action="<?= e(base_url('modules/inventory/actions.php')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" data-confirm="Permanently delete this product?"><?= e(__('Delete')) ?></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
