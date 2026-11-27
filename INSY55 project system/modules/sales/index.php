<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle = page_title(__('Sales'));
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Sales')) ?></h1>
        <p class="subtitle">Record sales and review history</p>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-6">
        <a class="card text-decoration-none h-100" href="<?= e(base_url('modules/sales/create.php')) ?>">
            <div class="card-body">
                <div class="icon-box mb-3"><i class="bi bi-plus-circle"></i></div>
                <h2 class="h5"><?= e(__('Record Sale')) ?></h2>
                <p class="text-muted mb-0">Select products, review totals, then confirm before saving.</p>
            </div>
        </a>
    </div>
    <div class="col-md-6">
        <a class="card text-decoration-none h-100" href="<?= e(base_url('modules/sales/history.php')) ?>">
            <div class="card-body">
                <div class="icon-box mb-3"><i class="bi bi-clock-history"></i></div>
                <h2 class="h5"><?= e(__('Sales History')) ?></h2>
                <p class="text-muted mb-0">View past receipts, edit corrections, or void/delete (Admin).</p>
            </div>
        </a>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
