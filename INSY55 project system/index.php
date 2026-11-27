<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('modules/dashboard/index.php');
}
redirect('login.php');
