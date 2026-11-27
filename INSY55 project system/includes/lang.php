<?php
declare(strict_types=1);

function current_lang(): string
{
    $lang = $_SESSION['lang'] ?? 'en';
    return in_array($lang, ['en', 'fil'], true) ? $lang : 'en';
}

function set_lang(string $lang): void
{
    if (in_array($lang, ['en', 'fil'], true)) {
        $_SESSION['lang'] = $lang;
        if (is_logged_in()) {
            try {
                $stmt = db()->prepare('UPDATE users SET language_preference = ? WHERE id = ?');
                $stmt->execute([$lang, current_user()['id']]);
                $_SESSION['user']['language_preference'] = $lang;
            } catch (Throwable $e) {
                // ignore persistence failure
            }
        }
    }
}

function translations(): array
{
    static $cache = [];
    $lang = current_lang();
    if (isset($cache[$lang])) {
        return $cache[$lang];
    }
    $file = __DIR__ . '/../assets/lang/' . $lang . '.php';
    $cache[$lang] = is_file($file) ? require $file : [];
    return $cache[$lang];
}

function __(string $key, array $replace = []): string
{
    $text = translations()[$key] ?? $key;
    foreach ($replace as $search => $value) {
        $text = str_replace(':' . $search, (string) $value, $text);
    }
    return $text;
}
