<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/bootstrap.php';
require_login();

$lang = (string) get('lang', 'en');
set_lang($lang);

$referer = $_SERVER['HTTP_REFERER'] ?? base_url('modules/dashboard/index.php');
header('Location: ' . $referer);
exit;
