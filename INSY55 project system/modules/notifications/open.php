<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';

$id = (int) get('id', 0);
$userId = (int) current_user()['id'];

if ($id <= 0) {
    redirect('modules/notifications/index.php');
}

// Mark read when opening
try {
    mark_notification_read($userId, $id);
} catch (Throwable $e) {
    // ignore
}

// Find action URL for this user notification
$stmt = db()->prepare(
    'SELECT n.action_url
     FROM user_notifications un
     INNER JOIN notifications n ON n.id = un.notification_id
     WHERE un.user_id = ? AND un.notification_id = ? AND un.is_cleared = 0
     LIMIT 1'
);
$stmt->execute([$userId, $id]);
$row = $stmt->fetch();

$target = $row['action_url'] ?? null;
if ($target) {
    // action_url stored as relative app path
    redirect($target);
}

redirect('modules/notifications/index.php');
