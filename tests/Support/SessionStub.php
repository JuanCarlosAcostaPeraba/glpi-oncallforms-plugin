<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Support;

use RuntimeException;

final class SessionStub
{
    /** @var array<string, int> */
    public static array $rights = [];
    public static int $activeEntity = 4;

    public static function haveRight(string $name, int $right): bool
    {
        return ((self::$rights[$name] ?? 0) & $right) === $right;
    }

    public static function getActiveEntity(): int
    {
        return self::$activeEntity;
    }

    public static function checkRight(string $name, int $right): void
    {
        if (!self::haveRight($name, $right)) {
            throw new RuntimeException('Acceso denegado.');
        }
    }
}
