<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/sales_helper.php';

$editId = (int) get('id', 0);
$editing = null;
if ($editId > 0) {
    $editing = get_sale_with_items($editId);
    if (!$editing || !can_edit_sale($editing)) {
        flash('error', 'You cannot edit this sale.');
        redirect('modules/sales/history.php');
    }
}

$pageTitle = page_title($editing ? 'Edit Sale' : __('Record Sale'));
$products = fetch_active_products_for_sale();
$vatDefault = (float) setting('default_vat_percent', DEFAULT_VAT_RATE_PERCENT);
$extraScripts = ['assets/js/sales.js'];

if (request_method() === 'POST') {
    require_csrf();
    $step = (string) post('step', 'review');

    try {
        $items = parse_sale_items_from_post();
        $payload = [
            'customer_name' => trim((string) post('customer_name')) ?: null,
            'discount_percent' => (float) post('discount_percent', 0),
            'vat_rate_percent' => (float) post('vat_rate_percent', $vatDefault),
            'payment_method' => (string) post('payment_method', PAYMENT_CASH),
            'amount_paid' => (float) post('amount_paid', 0),
            'remarks' => trim((string) post('remarks')) ?: null,
            'items' => $items,
        ];
        if (!in_array($payload['payment_method'], [PAYMENT_CASH, PAYMENT_GCASH], true)) {
            $payload['payment_method'] = PAYMENT_CASH;
        }

        if ($step === 'review') {
            $_SESSION['sale_draft'] = $payload;
            $_SESSION['sale_draft_edit_id'] = $editId ?: null;
            redirect('modules/sales/review.php');
        }

        if ($step === 'save') {
            if (empty($_SESSION['sale_draft'])) {
                throw new RuntimeException('Sale draft expired. Please re-enter the sale.');
            }
            $draft = $_SESSION['sale_draft'];
            $existingId = $_SESSION['sale_draft_edit_id'] ?? null;
            $saleId = save_sale_transaction($draft, $existingId ? (int) $existingId : null);
            unset($_SESSION['sale_draft'], $_SESSION['sale_draft_edit_id']);
            flash('success', 'Sale saved successfully.');
            redirect('modules/sales/receipt.php?id=' . $saleId);
        }
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
    }
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e($editing ? 'Edit Sale' : __('Record Sale')) ?></h1>
        <p class="subtitle">Select products, enter quantities, then review before saving</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(base_url('modules/sales/history.php')) ?>"><?= e(__('Sales History')) ?></a>
</div>

<form method="post" id="saleForm">
    <?= csrf_field() ?>
    <input type="hidden" name="step" value="review">

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h2 class="h6 mb-0">Line items</h2>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addLineBtn"><i class="bi bi-plus"></i> Add item</button>
                    </div>
                    <div id="saleLines">
                        <?php if ($editing): foreach ($editing['items'] as $item): ?>
                            <div class="row g-2 align-items-end sale-line mb-2">
                                <div class="col-md-7">
                                    <label class="form-label">Product</label>
                                    <select name="product_id[]" class="form-select product-select" required>
                                        <option value="">Select product</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= (int) $p['id'] ?>"
                                                data-price="<?= e($p['price']) ?>"
                                                data-stock="<?= e($p['stock_quantity']) ?>"
                                                data-unit="<?= e($p['unit']) ?>"
                                                <?= (int) $item['product_id'] === (int) $p['id'] ? 'selected' : '' ?>>
                                                <?= e($p['name'] . ' (' . $p['stock_quantity'] . ' ' . $p['unit'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Qty</label>
                                    <input type="number" step="0.001" min="0.001" name="quantity[]" class="form-control qty-input" value="<?= e($item['quantity']) ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger w-100 remove-line">&times;</button>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                            <div class="row g-2 align-items-end sale-line mb-2">
                                <div class="col-md-7">
                                    <label class="form-label">Product</label>
                                    <select name="product_id[]" class="form-select product-select" required>
                                        <option value="">Select product</option>
                                        <?php foreach ($products as $p): ?>
                                            <option value="<?= (int) $p['id'] ?>" data-price="<?= e($p['price']) ?>" data-stock="<?= e($p['stock_quantity']) ?>" data-unit="<?= e($p['unit']) ?>">
                                                <?= e($p['name'] . ' (' . $p['stock_quantity'] . ' ' . $p['unit'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Qty</label>
                                    <input type="number" step="0.001" min="0.001" name="quantity[]" class="form-control qty-input" value="1" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger w-100 remove-line">&times;</button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Customer name (optional)</label>
                        <input type="text" name="customer_name" class="form-control" value="<?= e((string) ($editing['customer_name'] ?? '')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount %</label>
                        <input type="number" step="0.01" min="0" max="100" name="discount_percent" id="discountPercent" class="form-control" value="<?= e((string) ($editing['discount_percent'] ?? 0)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">VAT %</label>
                        <input type="number" step="0.01" min="0" name="vat_rate_percent" id="vatPercent" class="form-control" value="<?= e((string) ($editing['vat_rate_percent'] ?? $vatDefault)) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment method</label>
                        <select name="payment_method" class="form-select">
                            <option value="Cash" <?= ($editing['payment_method'] ?? '') === 'Cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="GCash" <?= ($editing['payment_method'] ?? '') === 'GCash' ? 'selected' : '' ?>>GCash</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount paid</label>
                        <input type="number" step="0.01" min="0" name="amount_paid" id="amountPaid" class="form-control" value="<?= e((string) ($editing['amount_paid'] ?? '')) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" value="<?= e((string) ($editing['remarks'] ?? '')) ?>">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><strong id="subtotalDisplay">₱0.00</strong></div>
                    <div class="d-flex justify-content-between"><span>Discount</span><strong id="discountDisplay">₱0.00</strong></div>
                    <div class="d-flex justify-content-between"><span>VAT</span><strong id="vatDisplay">₱0.00</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Total</span><strong id="totalDisplay">₱0.00</strong></div>
                    <div class="d-flex justify-content-between mb-3"><span>Change</span><strong id="changeDisplay">₱0.00</strong></div>
                    <button type="submit" class="btn btn-primary w-100"><?= e(__('Review Sale')) ?></button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="saleLineTemplate">
    <div class="row g-2 align-items-end sale-line mb-2">
        <div class="col-md-7">
            <label class="form-label">Product</label>
            <select name="product_id[]" class="form-select product-select" required>
                <option value="">Select product</option>
                <?php foreach ($products as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" data-price="<?= e($p['price']) ?>" data-stock="<?= e($p['stock_quantity']) ?>" data-unit="<?= e($p['unit']) ?>">
                        <?= e($p['name'] . ' (' . $p['stock_quantity'] . ' ' . $p['unit'] . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Qty</label>
            <input type="number" step="0.001" min="0.001" name="quantity[]" class="form-control qty-input" value="1" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger w-100 remove-line">&times;</button>
        </div>
    </div>
</template>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
