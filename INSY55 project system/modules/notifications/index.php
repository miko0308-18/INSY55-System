<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle = page_title(__('Notifications'));
$userId = (int) current_user()['id'];
$filter = (string) get('filter', 'all');
if (!in_array($filter, ['all', 'unread', 'read'], true)) {
    $filter = 'all';
}

$notifications = fetch_user_notifications($userId, $filter === 'all' ? null : $filter, 100);

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Notifications')) ?></h1>
        <p class="subtitle">Role-specific in-app alerts and notification history</p>
    </div>
    <div class="d-flex gap-2">
        <form method="post" action="<?= e(base_url('modules/notifications/actions.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn btn-outline-primary btn-sm"><?= e(__('Mark as read')) ?> (All)</button>
        </form>
        <form method="post" action="<?= e(base_url('modules/notifications/actions.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="clear_read">
            <button type="submit" class="btn btn-outline-secondary btn-sm" data-confirm="Clear all read notifications?"><?= e(__('Clear')) ?> Read</button>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body d-flex gap-2 flex-wrap">
        <a class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= e(base_url('modules/notifications/index.php?filter=all')) ?>">All</a>
        <a class="btn btn-sm <?= $filter === 'unread' ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= e(base_url('modules/notifications/index.php?filter=unread')) ?>">Unread</a>
        <a class="btn btn-sm <?= $filter === 'read' ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= e(base_url('modules/notifications/index.php?filter=read')) ?>">Read</a>
    </div>
</div>

<div class="card">
    <div class="list-group list-group-flush">
        <?php if (!$notifications): ?>
            <div class="p-4 text-center text-muted"><?= e(__('No records found.')) ?></div>
        <?php endif; ?>
        <?php foreach ($notifications as $n): ?>
            <?php
            $severity = $n['severity'] ?? 'info';
            $badge = 'bg-secondary';
            if ($severity === 'success') $badge = 'bg-success';
            elseif ($severity === 'warning') $badge = 'bg-warning text-dark';
            elseif ($severity === 'danger') $badge = 'bg-danger';
            elseif ($severity === 'info') $badge = 'bg-info';
            $isRead = (int) ($n['is_read'] ?? 0) === 1;
            ?>
            <div class="list-group-item <?= $isRead ? '' : 'bg-light' ?>">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge <?= $badge ?>"><?= e(ucfirst($severity)) ?></span>
                            <strong><?= e($n['title']) ?></strong>
                            <?php if (!$isRead): ?><span class="badge bg-primary">New</span><?php endif; ?>
                        </div>
                        <div class="text-muted small mb-1"><?= e($n['message']) ?></div>
                        <div class="text-muted small"><?= e($n['notified_at'] ?? '') ?> · <?= e($n['notification_type'] ?? '') ?></div>
                        <?php if (!empty($n['action_url'])): ?>
                            <a class="small" href="<?= e(base_url('modules/notifications/open.php?id=' . (int) $n['notification_id'])) ?>">Open alert</a>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <?php if (!$isRead): ?>
                            <form method="post" action="<?= e(base_url('modules/notifications/actions.php')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="id" value="<?= (int) $n['notification_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary"><?= e(__('Mark as read')) ?></button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= e(base_url('modules/notifications/actions.php')) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="clear">
                            <input type="hidden" name="id" value="<?= (int) $n['notification_id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary"><?= e(__('Clear')) ?></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<p class="text-muted small mt-3 mb-0">Optional email notifications: <?= app_config('email_notifications_enabled') ? 'enabled (placeholder)' : 'disabled (placeholder structure ready)' ?>. In-app notifications are the primary system.</p>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
