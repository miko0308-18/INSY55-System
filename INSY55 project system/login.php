<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('modules/dashboard/index.php');
}

$error = '';

if (request_method() === 'POST') {
    require_csrf();
    $login = trim((string) post('login', ''));
    $password = (string) post('password', '');

    if ($login === '' || $password === '') {
        $error = __('Invalid username or password.');
    } else {
        $stmt = db()->prepare(
            'SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE (u.username = ? OR u.email = ?)
             LIMIT 1'
        );
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $error = __('Invalid username or password.');
        } elseif ($user['status'] !== STATUS_ACTIVE) {
            $error = __('Account is not active.');
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'role_id' => (int) $user['role_id'],
                'role_name' => $user['role_name'],
                'role_slug' => $user['role_slug'],
                'avatar_path' => $user['avatar_path'],
                'language_preference' => $user['language_preference'],
                'is_owner' => (int) $user['is_owner'],
            ];
            $_SESSION['lang'] = $user['language_preference'] ?: 'en';

            $upd = db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
            $upd->execute([(int) $user['id']]);

            log_activity('login', 'auth', 'User logged in', 'user', $user['id']);
            flash('success', __('Welcome back') . ', ' . $user['full_name'] . '!');
            redirect('modules/dashboard/index.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(__('Login')) ?> | <?= e(app_config('name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(base_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body>
<div class="auth-layout">
    <div class="auth-form-panel">
        <div class="auth-card">
            <img class="logo" src="<?= e(base_url('assets/images/placeholders/logo-placeholder.svg')) ?>" alt="[Logo Placeholder]">
            <h1 class="h3 mb-1"><?= e(app_config('business.name')) ?></h1>
            <p class="text-muted mb-4"><?= e(__('Welcome back')) ?>. <?= e(__('Sign in')) ?> to continue.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>
            <?php render_flashes(); ?>

            <form method="post" autocomplete="off">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" for="login"><?= e(__('Username or Email')) ?></label>
                    <input type="text" id="login" name="login" class="form-control" required value="<?= e((string) post('login', '')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password"><?= e(__('Password')) ?></label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Show password" title="Show password">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100"><?= e(__('Sign in')) ?></button>
            </form>

            <div class="mt-4 p-3 border rounded bg-light small">
                <div class="fw-semibold mb-2"><i class="bi bi-person-badge me-1"></i>Demo accounts</div>
                <ul class="mb-0 ps-3">
                    <li><strong>Admin:</strong> <code>admin</code> / <code>Admin@123</code></li>
                    <li><strong>Staff:</strong> <code>staff</code> / <code>Staff@123</code></li>
                    <li><strong>Staff:</strong> <code>staff.maria</code> / <code>Staff@123</code></li>
                </ul>
                <div class="text-muted mt-2">Demo/placeholder credentials from sample data — change passwords after first login.</div>
            </div>
        </div>
    </div>
    <aside class="auth-aside">
        <div>
            <h2 class="h4">Inventory for a modern meatshop</h2>
            <p class="mb-0 opacity-75">Track stock, record sales, and view analytics — [Business Placeholder]</p>
        </div>
    </aside>
</div>
<script>
(function () {
    var toggle = document.getElementById('togglePassword');
    var password = document.getElementById('password');
    if (!toggle || !password) return;
    toggle.addEventListener('click', function () {
        var isHidden = password.getAttribute('type') === 'password';
        password.setAttribute('type', isHidden ? 'text' : 'password');
        var icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.toggle('bi-eye', !isHidden);
            icon.classList.toggle('bi-eye-slash', isHidden);
        }
        toggle.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        toggle.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
    });
})();
</script>
</body>
</html>
