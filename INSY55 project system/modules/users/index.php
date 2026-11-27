<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_admin();

$pageTitle = page_title(__('Users'));
$q = trim((string) get('q', ''));
$status = (string) get('status', 'Active');

$sql = "SELECT u.*, r.name AS role_name FROM users u
        INNER JOIN roles r ON r.id = u.role_id
        WHERE r.slug = 'staff'";
$params = [];

if ($status !== 'all') {
    $sql .= ' AND u.status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $sql .= ' AND (u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
$sql .= ' ORDER BY u.created_at DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Users')) ?></h1>
        <p class="subtitle">Manage Staff accounts</p>
    </div>
    <a href="<?= e(base_url('modules/users/form.php')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add Staff</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2" method="get">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control" placeholder="<?= e(__('Search')) ?>" value="<?= e($q) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <?php foreach (['Active','Inactive','Archived','Deleted','all'] as $s): ?>
                        <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" type="submit"><?= e(__('Search')) ?></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
            <tr>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th><?= e(__('Status')) ?></th>
                <th><?= e(__('Actions')) ?></th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$users): ?>
                <tr><td colspan="5" class="text-muted p-3"><?= e(__('No records found.')) ?></td></tr>
            <?php else: foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['full_name']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="badge text-bg-light border"><?= e($u['status']) ?></span></td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(base_url('modules/users/form.php?id=' . $u['id'])) ?>"><?= e(__('Edit')) ?></a>
                        <?php if ($u['status'] !== 'Archived'): ?>
                            <form class="d-inline" method="post" action="<?= e(base_url('modules/users/actions.php')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="archive">
                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <button class="btn btn-sm btn-outline-warning" data-confirm="Archive this staff account?"><?= e(__('Archive')) ?></button>
                            </form>
                        <?php else: ?>
                            <form class="d-inline" method="post" action="<?= e(base_url('modules/users/actions.php')) ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="restore">
                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <button class="btn btn-sm btn-outline-success"><?= e(__('Restore')) ?></button>
                            </form>
                        <?php endif; ?>
                        <form class="d-inline" method="post" action="<?= e(base_url('modules/users/actions.php')) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" data-confirm="Permanently mark this staff as deleted?"><?= e(__('Delete')) ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
