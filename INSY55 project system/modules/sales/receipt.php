<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/sales_helper.php';

$id = (int) get('id', 0);
$sale = get_sale_with_items($id);
if (!$sale) {
    flash('error', 'Receipt not found.');
    redirect('modules/sales/history.php');
}

$pageTitle = page_title('Receipt ' . $sale['receipt_number']);
$business = app_config('business');
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header no-print">
    <div>
        <h1>Receipt</h1>
        <p class="subtitle"><?= e($sale['receipt_number']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" onclick="window.print()"><i class="bi bi-printer"></i> <?= e(__('Print Receipt')) ?> / PDF</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_url('modules/sales/history.php')) ?>"><?= e(__('Sales History')) ?></a>
    </div>
</div>

<div class="receipt-paper">
    <div class="text-center mb-2">
        <img src="<?= e(base_url('assets/images/placeholders/receipt-placeholder.svg')) ?>" alt="[Receipt Placeholder]" width="64" height="64">
    </div>
    <div class="shop-name"><?= e($business['name']) ?></div>
    <div class="meta">
        <?= e($business['address']) ?><br>
        <?= e($business['phone']) ?> · <?= e($business['email']) ?><br>
        [Logo / Business details are placeholders]
    </div>
    <hr>
    <div class="small">
        <div><strong>Receipt #:</strong> <?= e($sale['receipt_number']) ?></div>
        <div><strong>Date:</strong> <?= e($sale['sale_datetime']) ?></div>
        <div><strong>Cashier:</strong> <?= e($sale['cashier_name']) ?></div>
        <div><strong>Customer:</strong> <?= e($sale['customer_name'] ?: 'Walk-in') ?></div>
        <div><strong>Status:</strong> <?= e($sale['status']) ?></div>
    </div>
    <hr>
    <table class="table table-sm mb-2">
        <thead><tr><th>Item</th><th>Qty</th><th>Amt</th></tr></thead>
        <tbody>
        <?php foreach ($sale['items'] as $item): ?>
            <tr>
                <td><?= e($item['product_name_snapshot']) ?></td>
                <td><?= e($item['quantity'] . $item['unit_snapshot']) ?></td>
                <td><?= e(money($item['line_subtotal'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="small">
        <div class="d-flex justify-content-between"><span>Subtotal</span><span><?= e(money($sale['subtotal'])) ?></span></div>
        <div class="d-flex justify-content-between"><span>Discount</span><span><?= e(money($sale['discount_amount'])) ?></span></div>
        <div class="d-flex justify-content-between"><span>VAT</span><span><?= e(money($sale['vat_amount'])) ?></span></div>
        <div class="d-flex justify-content-between fw-bold"><span>Total</span><span><?= e(money($sale['total_amount'])) ?></span></div>
        <div class="d-flex justify-content-between"><span><?= e($sale['payment_method']) ?> paid</span><span><?= e(money($sale['amount_paid'])) ?></span></div>
        <div class="d-flex justify-content-between"><span>Change</span><span><?= e(money($sale['change_due'])) ?></span></div>
    </div>
    <hr>
    <div class="text-center small"><?= e($business['receipt_footer']) ?></div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
