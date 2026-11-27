<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_admin();

$id = (int) get('id', 0);
if ($id <= 0) {
    redirect('modules/backup_restore/index.php');
}

$stmt = db()->prepare('SELECT * FROM backups WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$backup = $stmt->fetch();
if (!$backup || empty($backup['file_path'])) {
    flash('error', 'Backup file not found.');
    redirect('modules/backup_restore/index.php');
}

$path = realpath(__DIR__ . '/../../' . $backup['file_path']);
$base = realpath(backups_dir());
if (!$path || !$base || !str_starts_with(strtolower($path), strtolower($base)) || !is_file($path)) {
    flash('error', 'Backup file is not available on disk.');
    redirect('modules/backup_restore/index.php');
}

header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . basename($backup['file_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
