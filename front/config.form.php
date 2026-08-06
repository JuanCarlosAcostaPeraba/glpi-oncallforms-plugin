<?php

declare(strict_types=1);

use GlpiPlugin\Oncallforms\Config;
use GlpiPlugin\Oncallforms\FormResolver;

if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', '../../..');
}
include GLPI_ROOT . '/inc/includes.php';

Session::checkRight('config', UPDATE);

$resolver = new FormResolver();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Session::checkRight('config', UPDATE);
    Session::checkCSRF($_POST);

    try {
        Config::save($_POST, $resolver);
        Session::addMessageAfterRedirect(__('Settings saved.', 'oncallforms'), true, INFO);
    } catch (InvalidArgumentException $exception) {
        Session::addMessageAfterRedirect($exception->getMessage(), true, ERROR);
    }
    Html::redirect($_SERVER['PHP_SELF']);
}

$config = Config::get();
$forms = $resolver->getSelectableOptions();
$weekdays = [
    1 => __('Monday', 'oncallforms'),
    2 => __('Tuesday', 'oncallforms'),
    3 => __('Wednesday', 'oncallforms'),
    4 => __('Thursday', 'oncallforms'),
    5 => __('Friday', 'oncallforms'),
    6 => __('Saturday', 'oncallforms'),
    7 => __('Sunday', 'oncallforms'),
];

/** @param mixed $value */
function oncallforms_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

Html::header(
    __('On-call Forms configuration', 'oncallforms'),
    $_SERVER['PHP_SELF'],
    'config',
    'plugins'
);
?>
<div class="container-xl">
    <form method="post" action="<?= oncallforms_escape($_SERVER['PHP_SELF']) ?>" class="card">
        <div class="card-header">
            <h2 class="card-title"><?= oncallforms_escape(__('On-call Forms', 'oncallforms')) ?></h2>
        </div>
        <div class="card-body">
            <h3><?= oncallforms_escape(__('Forms', 'oncallforms')) ?></h3>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label" for="dropdown_oncall_form_id0">
                        <?= oncallforms_escape(__('On-call form', 'oncallforms')) ?>
                    </label>
                    <?php Dropdown::showFromArray('oncall_form_id', $forms, [
                        'value' => $config['oncall_form_id'],
                        'display_emptychoice' => true,
                        'width' => '100%',
                        'rand' => 0,
                        'aria_label' => __('On-call form', 'oncallforms'),
                    ]); ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="dropdown_normal_form_id0">
                        <?= oncallforms_escape(__('Normal incident form', 'oncallforms')) ?>
                    </label>
                    <?php Dropdown::showFromArray('normal_form_id', $forms, [
                        'value' => $config['normal_form_id'],
                        'display_emptychoice' => true,
                        'width' => '100%',
                        'rand' => 0,
                        'aria_label' => __('Normal incident form', 'oncallforms'),
                    ]); ?>
                </div>
            </div>

            <h3><?= oncallforms_escape(__('Business schedule', 'oncallforms')) ?></h3>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label" for="business_start">
                        <?= oncallforms_escape(__('Start', 'oncallforms')) ?>
                    </label>
                    <input class="form-control" id="business_start" name="business_start" type="time" required
                           value="<?= oncallforms_escape($config['business_start']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="business_end">
                        <?= oncallforms_escape(__('End', 'oncallforms')) ?>
                    </label>
                    <input class="form-control" id="business_end" name="business_end" type="time" required
                           value="<?= oncallforms_escape($config['business_end']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="dropdown_timezone0">
                        <?= oncallforms_escape(__('Time zone', 'oncallforms')) ?>
                    </label>
                    <?php
                    $timezones = array_combine(DateTimeZone::listIdentifiers(), DateTimeZone::listIdentifiers());
                    Dropdown::showFromArray('timezone', $timezones ?: [], [
                        'value' => $config['timezone'],
                        'width' => '100%',
                        'rand' => 0,
                        'aria_label' => __('Time zone', 'oncallforms'),
                    ]);
                    ?>
                    <div class="form-hint">
                        <?= oncallforms_escape(sprintf(
                            __('Effective time zone: %s', 'oncallforms'),
                            $config['timezone']
                        )) ?>
                    </div>
                </div>
            </div>
            <fieldset class="mb-4">
                <legend class="form-label"><?= oncallforms_escape(__('Business days', 'oncallforms')) ?></legend>
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

            <h3><?= oncallforms_escape(__('Warning message', 'oncallforms')) ?></h3>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label" for="modal_title">
                        <?= oncallforms_escape(__('Modal title', 'oncallforms')) ?>
                    </label>
                    <input class="form-control" id="modal_title" name="modal_title" maxlength="160" required
                           value="<?= oncallforms_escape($config['modal_title']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="modal_body">
                        <?= oncallforms_escape(__('Message', 'oncallforms')) ?>
                    </label>
                    <textarea class="form-control" id="modal_body" name="modal_body" maxlength="2000" rows="4"
                              required><?= oncallforms_escape($config['modal_body']) ?></textarea>
                    <div class="form-hint">
                        <?= oncallforms_escape(
                            __('Plain text only; HTML and scripts are removed.', 'oncallforms')
                        ) ?>
                    </div>
                </div>
                <?php foreach (
                    [
                        'checkbox_text' => __('Checkbox text', 'oncallforms'),
                        'oncall_button_text' => __('On-call button text', 'oncallforms'),
                        'continue_button_text' => __('Continue button text', 'oncallforms'),
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

            <h3><?= oncallforms_escape(__('Appearance', 'oncallforms')) ?></h3>
            <div class="row g-3">
                <?php foreach (
                    [
                        'card_background' => __('Background color', 'oncallforms'),
                        'card_border' => __('Border color', 'oncallforms'),
                        'card_text' => __('Text color', 'oncallforms'),
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
                        <?= oncallforms_escape(__('Badge', 'oncallforms')) ?>
                    </label>
                    <input class="form-control" id="badge_text" name="badge_text" maxlength="60" required
                           value="<?= oncallforms_escape($config['badge_text']) ?>">
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            <input type="hidden" name="_glpi_csrf_token" value="<?= oncallforms_escape(Session::getNewCSRFToken()) ?>">
            <button class="btn btn-primary" type="submit"><?= oncallforms_escape(__('Save', 'oncallforms')) ?></button>
        </div>
    </form>
</div>
<?php Html::footer(); ?>
