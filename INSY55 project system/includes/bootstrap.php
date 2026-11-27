<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/flash.php';
require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/notifications_helper.php';

$appConfig = require __DIR__ . '/../config/app.php';
$sessionConfig = require __DIR__ . '/../config/session.php';

date_default_timezone_set($appConfig['timezone']);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($sessionConfig['name']);
    session_set_cookie_params([
        'lifetime' => $sessionConfig['lifetime'],
        'path' => $sessionConfig['path'],
        'secure' => $sessionConfig['secure'],
        'httponly' => $sessionConfig['httponly'],
        'samesite' => $sessionConfig['samesite'],
    ]);
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (empty($_SESSION['lang'])) {
    $_SESSION['lang'] = $appConfig['default_language'];
}
