<?php
declare(strict_types=1);
$current = $_SERVER['SCRIPT_NAME'] ?? '';
$admin = is_admin();

$links = [
    ['label' => __('Dashboard'), 'href' => 'modules/dashboard/index.php', 'icon' => 'bi-speedometer2', 'admin_only' => false],
    ['label' => __('Analytics'), 'href' => 'modules/analytics/index.php', 'icon' => 'bi-graph-up', 'admin_only' => true],
    ['label' => __('Inventory'), 'href' => 'modules/inventory/index.php', 'icon' => 'bi-box-seam', 'admin_only' => false],
    ['label' => __('Sales'), 'href' => 'modules/sales/index.php', 'icon' => 'bi-cart3', 'admin_only' => false],
    ['label' => __('Notifications'), 'href' => 'modules/notifications/index.php', 'icon' => 'bi-bell', 'admin_only' => false],
    ['label' => __('Reports'), 'href' => 'modules/reports/sales.php', 'icon' => 'bi-file-earmark-bar-graph', 'admin_only' => true],
    ['label' => __('Users'), 'href' => 'modules/users/index.php', 'icon' => 'bi-people', 'admin_only' => true],
    ['label' => __('Backup & Restore'), 'href' => 'modules/backup_restore/index.php', 'icon' => 'bi-cloud-arrow-up-down', 'admin_only' => true],
    ['label' => __('Settings'), 'href' => 'modules/settings/profile.php', 'icon' => 'bi-gear', 'admin_only' => false],
];
?>
<aside class="admin-sidebar" id="adminSidebar">
    <nav class="nav flex-column py-3">
        <?php foreach ($links as $link): ?>
            <?php if ($link['admin_only'] && !$admin) continue; ?>
            <?php
            $url = base_url($link['href']);
            $active = str_contains($current, '/' . dirname($link['href'])) || str_contains($current, '/' . $link['href']);
            if ($link['href'] === 'modules/settings/profile.php') {
                $active = str_contains($current, '/modules/settings/');
            }
            if ($link['href'] === 'modules/reports/sales.php') {
                $active = str_contains($current, '/modules/reports/');
            }
            ?>
            <a class="nav-link <?= $active ? 'active' : '' ?>" href="<?= e($url) ?>">
                <i class="bi <?= e($link['icon']) ?>"></i>
                <span><?= e($link['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
