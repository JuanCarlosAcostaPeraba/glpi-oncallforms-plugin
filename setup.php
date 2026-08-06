<?php

declare(strict_types=1);

use Glpi\Plugin\Hooks;

define('PLUGIN_ONCALLFORMS_VERSION', '1.0.3');
define('PLUGIN_ONCALLFORMS_MIN_GLPI', '11.0.0');
define('PLUGIN_ONCALLFORMS_MAX_GLPI', '12.0.0');
define('PLUGIN_ONCALLFORMS_MIN_PHP', '8.2.0');

function plugin_init_oncallforms(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['oncallforms'] = true;
    $PLUGIN_HOOKS['config_page']['oncallforms'] = 'front/config.form.php';

    if (!Plugin::isPluginActive('oncallforms')) {
        return;
    }

    try {
        $context = GlpiPlugin\Oncallforms\FrontendContext::fromCurrentRequest();
        if (!$context->isRelevant()) {
            return;
        }

        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT]['oncallforms'][] = 'js/oncallforms.js';
        $PLUGIN_HOOKS[Hooks::ADD_CSS]['oncallforms'][] = 'css/oncallforms.css';
        $PLUGIN_HOOKS[Hooks::ADD_JAVASCRIPT_ANONYMOUS_PAGE]['oncallforms'][] = 'js/oncallforms.js';
        $PLUGIN_HOOKS[Hooks::ADD_CSS_ANONYMOUS_PAGE]['oncallforms'][] = 'css/oncallforms.css';
        $PLUGIN_HOOKS[Hooks::ADD_HEADER_TAG]['oncallforms'][] = [
            'tag' => 'meta',
            'properties' => [
                'name' => 'oncallforms-context',
                'content' => $context->toJson(),
            ],
        ];
        $PLUGIN_HOOKS[Hooks::ADD_HEADER_TAG_ANONYMOUS_PAGE]['oncallforms'][] = [
            'tag' => 'meta',
            'properties' => [
                'name' => 'oncallforms-context',
                'content' => $context->toJson(),
            ],
        ];
    } catch (Throwable) {
        // A missing or incomplete configuration must never break GLPI pages.
    }
}

function plugin_version_oncallforms(): array
{
    return [
        'name' => __('Formularios de guardia', 'oncallforms'),
        'version' => PLUGIN_ONCALLFORMS_VERSION,
        'author' => 'Juan Carlos Acosta Peraba',
        'license' => 'GPL-3.0-or-later',
        'homepage' => 'https://github.com/JuanCarlosAcostaPeraba/glpi-oncallforms-plugin',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_ONCALLFORMS_MIN_GLPI,
                'max' => '11.99.99',
            ],
            'php' => ['min' => PLUGIN_ONCALLFORMS_MIN_PHP],
        ],
    ];
}

function plugin_oncallforms_check_prerequisites(): bool
{
    if (version_compare(PHP_VERSION, PLUGIN_ONCALLFORMS_MIN_PHP, '<')) {
        echo sprintf('Formularios de guardia requiere PHP %s o posterior.', PLUGIN_ONCALLFORMS_MIN_PHP);
        return false;
    }

    if (
        version_compare(GLPI_VERSION, PLUGIN_ONCALLFORMS_MIN_GLPI, '<')
        || version_compare(GLPI_VERSION, PLUGIN_ONCALLFORMS_MAX_GLPI, '>=')
    ) {
        if (method_exists(Plugin::class, 'messageIncompatible')) {
            echo Plugin::messageIncompatible(
                'core',
                PLUGIN_ONCALLFORMS_MIN_GLPI,
                PLUGIN_ONCALLFORMS_MAX_GLPI
            );
        }
        return false;
    }

    return true;
}

function plugin_oncallforms_check_config(bool $verbose = false): bool
{
    $config = GlpiPlugin\Oncallforms\Config::get();
    $valid = $config['oncall_form_id'] > 0 && $config['normal_form_id'] > 0;

    if ($verbose && !$valid) {
        echo __('Seleccione los formularios de guardia y normal en la configuración del plugin.', 'oncallforms');
    }

    return $valid;
}
