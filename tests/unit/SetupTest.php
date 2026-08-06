<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class SetupTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['PLUGIN_HOOKS'] = [];
        \Plugin::$active = false;
    }

    public function testRegistersConfigurationPageWhilePluginIsInactive(): void
    {
        \plugin_init_oncallforms();

        self::assertSame(
            'front/config.form.php',
            $GLOBALS['PLUGIN_HOOKS']['config_page']['oncallforms']
        );
        self::assertArrayNotHasKey('add_javascript', $GLOBALS['PLUGIN_HOOKS']);
        self::assertArrayNotHasKey('add_css', $GLOBALS['PLUGIN_HOOKS']);
    }

    public function testConfigurationFormTargetsItsGlpi11PluginUrl(): void
    {
        $source = file_get_contents(dirname(__DIR__, 2) . '/front/config.form.php');

        self::assertIsString($source);
        self::assertStringContainsString('/plugins/oncallforms/front/config.form.php', $source);
        self::assertStringContainsString('Html::getPrefixedUrl', $source);
        self::assertStringNotContainsString('$CFG_GLPI', $source);
        self::assertStringNotContainsString("\$_SERVER['PHP_SELF']", $source);
    }
}
