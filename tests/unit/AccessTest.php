<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Unit;

use GlpiPlugin\Oncallforms\Access;
use GlpiPlugin\Oncallforms\Tests\Support\SessionStub;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AccessTest extends TestCase
{
    protected function setUp(): void
    {
        SessionStub::$rights = [];
        SessionStub::$activeEntity = 4;
    }

    public function testTechnicianCanManageConfigurationInSsi(): void
    {
        SessionStub::$rights[Access::RIGHT] = READ | UPDATE;

        self::assertTrue(Access::canRead());
        self::assertTrue(Access::canUpdate());
    }

    public function testTechnicianCannotManageConfigurationOutsideSsi(): void
    {
        SessionStub::$rights[Access::RIGHT] = READ | UPDATE;
        SessionStub::$activeEntity = 8;

        self::assertFalse(Access::canRead());
        self::assertFalse(Access::canUpdate());
        $this->expectException(RuntimeException::class);
        Access::checkUpdate();
    }

    public function testSuperAdminKeepsGlobalAccess(): void
    {
        SessionStub::$rights['config'] = UPDATE;
        SessionStub::$activeEntity = 8;

        self::assertTrue(Access::canRead());
        self::assertTrue(Access::canUpdate());
    }
}
