<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/backup_helper.php';
require_admin();

$pageTitle = page_title(__('Backup & Restore'));
$backups = db()->query('SELECT b.*, u.full_name AS created_by_name FROM backups b LEFT JOIN users u ON u.id = b.created_by ORDER BY b.created_at DESC')->fetchAll();

$tables = [];
try {
    $rows = db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM);
    foreach ($rows as $r) $tables[] = $r[0];
} catch (Throwable $e) {
    $tables = [];
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e(__('Backup & Restore')) ?></h1>
        <p class="subtitle">Admin-only database backup, restore, validation, and history</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h2 class="h5">Create backup</h2>
                <p class="text-muted small">Creates a downloadable SQL backup. Uses mysqldump if available in XAMPP, otherwise a PHP-based export fallback. Backup history is recorded.</p>
                <form method="post" action="<?= e(base_url('modules/backup_restore/actions.php')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="backup">
                    <div class="mb-3">
                        <label class="form-label">Backup type</label>
                        <select name="backup_type" class="form-select">
                            <option value="full">Full database</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" data-confirm="Create a new database backup now?">Create & Download Backup</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100 border-warning">
            <div class="card-body">
                <h2 class="h5 text-warning">Restore data</h2>
                <div class="alert alert-warning small">
                    <strong>Warning:</strong> Restoring may replace existing data. A safety backup is created before restore whenever possible. Validate the uploaded SQL file before restoring.
                </div>
                <form method="post" action="<?= e(base_url('modules/backup_restore/actions.php')) ?>" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="restore">
                    <div class="mb-2">
                        <label class="form-label">Backup SQL file</label>
                        <input type="file" name="backup_file" class="form-control" accept=".sql,text/plain" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Restore mode</label>
                        <select name="restore_mode" id="restoreMode" class="form-select">
                            <option value="full">Full database restore</option>
                            <option value="selected">Restore selected tables only</option>
                        </select>
                    </div>
                    <div class="mb-3" id="tablesSelectWrap" style="display:none">
                        <label class="form-label">Select tables to restore</label>
                        <select name="selected_tables[]" class="form-select" multiple size="8">
                            <?php foreach ($tables as $t): ?>
                                <option value="<?= e($t) ?>"><?= e($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple tables. Only statements for selected tables will be executed (based on parsed table names).</div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="create_safety_backup" value="1" id="safetyBackup" checked>
                        <label class="form-check-label" for="safetyBackup">Create safety backup before restore (recommended)</label>
                    </div>
                    <button type="submit" class="btn btn-warning" data-confirm="Restore will modify the database. Continue?">Validate & Restore</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mt-3">
    <div class="card-body">
        <h2 class="h5">Backup history</h2>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>File</th><th>Type</th><th>Status</th><th>Size</th><th>Created</th><th>By</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                <?php if (!$backups): ?><tr><td colspan="7" class="text-muted">No backups recorded yet.</td></tr><?php endif; ?>
                <?php foreach ($backups as $b): ?>
                    <tr>
                        <td><?= e($b['file_name']) ?></td>
                        <td><?= e($b['backup_type']) ?></td>
                        <td><span class="badge <?= $b['status']==='Success'?'bg-success':($b['status']==='Failed'?'bg-danger':'bg-secondary') ?>"><?= e($b['status']) ?></span></td>
                        <td><?= e($b['file_size'] !== null ? number_format((float)$b['file_size']/1024, 1) . ' KB' : '—') ?></td>
                        <td><?= e($b['created_at']) ?></td>
                        <td><?= e($b['created_by_name'] ?? 'System') ?></td>
                        <td class="text-end">
                            <?php if (!empty($b['file_path']) && is_file(__DIR__ . '/../../' . $b['file_path'])): ?>
                                <a class="btn btn-sm btn-outline-primary" href="<?= e(base_url('modules/backup_restore/download.php?id=' . (int)$b['id'])) ?>">Download</a>
                            <?php else: ?>
                                <span class="text-muted small">Not available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('restoreMode')?.addEventListener('change', function(){
  document.getElementById('tablesSelectWrap').style.display = this.value === 'selected' ? 'block' : 'none';
});
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
