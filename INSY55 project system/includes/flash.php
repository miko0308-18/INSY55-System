<?php
declare(strict_types=1);

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function render_flashes(): void
{
    foreach (get_flashes() as $flash) {
        $type = $flash['type'] === 'error' ? 'danger' : $flash['type'];
        echo '<div class="alert alert-' . e($type) . ' alert-dismissible fade show" role="alert">'
            . e($flash['message'])
            . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}
