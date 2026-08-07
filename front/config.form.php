<?php

declare(strict_types=1);

use GlpiPlugin\Oncallforms\Access;
use GlpiPlugin\Oncallforms\Config;
use GlpiPlugin\Oncallforms\FormResolver;
use GlpiPlugin\Oncallforms\HolidayCalendar;

if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', '../../..');
}
include GLPI_ROOT . '/inc/includes.php';

Access::checkRead();

$configUrl = Html::getPrefixedUrl('/plugins/oncallforms/front/config.form.php');
$resolver = new FormResolver();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Access::checkUpdate();

    try {
        if (isset($_POST['import_holidays'])) {
            $upload = $_FILES['holidays_csv'] ?? null;
            if (!is_array($upload) || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new InvalidArgumentException(__('Seleccione un archivo CSV válido.', 'oncallforms'));
            }

            $imported = HolidayCalendar::fromCsvFile((string) ($upload['tmp_name'] ?? ''));
            $current = Config::get();
            $holidays = HolidayCalendar::merge($current['holidays'], $imported);
            Config::saveHolidays($holidays);
            Session::addMessageAfterRedirect(sprintf(
                __('Se han importado o actualizado %d festivos.', 'oncallforms'),
                count($imported)
            ), true, INFO);
        } else {
            Config::save($_POST, $resolver);
            Session::addMessageAfterRedirect(__('Configuración guardada.', 'oncallforms'), true, INFO);
        }
    } catch (InvalidArgumentException $exception) {
        Session::addMessageAfterRedirect($exception->getMessage(), true, ERROR);
    }
    Html::redirect($configUrl);
}

$config = Config::get();
$forms = $resolver->getSelectableOptions();
$weekdays = [
    1 => __('Lunes', 'oncallforms'),
    2 => __('Martes', 'oncallforms'),
    3 => __('Miércoles', 'oncallforms'),
    4 => __('Jueves', 'oncallforms'),
    5 => __('Viernes', 'oncallforms'),
    6 => __('Sábado', 'oncallforms'),
    7 => __('Domingo', 'oncallforms'),
];

/** @param mixed $value */
function oncallforms_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

Html::header(
    __('Configuración de Formularios de guardia', 'oncallforms'),
    $configUrl,
    'config',
    'plugins'
);
?>
<div class="container-xl">
    <form method="post" action="<?= oncallforms_escape($configUrl) ?>" enctype="multipart/form-data" class="card">
        <div class="card-header">
            <h2 class="card-title"><?= oncallforms_escape(__('Formularios de guardia', 'oncallforms')) ?></h2>
        </div>
        <div class="card-body">
            <h3><?= oncallforms_escape(__('Catálogo y formulario de guardia', 'oncallforms')) ?></h3>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label" for="dropdown_oncall_form_id0">
                        <?= oncallforms_escape(__('Formulario de guardia', 'oncallforms')) ?>
                    </label>
                    <?php Dropdown::showFromArray('oncall_form_id', $forms, [
                        'value' => $config['oncall_form_id'],
                        'display_emptychoice' => true,
                        'width' => '100%',
                        'rand' => 0,
                        'aria_label' => __('Formulario de guardia', 'oncallforms'),
                    ]); ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="catalog_category_id">
                        <?= oncallforms_escape(__('ID de la categoría del catálogo', 'oncallforms')) ?>
                    </label>
                    <input class="form-control" id="catalog_category_id" name="catalog_category_id"
                           type="number" min="1" required
                           value="<?= oncallforms_escape($config['catalog_category_id']) ?>">
                    <div class="form-hint">
                        <?= oncallforms_escape(
                            __('Es el valor category de la URL del catálogo; por ejemplo, 27.', 'oncallforms')
                        ) ?>
                    </div>
                </div>
            </div>

            <h3><?= oncallforms_escape(__('Horario laboral', 'oncallforms')) ?></h3>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label" for="business_start">
                        <?= oncallforms_escape(__('Inicio', 'oncallforms')) ?>
                    </label>
                    <input class="form-control" id="business_start" name="business_start" type="time" required
                           value="<?= oncallforms_escape($config['business_start']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="business_end">
                        <?= oncallforms_escape(__('Fin', 'oncallforms')) ?>
                    </label>
                    <input class="form-control" id="business_end" name="business_end" type="time" required
                           value="<?= oncallforms_escape($config['business_end']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="dropdown_timezone0">
                        <?= oncallforms_escape(__('Zona horaria', 'oncallforms')) ?>
                    </label>
                    <?php
                    $timezones = array_combine(DateTimeZone::listIdentifiers(), DateTimeZone::listIdentifiers());
                    Dropdown::showFromArray('timezone', $timezones ?: [], [
                        'value' => $config['timezone'],
                        'width' => '100%',
                        'rand' => 0,
                        'aria_label' => __('Zona horaria', 'oncallforms'),
                    ]);
                    ?>
                    <div class="form-hint">
                        <?= oncallforms_escape(sprintf(
                            __('Zona horaria efectiva: %s', 'oncallforms'),
                            $config['timezone']
                        )) ?>
                    </div>
                </div>
            </div>
            <fieldset class="mb-4">
                <legend class="form-label"><?= oncallforms_escape(__('Días laborables', 'oncallforms')) ?></legend>
                <div class="d-flex flex-wrap gap-3">
                    <?php foreach ($weekdays as $number => $label) : ?>
                        <label class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="business_days[]"
                                   value="<?= $number ?>"
                                   <?= in_array($number, $config['business_days'], true) ? 'checked' : '' ?>>
                            <span class="form-check-label"><?= oncallforms_escape($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <h3><?= oncallforms_escape(__('Días festivos', 'oncallforms')) ?></h3>
            <p class="text-secondary">
                <?= oncallforms_escape(
                    __(
                        'Los días marcados activan el modo de guardia durante todo el día, aunque sean laborables.',
                        'oncallforms'
                    )
                ) ?>
            </p>
            <?php
            $holidaysJson = json_encode(
                $config['holidays'],
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
            );
            ?>
            <div class="oncallforms-holidays mb-4" data-oncallforms-holidays>
                <input type="hidden" id="holidays_json" name="holidays_json"
                       value="<?= oncallforms_escape($holidaysJson) ?>">
                <div class="oncallforms-calendar"
                     aria-label="<?= oncallforms_escape(__('Calendario de festivos', 'oncallforms')) ?>">
                    <div class="oncallforms-calendar__header">
                        <button class="btn btn-outline-secondary" type="button" data-calendar-previous
                                aria-label="<?= oncallforms_escape(__('Mes anterior', 'oncallforms')) ?>">
                            <i class="ti ti-chevron-left" aria-hidden="true"></i>
                        </button>
                        <strong data-calendar-title></strong>
                        <button class="btn btn-outline-secondary" type="button" data-calendar-next
                                aria-label="<?= oncallforms_escape(__('Mes siguiente', 'oncallforms')) ?>">
                            <i class="ti ti-chevron-right" aria-hidden="true"></i>
                        </button>
                    </div>
                    <div class="oncallforms-calendar__weekdays" aria-hidden="true">
                        <?php foreach (['L', 'M', 'X', 'J', 'V', 'S', 'D'] as $dayLabel) : ?>
                            <span><?= $dayLabel ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="oncallforms-calendar__days" role="grid" data-calendar-days></div>
                </div>
                <div class="oncallforms-holidays__list">
                    <h4><?= oncallforms_escape(__('Festivos seleccionados', 'oncallforms')) ?></h4>
                    <p class="text-secondary" data-holidays-empty>
                        <?= oncallforms_escape(__('Todavía no hay festivos seleccionados.', 'oncallforms')) ?>
                    </p>
                    <div data-holidays-list></div>
                </div>
            </div>

            <div class="card card-sm mb-4">
                <div class="card-body">
                    <h4><?= oncallforms_escape(__('Importar festivos desde CSV', 'oncallforms')) ?></h4>
                    <p class="text-secondary mb-2">
                        <?= oncallforms_escape(
                            __(
                                'Archivo UTF-8, separado por comas, con cabecera fecha,nombre. '
                                . 'La fecha usa AAAA-MM-DD y el nombre puede quedar vacío. '
                                . 'Los datos se combinan con los festivos existentes.',
                                'oncallforms'
                            )
                        ) ?>
                    </p>
                    <pre class="bg-light p-2 rounded">fecha,nombre
2027-01-01,Año Nuevo
2027-05-30,Día de Canarias</pre>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label" for="holidays_csv">
                                <?= oncallforms_escape(__('Archivo CSV de festivos', 'oncallforms')) ?>
                            </label>
                            <input class="form-control" id="holidays_csv" name="holidays_csv"
                                   type="file" accept=".csv,text/csv">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary w-100" type="submit" name="import_holidays" value="1"
                                    formnovalidate>
                                <i class="ti ti-file-import" aria-hidden="true"></i>
                                <?= oncallforms_escape(__('Importar CSV', 'oncallforms')) ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <h3><?= oncallforms_escape(__('Mensaje de aviso', 'oncallforms')) ?></h3>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label" for="modal_title">
                        <?= oncallforms_escape(__('Título del aviso', 'oncallforms')) ?>
                    </label>
                    <input class="form-control" id="modal_title" name="modal_title" maxlength="160" required
                           value="<?= oncallforms_escape($config['modal_title']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="modal_body">
                        <?= oncallforms_escape(__('Mensaje', 'oncallforms')) ?>
                    </label>
                    <textarea class="form-control" id="modal_body" name="modal_body" maxlength="2000" rows="4"
                              required><?= oncallforms_escape($config['modal_body']) ?></textarea>
                    <div class="form-hint">
                        <?= oncallforms_escape(
                            __('Solo texto plano; se eliminan el HTML y los scripts.', 'oncallforms')
                        ) ?>
                    </div>
                </div>
                <?php foreach (
                    [
                        'checkbox_text' => __('Texto de la casilla de aceptación', 'oncallforms'),
                        'oncall_button_text' => __('Texto del botón de guardia', 'oncallforms'),
                        'continue_button_text' => __('Texto del botón para continuar', 'oncallforms'),
                    ] as $field => $label
) : ?>
                    <div class="col-md-4">
                        <label class="form-label" for="<?= oncallforms_escape($field) ?>">
                            <?= oncallforms_escape($label) ?>
                        </label>
                        <input class="form-control" id="<?= oncallforms_escape($field) ?>"
                               name="<?= oncallforms_escape($field) ?>"
                               maxlength="300" required value="<?= oncallforms_escape($config[$field]) ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <h3><?= oncallforms_escape(__('Apariencia', 'oncallforms')) ?></h3>
            <div class="row g-3">
                <?php foreach (
                    [
                        'card_background' => __('Color de fondo', 'oncallforms'),
                        'card_border' => __('Color del borde', 'oncallforms'),
                        'card_text' => __('Color del texto', 'oncallforms'),
                    ] as $field => $label
) : ?>
                    <div class="col-md-3">
                        <label class="form-label" for="<?= oncallforms_escape($field) ?>">
                            <?= oncallforms_escape($label) ?>
                        </label>
                        <input class="form-control form-control-color w-100" id="<?= oncallforms_escape($field) ?>"
                               name="<?= oncallforms_escape($field) ?>" type="color" required
                               value="<?= oncallforms_escape($config[$field]) ?>">
                    </div>
                <?php endforeach; ?>
                <div class="col-md-3">
                    <label class="form-label" for="badge_text">
                        <?= oncallforms_escape(__('Distintivo', 'oncallforms')) ?>
                    </label>
                    <input class="form-control" id="badge_text" name="badge_text" maxlength="60" required
                           value="<?= oncallforms_escape($config['badge_text']) ?>">
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <input type="hidden" name="_glpi_csrf_token" value="<?= oncallforms_escape(Session::getNewCSRFToken()) ?>">
            <button class="btn btn-primary" type="submit">
                <?= oncallforms_escape(__('Guardar', 'oncallforms')) ?>
            </button>
        </div>
    </form>
</div>
<?php Html::footer(); ?>
