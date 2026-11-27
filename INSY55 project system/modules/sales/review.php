<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/sales_helper.php';

$draft = $_SESSION['sale_draft'] ?? null;
if (!$draft || empty($draft['items'])) {
    flash('error', 'Nothing to review. Please create a sale first.');
    redirect('modules/sales/create.php');
}

$subtotal = array_sum(array_column($draft['items'], 'line_subtotal'));
$totals = calculate_sale_totals($subtotal, (float) $draft['discount_percent'], (float) $draft['vat_rate_percent']);
$change = round((float) $draft['amount_paid'] - $totals['total_amount'], 2);
$pageTitle = page_title(__('Review Sale'));

if (request_method() === 'POST') {
    require_csrf();
    $action = (string) post('action', 'save');
    if ($action === 'edit') {
        $editId = $_SESSION['sale_draft_edit_id'] ?? null;
        redirect('modules/sales/create.php' . ($editId ? ('?id=' . (int) $editId) : ''));
    }

    try {
        $existingId = $_SESSION['sale_draft_edit_id'] ?? null;
        $saleId = save_sale_transaction($draft, $existingId ? (int) $existingId : null);
        unset($_SESSION['sale_draft'], $_SESSION['sale_draft_edit_id']);
        flash('success', 'Sale saved successfully.');
        redirect('modules/sales/receipt.php?id=' . $saleId);
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        redirect('modules/sales/create.php');
    }
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Review Sale')) ?></h1>
        <p class="subtitle">Confirm details before the sale becomes official</p>
    </div>
</div>

<div class="card" style="max-width:760px">
    <div class="card-body">
        <p><strong>Customer:</strong> <?= e($draft['customer_name'] ?: 'Walk-in') ?></p>
        <p><strong>Payment:</strong> <?= e($draft['payment_method']) ?></p>
        <div class="table-responsive mb-3">
            <table class="table table-sm">
                <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Line</th></tr></thead>
                <tbody>
                <?php foreach ($draft['items'] as $item): ?>
                    <tr>
                        <td><?= e($item['product_name_snapshot']) ?></td>
                        <td><?= e($item['quantity'] . ' ' . $item['unit_snapshot']) ?></td>
                        <td><?= e(money($item['unit_price'])) ?></td>
                        <td><?= e(money($item['line_subtotal'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="d-flex justify-content-between"><span>Subtotal</span><strong><?= e(money($totals['subtotal'])) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Discount (<?= e($totals['discount_percent']) ?>%)</span><strong><?= e(money($totals['discount_amount'])) ?></strong></div>
                <div class="d-flex justify-content-between"><span>VAT (<?= e($totals['vat_rate_percent']) ?>%)</span><strong><?= e(money($totals['vat_amount'])) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Total</span><strong><?= e(money($totals['total_amount'])) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Amount paid</span><strong><?= e(money($draft['amount_paid'])) ?></strong></div>
                <div class="d-flex justify-content-between"><span>Change</span><strong><?= e(money($change)) ?></strong></div>
            </div>
        </div>
        <form method="post" class="d-flex gap-2 mt-4 no-print">
            <?= csrf_field() ?>
            <button type="submit" name="action" value="edit" class="btn btn-outline-secondary"><?= e(__('Make Changes')) ?></button>
            <button type="submit" name="action" value="save" class="btn btn-primary" data-confirm="Confirm and save this sale?"><?= e(__('Confirm & Save')) ?></button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
