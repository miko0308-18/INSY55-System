<?php
declare(strict_types=1);

/**
 * Application settings and business placeholders.
 * Replace placeholder business details when real data is available.
 */
return [
    'name' => "Come N' Meatshop",
    'tagline' => 'Web-Based Inventory Management System',
    'timezone' => 'Asia/Manila',
    'base_path' => str_replace('\\', '/', dirname(__DIR__)),
    'base_url' => '/clone/INSY55-System/INSY55%20project%20system/',
    'default_language' => 'en',
    'supported_languages' => ['en', 'fil'],
    'currency_symbol' => '₱',
    'currency_code' => 'PHP',
    'default_vat_percent' => 12.00,
    'default_min_stock' => 10,
    'near_expiry_days' => 3,
    'email_notifications_enabled' => false, // optional; easy to disable/remove
    'business' => [
        'name' => "Come N' Meatshop",
        'address' => '[Business Address Placeholder]',
        'phone' => '[Contact Number Placeholder]',
        'email' => '[Business Email Placeholder]',
        'logo' => 'assets/images/placeholders/logo-placeholder.svg',
        'receipt_footer' => "Thank you for shopping at Come N' Meatshop!",
    ],
];
