<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_admin();

$id = (int) get('id', 0);
$isEdit = $id > 0;
$pageTitle = page_title($isEdit ? 'Edit Staff' : 'Add Staff');
$pdo = db();

$user = [
    'username' => '',
    'email' => '',
    'full_name' => '',
    'phone' => '',
    'language_preference' => 'en',
    'status' => 'Active',
];

if ($isEdit) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role_id = ? LIMIT 1");
    $stmt->execute([$id, ROLE_STAFF_ID]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('error', 'Staff account not found.');
        redirect('modules/users/index.php');
    }
    $user = $found;
}

if (request_method() === 'POST') {
    require_csrf();
    $data = [
        'username' => trim((string) post('username')),
        'email' => trim((string) post('email')),
        'full_name' => trim((string) post('full_name')),
        'phone' => trim((string) post('phone')),
        'language_preference' => (string) post('language_preference', 'en'),
        'status' => (string) post('status', 'Active'),
    ];
    $password = (string) post('password', '');

    $errors = [];
    if ($data['username'] === '') $errors[] = 'Username is required.';
    if ($data['full_name'] === '') $errors[] = 'Full name is required.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (!$isEdit && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($isEdit && $password !== '' && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    $dup = $pdo->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1');
    $dup->execute([$data['username'], $data['email'], $id]);
    if ($dup->fetch()) $errors[] = 'Username or email already exists.';

    if (!$errors) {
        if ($isEdit) {
            if ($password !== '') {
                $sql = 'UPDATE users SET username=?, email=?, full_name=?, phone=?, language_preference=?, status=?, password_hash=?, updated_by=?, updated_at=NOW() WHERE id=? AND role_id=?';
                $params = [$data['username'], $data['email'], $data['full_name'], $data['phone'] ?: null, $data['language_preference'], $data['status'], password_hash($password, PASSWORD_DEFAULT), current_user()['id'], $id, ROLE_STAFF_ID];
            } else {
                $sql = 'UPDATE users SET username=?, email=?, full_name=?, phone=?, language_preference=?, status=?, updated_by=?, updated_at=NOW() WHERE id=? AND role_id=?';
                $params = [$data['username'], $data['email'], $data['full_name'], $data['phone'] ?: null, $data['language_preference'], $data['status'], current_user()['id'], $id, ROLE_STAFF_ID];
            }
            $pdo->prepare($sql)->execute($params);
            create_notification('staff_account_updated', 'Staff updated', $data['full_name'] . ' was updated.', 'info', 'Admin', 'users', 'user', $id, 'modules/users/index.php');
            log_activity('staff_update', 'users', 'Updated staff account', 'user', $id, null, $data);
            flash('success', 'Staff account updated.');
        } else {
            $sql = 'INSERT INTO users (role_id, username, email, password_hash, full_name, phone, language_preference, status, created_by, updated_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $pdo->prepare($sql)->execute([
                ROLE_STAFF_ID,
                $data['username'],
                $data['email'],
                password_hash($password, PASSWORD_DEFAULT),
                $data['full_name'],
                $data['phone'] ?: null,
                $data['language_preference'],
                $data['status'],
                current_user()['id'],
                current_user()['id'],
            ]);
            $newId = (int) $pdo->lastInsertId();
            create_notification('staff_account_created', 'New staff account', $data['full_name'] . ' was created.', 'success', 'Admin', 'users', 'user', $newId, 'modules/users/index.php');
            log_activity('staff_create', 'users', 'Created staff account', 'user', $newId, null, $data);
            flash('success', 'Staff account created.');
        }
        redirect('modules/users/index.php');
    }

    foreach ($errors as $err) flash('error', $err);
    $user = array_merge($user, $data);
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e($isEdit ? 'Edit Staff' : 'Add Staff') ?></h1>
        <p class="subtitle">Admin account management</p>
    </div>
    <a href="<?= e(base_url('modules/users/index.php')) ?>" class="btn btn-outline-secondary"><?= e(__('Cancel')) ?></a>
</div>

<div class="card" style="max-width:720px">
    <div class="card-body">
        <form method="post">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full name</label>
                    <input type="text" name="full_name" class="form-control" required value="<?= e($user['full_name']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required value="<?= e($user['username']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= e($user['email']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?= e((string) $user['phone']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password <?= $isEdit ? '(optional)' : '' ?></label>
                    <input type="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('Language')) ?></label>
                    <select name="language_preference" class="form-select">
                        <option value="en" <?= $user['language_preference'] === 'en' ? 'selected' : '' ?>>EN</option>
                        <option value="fil" <?= $user['language_preference'] === 'fil' ? 'selected' : '' ?>>FIL</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('Status')) ?></label>
                    <select name="status" class="form-select">
                        <?php foreach (['Active','Inactive','Archived'] as $s): ?>
                            <option value="<?= $s ?>" <?= $user['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><?= e(__('Save')) ?></button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
