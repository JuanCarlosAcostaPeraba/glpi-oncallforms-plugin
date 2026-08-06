<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (!class_exists('Plugin')) {
    final class Plugin
    {
        public static bool $active = false;

        public static function isPluginActive(string $plugin): bool
        {
            return self::$active;
        }
    }
}

if (!function_exists('__')) {
    function __(string $message, ?string $domain = null): string
    {
        return $message;
    }
}

require dirname(__DIR__) . '/setup.php';
