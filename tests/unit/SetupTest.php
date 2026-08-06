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
        \Config::$values = [];
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
        self::assertStringContainsString('_glpi_csrf_token', $source);
        self::assertStringNotContainsString('Session::checkCSRF', $source);
        self::assertStringNotContainsString('$CFG_GLPI', $source);
        self::assertStringNotContainsString("\$_SERVER['PHP_SELF']", $source);
    }

    public function testChecksConfigurationUsingOnlyGlpiCoreClasses(): void
    {
        \Config::$values['plugin:oncallforms'] = [
            'oncall_form_id' => '12',
            'catalog_category_id' => '27',
        ];

        self::assertTrue(\plugin_oncallforms_check_config());

        \Config::$values['plugin:oncallforms']['catalog_category_id'] = '0';

        self::assertFalse(\plugin_oncallforms_check_config());
    }
}
