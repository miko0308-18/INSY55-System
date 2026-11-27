<?php
declare(strict_types=1);

/**
 * In-app notifications. Optional email is disabled by default.
 */
function create_notification(
    string $type,
    string $title,
    string $message,
    string $severity = 'info',
    ?string $targetRole = 'All',
    ?string $module = null,
    ?string $entityType = null,
    $entityId = null,
    ?string $actionUrl = null,
    ?array $specificUserIds = null
): int {
    $pdo = db();
    $stmt = $pdo->prepare(
        'INSERT INTO notifications
        (notification_type, title, message, severity, module, related_entity_type, related_entity_id, target_role, action_url, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $type,
        $title,
        $message,
        $severity,
        $module,
        $entityType,
        $entityId !== null ? (string) $entityId : null,
        $targetRole,
        $actionUrl,
        current_user()['id'] ?? null,
    ]);
    $notificationId = (int) $pdo->lastInsertId();

    if ($specificUserIds !== null) {
        $userIds = $specificUserIds;
    } else {
        $sql = "SELECT id FROM users WHERE status = 'Active'";
        $params = [];
        if ($targetRole === ROLE_ADMIN || $targetRole === ROLE_STAFF) {
            $sql .= ' AND role_id = ?';
            $params[] = $targetRole === ROLE_ADMIN ? ROLE_ADMIN_ID : ROLE_STAFF_ID;
        }
        $userStmt = $pdo->prepare($sql);
        $userStmt->execute($params);
        $userIds = array_column($userStmt->fetchAll(), 'id');
    }

    $link = $pdo->prepare(
        'INSERT IGNORE INTO user_notifications (notification_id, user_id) VALUES (?, ?)'
    );
    foreach ($userIds as $userId) {
        $link->execute([$notificationId, (int) $userId]);
    }

    // Optional email structure (disabled by default; easy to remove later)
    if (app_config('email_notifications_enabled')) {
        send_optional_email_notification($title, $message, $userIds);
    }

    return $notificationId;
}

function send_optional_email_notification(string $title, string $message, array $userIds): void
{
    // Placeholder only — not required for the system to work.
    // Implement PHPMailer/SMTP later if needed.
}

function unread_notification_count(int $userId): int
{
    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM user_notifications
         WHERE user_id = ? AND is_read = 0 AND is_cleared = 0'
    );
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function fetch_user_notifications(int $userId, ?string $filter = null, int $limit = 50): array
{
    $sql = 'SELECT un.*, n.title, n.message, n.severity, n.notification_type, n.action_url, n.created_at AS notified_at
            FROM user_notifications un
            INNER JOIN notifications n ON n.id = un.notification_id
            WHERE un.user_id = ? AND un.is_cleared = 0';
    $params = [$userId];
    if ($filter === 'unread') {
        $sql .= ' AND un.is_read = 0';
    } elseif ($filter === 'read') {
        $sql .= ' AND un.is_read = 1';
    }
    $sql .= ' ORDER BY n.created_at DESC LIMIT ' . (int) $limit;
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function mark_notification_read(int $userId, int $notificationId): void
{
    $stmt = db()->prepare(
        'UPDATE user_notifications SET is_read = 1, read_at = COALESCE(read_at, NOW())
         WHERE user_id = ? AND notification_id = ? AND is_cleared = 0'
    );
    $stmt->execute([$userId, $notificationId]);
}

function mark_all_notifications_read(int $userId): void
{
    $stmt = db()->prepare(
        'UPDATE user_notifications SET is_read = 1, read_at = COALESCE(read_at, NOW())
         WHERE user_id = ? AND is_read = 0 AND is_cleared = 0'
    );
    $stmt->execute([$userId]);
}

function clear_notification(int $userId, int $notificationId): void
{
    $stmt = db()->prepare(
        'UPDATE user_notifications SET is_cleared = 1, cleared_at = NOW()
         WHERE user_id = ? AND notification_id = ?'
    );
    $stmt->execute([$userId, $notificationId]);
}

function clear_read_notifications(int $userId): void
{
    $stmt = db()->prepare(
        'UPDATE user_notifications SET is_cleared = 1, cleared_at = NOW()
         WHERE user_id = ? AND is_read = 1 AND is_cleared = 0'
    );
    $stmt->execute([$userId]);
}

function notification_recently_sent(string $type, string $entityType, $entityId, int $hours = 24): bool
{
    $stmt = db()->prepare(
        'SELECT id FROM notifications
         WHERE notification_type = ? AND related_entity_type = ? AND related_entity_id = ?
           AND created_at >= (NOW() - INTERVAL ? HOUR)
         LIMIT 1'
    );
    $stmt->execute([$type, $entityType, (string) $entityId, $hours]);
    return (bool) $stmt->fetch();
}

function notify_once(
    string $type,
    string $title,
    string $message,
    string $severity,
    ?string $targetRole,
    ?string $module,
    string $entityType,
    $entityId,
    ?string $actionUrl = null
): void {
    if (notification_recently_sent($type, $entityType, $entityId)) {
        return;
    }
    create_notification($type, $title, $message, $severity, $targetRole, $module, $entityType, $entityId, $actionUrl);
}

function check_inventory_alerts(): void
{
    $pdo = db();
    $nearDays = (int) app_config('near_expiry_days');

    $low = $pdo->query(
        "SELECT id, name, stock_quantity, min_stock_level FROM products
         WHERE status = 'Active' AND stock_quantity > 0 AND stock_quantity <= min_stock_level"
    )->fetchAll();
    foreach ($low as $p) {
        notify_once(
            'low_stock',
            'Low stock: ' . $p['name'],
            $p['name'] . ' has only ' . $p['stock_quantity'] . ' left (min ' . $p['min_stock_level'] . ').',
            'warning',
            'All',
            'inventory',
            'product',
            $p['id'],
            'modules/inventory/index.php?q=' . urlencode($p['name'])
        );
    }

    $out = $pdo->query(
        "SELECT id, name FROM products WHERE status = 'Active' AND stock_quantity <= 0"
    )->fetchAll();
    foreach ($out as $p) {
        notify_once(
            'out_of_stock',
            'Out of stock: ' . $p['name'],
            $p['name'] . ' is out of stock.',
            'danger',
            'All',
            'inventory',
            'product',
            $p['id'],
            'modules/inventory/index.php?q=' . urlencode($p['name'])
        );
    }

    $near = $pdo->prepare(
        "SELECT id, name, expiration_date FROM products
         WHERE status = 'Active' AND expiration_date IS NOT NULL
           AND expiration_date >= CURDATE()
           AND expiration_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)"
    );
    $near->execute([$nearDays]);
    foreach ($near->fetchAll() as $p) {
        notify_once(
            'near_expiry',
            'Near expiry: ' . $p['name'],
            $p['name'] . ' expires on ' . $p['expiration_date'] . '.',
            'warning',
            'All',
            'inventory',
            'product',
            $p['id'],
            'modules/inventory/index.php?q=' . urlencode($p['name'])
        );
    }

    $expired = $pdo->query(
        "SELECT id, name, expiration_date FROM products
         WHERE status = 'Active' AND expiration_date IS NOT NULL AND expiration_date < CURDATE()"
    )->fetchAll();
    foreach ($expired as $p) {
        notify_once(
            'expired_product',
            'Expired: ' . $p['name'],
            $p['name'] . ' expired on ' . $p['expiration_date'] . '.',
            'danger',
            'Admin',
            'inventory',
            'product',
            $p['id'],
            'modules/inventory/index.php?q=' . urlencode($p['name'])
        );
    }
}
