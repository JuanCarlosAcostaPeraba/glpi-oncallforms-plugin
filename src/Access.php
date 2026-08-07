<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

final class Access
{
    public const RIGHT = 'plugin_oncallforms_configuration';

    public static function canRead(): bool
    {
        return \Session::haveRight('config', UPDATE)
            || \Session::haveRight(self::RIGHT, READ)
            || \Session::haveRight(self::RIGHT, UPDATE);
    }

    public static function canUpdate(): bool
    {
        return \Session::haveRight('config', UPDATE)
            || \Session::haveRight(self::RIGHT, UPDATE);
    }

    public static function checkRead(): void
    {
        if (!self::canRead()) {
            \Session::checkRight(self::RIGHT, READ);
        }
    }

    public static function checkUpdate(): void
    {
        if (!self::canUpdate()) {
            \Session::checkRight(self::RIGHT, UPDATE);
        }
    }
}
