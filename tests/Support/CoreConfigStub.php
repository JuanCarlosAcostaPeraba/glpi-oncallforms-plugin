<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Support;

final class CoreConfigStub
{
    /** @var array<string, array<string, mixed>> */
    public static array $values = [];

    /** @return array<string, mixed> */
    public static function getConfigurationValues(string $context): array
    {
        return self::$values[$context] ?? [];
    }
}
