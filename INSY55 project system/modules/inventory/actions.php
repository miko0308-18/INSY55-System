<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_admin();

if (request_method() !== 'POST') {
    redirect('modules/inventory/index.php');
}
require_csrf();

$id = (int) post('id', 0);
$action = (string) post('action', '');
$pdo = db();

$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    flash('error', 'Product not found.');
    redirect('modules/inventory/index.php');
}

if ($action === 'archive') {
    $pdo->prepare("UPDATE products SET status='Archived', archived_at=NOW(), updated_by=?, updated_at=NOW() WHERE id=?")
        ->execute([current_user()['id'], $id]);
    log_activity('product_archive', 'inventory', 'Archived product', 'product', $id);
    flash('success', 'Product archived.');
} elseif ($action === 'restore') {
    $pdo->prepare("UPDATE products SET status='Active', archived_at=NULL, deleted_at=NULL, updated_by=?, updated_at=NOW() WHERE id=?")
        ->execute([current_user()['id'], $id]);
    log_activity('product_restore', 'inventory', 'Restored product', 'product', $id);
    flash('success', 'Product restored.');
} elseif ($action === 'delete') {
    $pdo->prepare("UPDATE products SET status='Deleted', deleted_at=NOW(), updated_by=?, updated_at=NOW() WHERE id=?")
        ->execute([current_user()['id'], $id]);
    log_activity('product_delete', 'inventory', 'Deleted product', 'product', $id);
    flash('success', 'Product deleted.');
}

redirect('modules/inventory/index.php');
