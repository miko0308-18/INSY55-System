<?php
declare(strict_types=1);

/**
 * MySQL/MariaDB connection settings for local Laragon/XAMPP.
 * Default Laragon/XAMPP: host 127.0.0.1 or localhost, user root, password empty.
 * Update host/port/username/password if your local MySQL differs.
 */
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'dbname' => 'come_n_meatshop_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
