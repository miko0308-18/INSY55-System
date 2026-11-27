<?php
declare(strict_types=1);

return [
    'name' => 'COMEN_MEATSHOP_SESSID',
    'lifetime' => 7200,
    // Use '/' so the session cookie works for localhost subpaths and Laragon virtual hosts.
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax',
];
