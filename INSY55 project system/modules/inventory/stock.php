<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';

$id = (int) get('id', 0);
$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status IN ('Active','Inactive') LIMIT 1");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    flash('error', 'Product not found.');
    redirect('modules/inventory/index.php');
}

$pageTitle = page_title(__('Add Stock'));

if (request_method() === 'POST') {
    require_csrf();
    $qty = (float) post('quantity', 0);
    $remarks = trim((string) post('remarks', 'Manual stock in'));

    if ($qty <= 0) {
        flash('error', 'Quantity must be greater than zero.');
    } else {
        $pdo->beginTransaction();
        try {
            $before = (float) $product['stock_quantity'];
            $after = $before + $qty;
            $pdo->prepare('UPDATE products SET stock_quantity = ?, updated_by = ?, updated_at = NOW() WHERE id = ?')
                ->execute([$after, current_user()['id'], $id]);
            $pdo->prepare(
                'INSERT INTO stock_movements (product_id, movement_type, quantity_change, quantity_before, quantity_after, reference_type, remarks, performed_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([$id, 'stock_in', $qty, $before, $after, 'manual', $remarks, current_user()['id']]);
            $pdo->commit();
            log_activity('stock_in', 'inventory', 'Added stock to ' . $product['name'], 'product', $id, ['before' => $before], ['after' => $after, 'qty' => $qty]);
            flash('success', 'Stock added successfully.');
            redirect('modules/inventory/index.php');
        } catch (Throwable $e) {
            $pdo->rollBack();
            flash('error', 'Failed to add stock.');
        }
    }
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Add Stock')) ?></h1>
        <p class="subtitle"><?= e($product['name']) ?> · current <?= e($product['stock_quantity'] . ' ' . $product['unit']) ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(base_url('modules/inventory/index.php')) ?>"><?= e(__('Cancel')) ?></a>
</div>

<div class="card" style="max-width:520px">
    <div class="card-body">
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Quantity to add (<?= e($product['unit']) ?>)</label>
                <input type="number" step="0.001" min="0.001" name="quantity" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Remarks</label>
                <input type="text" name="remarks" class="form-control" value="Restock delivery">
            </div>
            <button type="submit" class="btn btn-primary"><?= e(__('Save')) ?></button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
