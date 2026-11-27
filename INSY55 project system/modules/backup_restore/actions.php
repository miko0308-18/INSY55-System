<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/backup_helper.php';
require_admin();

if (request_method() !== 'POST') {
    redirect('modules/backup_restore/index.php');
}

require_csrf();
$action = (string) post('action', '');

try {
    if ($action === 'backup') {
        $type = (string) post('backup_type', 'full');
        $info = create_backup_file($type);
        db()->prepare(
            'INSERT INTO backups (backup_type, file_name, file_path, file_size, storage_location, status, notes, created_by)
             VALUES (?,?,?,?,?,?,?,?)'
        )->execute([
            $type,
            $info['file_name'],
            $info['file_path'],
            $info['file_size'],
            'local',
            'Success',
            'Backup created via ' . $info['method'],
            current_user()['id'],
        ]);
        $backupId = (int) db()->lastInsertId();
        create_notification('backup_completed', 'Backup completed', 'Database backup ' . $info['file_name'] . ' was created.', 'success', 'Admin', 'backup_restore', 'backup', $backupId);
        log_activity('backup_create', 'backup_restore', 'Created database backup', 'backup', $backupId);

        // Force download
        if (is_file($info['full_path'])) {
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . basename($info['file_name']) . '"');
            header('Content-Length: ' . filesize($info['full_path']));
            readfile($info['full_path']);
            exit;
        }
        flash('success', 'Backup created: ' . $info['file_name']);
    } elseif ($action === 'restore') {
        if (empty($_FILES['backup_file']) || ($_FILES['backup_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Please upload a valid SQL backup file.');
        }
        $tmp = $_FILES['backup_file']['tmp_name'];
        $orig = basename((string) $_FILES['backup_file']['name']);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            throw new RuntimeException('Only .sql files are allowed.');
        }

        // Move to temp location for validation/restore
        $tempDir = backups_dir() . DIRECTORY_SEPARATOR . 'tmp';
        if (!is_dir($tempDir)) @mkdir($tempDir, 0777, true);
        $tempPath = $tempDir . DIRECTORY_SEPARATOR . 'restore_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.sql';
        if (!move_uploaded_file($tmp, $tempPath)) {
            throw new RuntimeException('Failed to store uploaded backup file.');
        }

        [$ok, $errors] = validate_backup_sql_file($tempPath);
        if (!$ok) {
            @unlink($tempPath);
            throw new RuntimeException('Invalid backup file: ' . implode(' ', $errors));
        }

        $mode = (string) post('restore_mode', 'full');
        $selected = (array) post('selected_tables', []);
        $safety = (bool) post('create_safety_backup', false);

        if ($mode === 'selected' && empty($selected)) {
            @unlink($tempPath);
            throw new RuntimeException('Select at least one table for selected restore.');
        }

        $result = restore_sql_file($tempPath, $mode, $selected, $safety);
        @unlink($tempPath);

        $notes = ucfirst($mode) . ' restore executed (' . $result['executed'] . ' statements).';
        db()->prepare(
            'INSERT INTO backups (backup_type, file_name, file_path, file_size, storage_location, status, notes, created_by)
             VALUES (?,?,?,?,?,?,?,?)'
        )->execute([
            $mode === 'full' ? 'restore_full' : 'restore_selected',
            $orig,
            null,
            (int) ($_FILES['backup_file']['size'] ?? 0),
            'upload',
            'Restored',
            $notes,
            current_user()['id'],
        ]);
        $restoreId = (int) db()->lastInsertId();
        create_notification('restore_completed', 'Restore completed', $notes, 'success', 'Admin', 'backup_restore', 'restore', $restoreId);
        log_activity('restore', 'backup_restore', $notes, 'backup', $restoreId, null, ['mode' => $mode, 'tables' => $selected]);

        flash('success', 'Restore completed. ' . $notes);
    } else {
        flash('error', 'Invalid backup action.');
    }
} catch (Throwable $e) {
    try {
        db()->prepare(
            'INSERT INTO backups (backup_type, file_name, file_path, file_size, storage_location, status, notes, created_by)
             VALUES (?,?,?,?,?,?,?,?)'
        )->execute([
            $action === 'backup' ? 'full' : 'restore_full',
            'N/A',
            null,
            null,
            'local',
            'Failed',
            substr($e->getMessage(), 0, 250),
            current_user()['id'] ?? null,
        ]);
    } catch (Throwable $ignored) {
    }
    log_activity($action === 'backup' ? 'backup_failed' : 'restore_failed', 'backup_restore', $e->getMessage());
    flash('error', $e->getMessage());
}

redirect('modules/backup_restore/index.php');
