<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle = page_title(__('Profile'));
$userId = (int) current_user()['id'];
$pdo = db();

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (request_method() === 'POST') {
    require_csrf();
    $fullName = trim((string) post('full_name'));
    $email = trim((string) post('email'));
    $phone = trim((string) post('phone'));
    $language = (string) post('language_preference', 'en');
    $password = (string) post('password', '');
    $passwordConfirm = (string) post('password_confirm', '');

    $errors = [];
    if ($fullName === '') $errors[] = 'Full name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (!in_array($language, ['en', 'fil'], true)) $language = 'en';
    if ($password !== '' && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $passwordConfirm) $errors[] = 'Password confirmation does not match.';

    $check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $check->execute([$email, $userId]);
    if ($check->fetch()) $errors[] = 'Email is already in use.';

    if (!$errors) {
        if ($password !== '') {
            $upd = $pdo->prepare(
                'UPDATE users SET full_name=?, email=?, phone=?, language_preference=?, password_hash=?, updated_by=?, updated_at=NOW() WHERE id=?'
            );
            $upd->execute([$fullName, $email, $phone ?: null, $language, password_hash($password, PASSWORD_DEFAULT), $userId, $userId]);
        } else {
            $upd = $pdo->prepare(
                'UPDATE users SET full_name=?, email=?, phone=?, language_preference=?, updated_by=?, updated_at=NOW() WHERE id=?'
            );
            $upd->execute([$fullName, $email, $phone ?: null, $language, $userId, $userId]);
        }

        $_SESSION['user']['full_name'] = $fullName;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['language_preference'] = $language;
        $_SESSION['lang'] = $language;

        log_activity('profile_update', 'settings', 'Updated own profile', 'user', $userId);
        flash('success', __('Profile updated successfully.'));
        redirect('modules/settings/profile.php');
    }

    foreach ($errors as $err) {
        flash('error', $err);
    }
    $user = array_merge($user, [
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'language_preference' => $language,
    ]);
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Profile')) ?></h1>
        <p class="subtitle">Edit your account details</p>
    </div>
</div>

<div class="card" style="max-width:640px">
    <div class="card-body">
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Full name</label>
                <input type="text" name="full_name" class="form-control" required value="<?= e($user['full_name']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?= e($user['email']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?= e((string) $user['phone']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label"><?= e(__('Language')) ?></label>
                <select name="language_preference" class="form-select">
                    <option value="en" <?= $user['language_preference'] === 'en' ? 'selected' : '' ?>><?= e(__('English')) ?></option>
                    <option value="fil" <?= $user['language_preference'] === 'fil' ? 'selected' : '' ?>><?= e(__('Filipino')) ?></option>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">New password (optional)</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Confirm password</label>
                    <input type="password" name="password_confirm" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><?= e(__('Save')) ?></button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
