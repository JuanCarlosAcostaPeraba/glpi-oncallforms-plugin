<?php

declare(strict_types=1);

use GlpiPlugin\Oncallforms\Config;
use GlpiPlugin\Oncallforms\ProfilePermission;

function plugin_oncallforms_install(): bool
{
    Config::install();
    ProfilePermission::installRights();
    return true;
}

function plugin_oncallforms_uninstall(): bool
{
    global $DB;

    ProfilePermission::uninstallRights();
    $DB->delete('glpi_configs', ['context' => Config::CONTEXT]);
    return true;
}
