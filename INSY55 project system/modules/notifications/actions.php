<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';

if (request_method() !== 'POST') {
    redirect('modules/notifications/index.php');
}

require_csrf();
$action = (string) post('action', '');
$id = (int) post('id', 0);
$userId = (int) current_user()['id'];

try {
    switch ($action) {
        case 'mark_read':
            if ($id > 0) mark_notification_read($userId, $id);
            flash('success', 'Notification marked as read.');
            break;
        case 'mark_all_read':
            mark_all_notifications_read($userId);
            flash('success', 'All notifications marked as read.');
            break;
        case 'clear':
            if ($id > 0) clear_notification($userId, $id);
            flash('success', 'Notification cleared.');
            break;
        case 'clear_read':
            clear_read_notifications($userId);
            flash('success', 'Read notifications cleared.');
            break;
        default:
            flash('error', 'Invalid notification action.');
    }
} catch (Throwable $e) {
    flash('error', $e->getMessage());
}

$back = (string) post('back', '');
if ($back === '') {
    $back = 'modules/notifications/index.php';
}
redirect($back);
