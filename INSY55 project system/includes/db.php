<?php
declare(strict_types=1);

/**
 * Shared PDO connection singleton.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = require __DIR__ . '/../config/database.php';
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $cfg['host'],
        $cfg['port'],
        $cfg['dbname'],
        $cfg['charset']
    );

    $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
    return $pdo;
}
