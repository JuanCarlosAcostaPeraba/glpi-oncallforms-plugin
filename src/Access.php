<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

final class Access
{
    public const RIGHT = 'plugin_oncallforms_configuration';

    public static function canRead(): bool
    {
        if (\Session::haveRight('config', UPDATE)) {
            return true;
        }

        return self::isManagementEntity()
            && (
                \Session::haveRight(self::RIGHT, READ)
                || \Session::haveRight(self::RIGHT, UPDATE)
            );
    }

    public static function canUpdate(): bool
    {
        if (\Session::haveRight('config', UPDATE)) {
            return true;
        }

        return self::isManagementEntity()
            && \Session::haveRight(self::RIGHT, UPDATE);
    }

    public static function checkRead(): void
    {
        if (!self::canRead()) {
            \Session::checkRight('config', UPDATE);
        }
    }

    public static function checkUpdate(): void
    {
        if (!self::canUpdate()) {
            \Session::checkRight('config', UPDATE);
        }
    }

    private static function isManagementEntity(): bool
    {
        $entity = \Entity::getById(\Session::getActiveEntity());

        return $entity instanceof \Entity
            && strcasecmp(trim((string) ($entity->fields['name'] ?? '')), 'SSI') === 0;
    }
}
