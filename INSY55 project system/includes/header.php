<?php
declare(strict_types=1);
$user = current_user();
$unread = $user ? unread_notification_count((int) $user['id']) : 0;
$pageTitle = $pageTitle ?? app_config('name');
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="admin-app">
    <header class="admin-header">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <div class="brand-lockup">
            <img src="<?= e(base_url(app_config('business.logo'))) ?>" alt="[Logo Placeholder]">
            <div>
                <div class="brand-name"><?= e(app_config('business.name')) ?></div>
                <div class="brand-sub">[Business Placeholder]</div>
            </div>
        </div>
        <div class="ms-auto d-flex align-items-center gap-2">
            <form method="get" action="<?= e(base_url('modules/settings/set_language.php')) ?>" class="d-flex align-items-center gap-1">
                <label class="small text-muted mb-0 d-none d-md-inline"><?= e(__('Language')) ?></label>
                <select name="lang" class="form-select form-select-sm" onchange="this.form.submit()" style="width:auto">
                    <option value="en" <?= current_lang() === 'en' ? 'selected' : '' ?>><?= e(__('English')) ?></option>
                    <option value="fil" <?= current_lang() === 'fil' ? 'selected' : '' ?>><?= e(__('Filipino')) ?></option>
                </select>
            </form>
            <a href="<?= e(base_url('modules/notifications/index.php')) ?>" class="btn btn-sm btn-outline-secondary position-relative" title="<?= e(__('Notifications')) ?>">
                <i class="bi bi-bell"></i>
                <?php if ($unread > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= (int) $unread ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?= e(base_url($user['avatar_path'] ?? 'assets/images/placeholders/avatar-placeholder.svg')) ?>" alt="" width="22" height="22" class="rounded-circle me-1">
                    <?= e($user['full_name'] ?? '') ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= e(base_url('modules/settings/profile.php')) ?>"><?= e(__('Profile')) ?></a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= e(base_url('logout.php')) ?>"><?= e(__('Logout')) ?></a></li>
                </ul>
            </div>
        </div>
    </header>
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
<?php require __DIR__ . '/sidebar.php'; ?>
    <main class="admin-main">
        <?php render_flashes(); ?>
