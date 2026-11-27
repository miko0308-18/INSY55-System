<?php
declare(strict_types=1);

function app_config(?string $key = null)
{
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../config/app.php';
    }
    if ($key === null) {
        return $config;
    }
    $parts = explode('.', $key);
    $value = $config;
    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return null;
        }
        $value = $value[$part];
    }
    return $value;
}

function base_url(string $path = ''): string
{
    $base = rtrim((string) app_config('base_url'), '/');
    $path = ltrim($path, '/');
    return $path === '' ? $base . '/' : $base . '/' . $path;
}

function redirect(string $path): void
{
    header('Location: ' . base_url($path));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money($amount): string
{
    return app_config('currency_symbol') . number_format((float) $amount, 2);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']['id']);
}

function is_admin(): bool
{
    return (current_user()['role_name'] ?? '') === ROLE_ADMIN;
}

function is_staff(): bool
{
    return (current_user()['role_name'] ?? '') === ROLE_STAFF;
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', __('Please log in to continue.'));
        redirect('login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        flash('error', __('You do not have permission to access that page.'));
        redirect('modules/dashboard/index.php');
    }
}

function setting(string $key, $default = null)
{
    static $cache = [];
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    try {
        $stmt = db()->prepare('SELECT setting_value, value_type FROM settings WHERE setting_key = ? LIMIT 1');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if (!$row) {
            $cache[$key] = $default;
            return $default;
        }
        $value = $row['setting_value'];
        switch ($row['value_type']) {
            case 'number':
                $value = (float) $value;
                break;
            case 'boolean':
                $value = in_array(strtolower((string) $value), ['1', 'true', 'yes'], true);
                break;
            case 'json':
                $value = json_decode((string) $value, true);
                break;
        }
        $cache[$key] = $value;
        return $value;
    } catch (Throwable $e) {
        return $default;
    }
}

function log_activity(
    string $eventType,
    string $module,
    string $description,
    ?string $entityType = null,
    $entityId = null,
    $oldValues = null,
    $newValues = null
): void {
    try {
        $stmt = db()->prepare(
            'INSERT INTO activity_logs
            (user_id, event_type, module, entity_type, entity_id, description, old_values_json, new_values_json, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            current_user()['id'] ?? null,
            $eventType,
            $module,
            $entityType,
            $entityId !== null ? (string) $entityId : null,
            $description,
            $oldValues !== null ? json_encode($oldValues) : null,
            $newValues !== null ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null,
        ]);
    } catch (Throwable $e) {
        // Do not break primary workflow if logging fails.
    }
}

function generate_receipt_number(PDO $pdo): string
{
    $prefix = 'CNM-' . date('Ymd') . '-';
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM sales WHERE DATE(sale_datetime) = CURDATE()');
    $stmt->execute();
    $count = (int) $stmt->fetchColumn() + 1;
    return $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
}

function calculate_sale_totals(float $subtotal, float $discountPercent, float $vatPercent): array
{
    $discountAmount = round($subtotal * ($discountPercent / 100), 2);
    $afterDiscount = max(0, $subtotal - $discountAmount);
    $vatAmount = round($afterDiscount * ($vatPercent / 100), 2);
    $total = round($afterDiscount + $vatAmount, 2);
    return [
        'subtotal' => round($subtotal, 2),
        'discount_percent' => $discountPercent,
        'discount_amount' => $discountAmount,
        'vat_rate_percent' => $vatPercent,
        'vat_amount' => $vatAmount,
        'total_amount' => $total,
    ];
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function post(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

function get(string $key, $default = null)
{
    return $_GET[$key] ?? $default;
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function page_title(string $title): string
{
    return $title . ' | ' . app_config('name');
}
