<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Support;

final class EntityStub extends CommonDBTMStub
{
    /** @var array<string, mixed> */
    public array $fields = [];

    public static function getById(int $id): ?self
    {
        $names = [4 => 'SSI', 8 => 'Obras y Mantenimiento'];
        if (!isset($names[$id])) {
            return null;
        }

        $entity = new self();
        $entity->fields = ['id' => $id, 'name' => $names[$id]];
        return $entity;
    }
}
