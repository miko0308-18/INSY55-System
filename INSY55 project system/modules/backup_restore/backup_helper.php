<?php
declare(strict_types=1);

function backups_dir(): string
{
    $dir = __DIR__ . '/../../uploads/backups';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    return realpath($dir) ?: $dir;
}

function required_backup_tables(): array
{
    return ['users','roles','products','product_categories','stock_movements','sales','sale_items','payments','notifications','user_notifications','settings','backups','activity_logs','reports'];
}

function validate_backup_sql_file(string $filePath): array
{
    $errors = [];
    if (!is_file($filePath)) {
        $errors[] = 'Backup file does not exist.';
        return [false, $errors];
    }
    $size = filesize($filePath);
    if ($size === false || $size <= 0) {
        $errors[] = 'Backup file is empty.';
    }
    if ($size !== false && $size > 50 * 1024 * 1024) {
        $errors[] = 'Backup file is too large (max 50 MB).';
    }
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if ($ext !== 'sql') {
        $errors[] = 'Only .sql backup files are allowed.';
    }

    $content = @file_get_contents($filePath);
    if ($content === false || trim($content) === '') {
        $errors[] = 'Unable to read backup file contents.';
        return [false, $errors];
    }

    // Basic safety checks
    $danger = ['LOAD_FILE', 'INTO OUTFILE', 'INTO DUMPFILE'];
    foreach ($danger as $token) {
        if (stripos($content, $token) !== false) {
            $errors[] = 'Backup contains unsafe SQL token: ' . $token;
        }
    }

    $lower = strtolower($content);
    $found = 0;
    foreach (required_backup_tables() as $table) {
        if (strpos($lower, '`' . $table . '`') !== false || preg_match('/\b' . preg_quote($table, '/') . '\b/i', $content)) {
            $found++;
        }
    }
    if ($found < 5) {
        $errors[] = 'Backup does not appear to contain required application tables/structure (found ' . $found . ' expected table markers).';
    }

    $hasSql = (stripos($content, 'create table') !== false) || (stripos($content, 'insert into') !== false) || (stripos($content, 'drop table') !== false);
    if (!$hasSql) {
        $errors[] = 'File does not look like a valid SQL backup.';
    }

    return [count($errors) === 0, $errors];
}

function generate_php_sql_backup(string $targetPath): void
{
    $pdo = db();
    $pdo->exec('SET NAMES utf8mb4');
    $dbName = (require __DIR__ . '/../../config/database.php')['dbname'];

    $fh = fopen($targetPath, 'wb');
    if (!$fh) throw new RuntimeException('Unable to create backup file.');

    fwrite($fh, "-- Come N' Meatshop backup\n");
    fwrite($fh, "-- Generated: " . date('c') . "\n");
    fwrite($fh, "-- Database: {$dbName}\n\n");
    fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_NUM);
    foreach ($tables as $row) {
        $table = $row[0];
        fwrite($fh, "DROP TABLE IF EXISTS `{$table}`;\n");
        $create = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
        $createSql = $create['Create Table'] ?? ($create[array_key_last($create)] ?? '');
        fwrite($fh, $createSql . ";\n\n");

        $data = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        if ($data) {
            foreach ($data as $r) {
                $cols = array_map(fn($c) => "`{$c}`", array_keys($r));
                $vals = [];
                foreach ($r as $v) {
                    if ($v === null) $vals[] = 'NULL';
                    else $vals[] = $pdo->quote((string) $v);
                }
                fwrite($fh, "INSERT INTO `{$table}` (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ");\n");
            }
            fwrite($fh, "\n");
        }
    }

    fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($fh);
}

function create_backup_file(string $backupType = 'full'): array
{
    $dir = backups_dir();
    $fileName = 'backup_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.sql';
    $fullPath = $dir . DIRECTORY_SEPARATOR . $fileName;
    $relative = 'uploads/backups/' . $fileName;

    $method = 'php_fallback';
    $usedMysqldump = false;

    // Try mysqldump if available (Laragon/XAMPP/PATH)
    $cfg = require __DIR__ . '/../../config/database.php';
    $mysqldumpCandidates = [
        'C:\\xampp\\mysql\\bin\\mysqldump.exe',
        'mysqldump',
    ];
    // Discover Laragon MySQL/MariaDB bins dynamically (versions vary by install)
    foreach ([ 'C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe', 'C:\\laragon\\bin\\mariadb\\*\\bin\\mysqldump.exe' ] as $pattern) {
        $matches = glob($pattern) ?: [];
        rsort($matches, SORT_NATURAL);
        foreach ($matches as $m) {
            $mysqldumpCandidates[] = $m;
        }
    }
    $mysqldumpCandidates = array_values(array_unique($mysqldumpCandidates));

    foreach ($mysqldumpCandidates as $bin) {
        $cmd = null;
        $output = [];
        $code = 1;
        if (stripos(PHP_OS_FAMILY, 'Windows') === 0 && str_contains((string) $bin, ':\\')) {
            if (!is_file($bin)) continue;
            $cmd = '"' . $bin . '"';
        } elseif ($bin === 'mysqldump') {
            $cmd = 'mysqldump';
        } else {
            if (!is_file($bin)) continue;
            $cmd = escapeshellarg($bin);
        }
        $host = escapeshellarg($cfg['host']);
        $user = escapeshellarg($cfg['username']);
        $db = escapeshellarg($cfg['dbname']);
        $port = (int) $cfg['port'];
        $pass = (string) $cfg['password'];
        $passArg = $pass !== '' ? ('--password=' . escapeshellarg($pass)) : '';
        $outArg = escapeshellarg($fullPath);
        $command = $cmd . " --host={$host} --port={$port} --user={$user} {$passArg} --routines --triggers --single-transaction {$db} > {$outArg} 2>&1";
        @exec($command, $output, $code);
        if ($code === 0 && is_file($fullPath) && filesize($fullPath) > 0) {
            $usedMysqldump = true;
            $method = 'mysqldump';
            break;
        }
        @unlink($fullPath);
    }

    if (!$usedMysqldump) {
        generate_php_sql_backup($fullPath);
        $method = 'php_fallback';
    }

    return [
        'file_name' => $fileName,
        'file_path' => $relative,
        'full_path' => $fullPath,
        'file_size' => filesize($fullPath) ?: 0,
        'method' => $method,
    ];
}

function split_sql_statements(string $sql): array
{
    $statements = [];
    $buffer = '';
    $len = strlen($sql);
    $inString = false;
    $stringChar = '';
    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];
        $next = $i + 1 < $len ? $sql[$i + 1] : '';
        if (!$inString && $ch === '-' && $next === '-') {
            while ($i < $len && $sql[$i] !== "\n") $i++;
            continue;
        }
        if ($ch === "'" || $ch === '"') {
            if ($inString && $ch === $stringChar) {
                $inString = false;
            } elseif (!$inString) {
                $inString = true;
                $stringChar = $ch;
            }
        }
        if (!$inString && $ch === ';') {
            $trim = trim($buffer);
            if ($trim !== '') $statements[] = $trim;
            $buffer = '';
            continue;
        }
        $buffer .= $ch;
    }
    $trim = trim($buffer);
    if ($trim !== '') $statements[] = $trim;
    return $statements;
}

function statement_table_name(string $statement): ?string
{
    if (preg_match('/^\s*(?:CREATE|DROP|ALTER|INSERT\s+INTO|REPLACE\s+INTO|TRUNCATE(?:\s+TABLE)?)\s+(?:TABLE\s+)?(?:IF\s+(?:NOT\s+)?EXISTS\s+)?`?([a-zA-Z0-9_]+)`?/i', $statement, $m)) {
        return $m[1];
    }
    return null;
}

function restore_sql_file(string $filePath, string $mode = 'full', array $selectedTables = [], bool $safetyBackup = true): array
{
    [$ok, $errors] = validate_backup_sql_file($filePath);
    if (!$ok) {
        throw new RuntimeException('Backup validation failed: ' . implode(' ', $errors));
    }

    $safety = null;
    if ($safetyBackup) {
        try {
            $safety = create_backup_file('safety');
            db()->prepare(
                'INSERT INTO backups (backup_type, file_name, file_path, file_size, storage_location, status, notes, created_by)
                 VALUES (?,?,?,?,?,?,?,?)'
            )->execute([
                'safety',
                $safety['file_name'],
                $safety['file_path'],
                $safety['file_size'],
                'local',
                'Success',
                'Automatic safety backup before restore (' . $safety['method'] . ')',
                current_user()['id'] ?? null,
            ]);
        } catch (Throwable $e) {
            // continue but note failure
            $safety = ['error' => $e->getMessage()];
        }
    }

    $sql = file_get_contents($filePath);
    $statements = split_sql_statements((string) $sql);
    $pdo = db();
    $executed = 0;

    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    try {
        foreach ($statements as $stmtSql) {
            $trim = ltrim($stmtSql);
            if ($trim === '' || str_starts_with($trim, '--') || str_starts_with($trim, '/*')) continue;
            if ($mode === 'selected') {
                $table = statement_table_name($stmtSql);
                if ($table !== null && !in_array($table, $selectedTables, true)) {
                    continue;
                }
                // Allow global SET statements in selected mode
                if ($table === null && !preg_match('/^\s*SET\b/i', $stmtSql)) {
                    continue;
                }
            }
            $pdo->exec($stmtSql);
            $executed++;
        }
    } finally {
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    return ['executed' => $executed, 'safety_backup' => $safety];
}
