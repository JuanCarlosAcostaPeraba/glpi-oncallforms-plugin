<?php

declare(strict_types=1);

use GlpiPlugin\Oncallforms\Access;
use GlpiPlugin\Oncallforms\Config;
use GlpiPlugin\Oncallforms\HolidayCalendar;

if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', '../../..');
}
include GLPI_ROOT . '/inc/includes.php';

Access::checkRead();

$csv = HolidayCalendar::toCsv(Config::get()['holidays']);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="festivos.csv"');
header('Content-Length: ' . strlen($csv));
header('Cache-Control: no-store, max-age=0');
header('X-Content-Type-Options: nosniff');

echo $csv;
