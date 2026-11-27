<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/sales_helper.php';

if (request_method() !== 'POST') {
    redirect('modules/sales/history.php');
}

require_csrf();
$action = (string) post('action', '');
$id = (int) post('id', 0);
$reason = trim((string) post('reason', ''));

try {
    if (!in_array($action, ['void', 'delete'], true) || $id <= 0) {
        throw new RuntimeException('Invalid action.');
    }
    void_or_delete_sale($id, $action, $reason);
    flash('success', $action === 'void' ? 'Sale voided and stock restored.' : 'Sale deleted and stock restored.');
} catch (Throwable $e) {
    flash('error', $e->getMessage());
}

redirect('modules/sales/history.php');
