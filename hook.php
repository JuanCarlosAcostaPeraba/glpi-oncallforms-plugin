<?php

declare(strict_types=1);

use GlpiPlugin\Oncallforms\Config;

function plugin_oncallforms_install(): bool
{
    Config::install();
    return true;
}

function plugin_oncallforms_uninstall(): bool
{
    global $DB;

    $DB->delete('glpi_configs', ['context' => Config::CONTEXT]);
    return true;
}
