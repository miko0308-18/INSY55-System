<?php
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(?string $token = null): bool
{
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function require_csrf(): void
{
    if (!verify_csrf()) {
        flash('error', __('Invalid security token. Please try again.'));
        $referer = $_SERVER['HTTP_REFERER'] ?? base_url('modules/dashboard/index.php');
        header('Location: ' . $referer);
        exit;
    }
}
