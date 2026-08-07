<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (!class_exists('CommonGLPI')) {
    class_alias(GlpiPlugin\Oncallforms\Tests\Support\CommonGLPIStub::class, 'CommonGLPI');
}

if (!class_exists('CommonDBTM')) {
    class_alias(GlpiPlugin\Oncallforms\Tests\Support\CommonDBTMStub::class, 'CommonDBTM');
}

if (!class_exists('Profile')) {
    class_alias(GlpiPlugin\Oncallforms\Tests\Support\ProfileStub::class, 'Profile');
}

if (!class_exists('Plugin')) {
    class_alias(GlpiPlugin\Oncallforms\Tests\Support\PluginStub::class, 'Plugin');
}

if (!class_exists('Config')) {
    class_alias(GlpiPlugin\Oncallforms\Tests\Support\CoreConfigStub::class, 'Config');
}

if (!function_exists('__')) {
    function __(string $message, ?string $domain = null): string
    {
        return $message;
    }
}

require dirname(__DIR__) . '/setup.php';
