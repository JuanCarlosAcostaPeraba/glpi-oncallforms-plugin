<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (!class_exists('Plugin')) {
    class_alias(GlpiPlugin\Oncallforms\Tests\Support\PluginStub::class, 'Plugin');
}

if (!function_exists('__')) {
    function __(string $message, ?string $domain = null): string
    {
        return $message;
    }
}

require dirname(__DIR__) . '/setup.php';
