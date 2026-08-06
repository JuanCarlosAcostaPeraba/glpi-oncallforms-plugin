<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Support;

final class PluginStub
{
    public static bool $active = false;

    public static function isPluginActive(string $plugin): bool
    {
        return self::$active;
    }
}
