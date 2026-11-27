<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_admin();

if (request_method() !== 'POST') {
    redirect('modules/users/index.php');
}
require_csrf();

$id = (int) post('id', 0);
$action = (string) post('action', '');
$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role_id = ? LIMIT 1");
$stmt->execute([$id, ROLE_STAFF_ID]);
$user = $stmt->fetch();
if (!$user) {
    flash('error', 'Staff account not found.');
    redirect('modules/users/index.php');
}

if ($action === 'archive') {
    $pdo->prepare("UPDATE users SET status='Archived', archived_at=NOW(), updated_by=?, updated_at=NOW() WHERE id=?")
        ->execute([current_user()['id'], $id]);
    create_notification('staff_account_updated', 'Staff archived', $user['full_name'] . ' was archived.', 'warning', 'Admin', 'users', 'user', $id);
    log_activity('staff_archive', 'users', 'Archived staff', 'user', $id);
    flash('success', 'Staff archived.');
} elseif ($action === 'restore') {
    $pdo->prepare("UPDATE users SET status='Active', archived_at=NULL, deleted_at=NULL, updated_by=?, updated_at=NOW() WHERE id=?")
        ->execute([current_user()['id'], $id]);
    create_notification('staff_account_updated', 'Staff restored', $user['full_name'] . ' was restored.', 'success', 'Admin', 'users', 'user', $id);
    log_activity('staff_restore', 'users', 'Restored staff', 'user', $id);
    flash('success', 'Staff restored.');
} elseif ($action === 'delete') {
    $pdo->prepare("UPDATE users SET status='Deleted', deleted_at=NOW(), updated_by=?, updated_at=NOW() WHERE id=?")
        ->execute([current_user()['id'], $id]);
    create_notification('staff_account_deleted', 'Staff deleted', $user['full_name'] . ' was deleted.', 'danger', 'Admin', 'users', 'user', $id);
    log_activity('staff_delete', 'users', 'Deleted staff', 'user', $id);
    flash('success', 'Staff marked as deleted.');
}

redirect('modules/users/index.php');
