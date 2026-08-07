<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

use DateTimeZone;
use InvalidArgumentException;

final class Config
{
    public const CONTEXT = 'plugin:oncallforms';

    /** @var array<string, string> */
    public const DEFAULTS = [
        'oncall_form_id' => '0',
        'catalog_category_id' => '27',
        'business_start' => '08:00',
        'business_end' => '15:00',
        'business_days' => '[1,2,3,4,5]',
        'holidays' => '[]',
        'timezone' => '',
        'modal_title' => 'Fuera del horario laboral habitual',
        'modal_body' => 'Está accediendo al catálogo de incidencias fuera del horario laboral habitual. '
            . 'Utilice el formulario de guardia para solicitudes urgentes.',
        'checkbox_text' => 'He leído y acepto las condiciones.',
        'oncall_button_text' => 'Ir al formulario de guardia',
        'continue_button_text' => 'Continuar en el catálogo',
        'card_background' => '#FFF3CD',
        'card_border' => '#FFB300',
        'card_text' => '#3D2E00',
        'badge_text' => 'GUARDIAS',
        'catalog_card_background' => '#D1E7DD',
        'catalog_card_border' => '#198754',
        'catalog_card_text' => '#0F5132',
    ];

    /** @return array<string, mixed> */
    public static function get(): array
    {
        $raw = self::DEFAULTS;
        if (class_exists(\Config::class)) {
            $raw = array_replace($raw, \Config::getConfigurationValues(self::CONTEXT));
        }

        $legacyDefaults = [
            'modal_title' => 'Outside normal business hours',
            'modal_body' => 'You are accessing the normal incident form outside normal business hours. '
                . 'Use the on-call form for urgent requests.',
            'checkbox_text' => 'I have read and accept the conditions.',
            'oncall_button_text' => 'Go to the on-call form',
            'continue_button_text' => 'Continue to the normal form',
        ];
        foreach ($legacyDefaults as $key => $legacyValue) {
            if ($raw[$key] === $legacyValue) {
                $raw[$key] = self::DEFAULTS[$key];
            }
        }

        $supersededDefaults = [
            'modal_body' => 'Está accediendo al formulario normal de incidencias fuera del horario laboral habitual. '
                . 'Utilice el formulario de guardia para solicitudes urgentes.',
            'continue_button_text' => 'Continuar al formulario normal',
        ];
        foreach ($supersededDefaults as $key => $supersededValue) {
            if ($raw[$key] === $supersededValue) {
                $raw[$key] = self::DEFAULTS[$key];
            }
        }

        $days = json_decode((string) $raw['business_days'], true);
        if (!is_array($days) || $days === []) {
            $days = [1, 2, 3, 4, 5];
        }

        $timezone = (string) $raw['timezone'];
        if (!in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            $timezone = date_default_timezone_get();
        }

        return [
            'oncall_form_id' => max(0, (int) $raw['oncall_form_id']),
            'catalog_category_id' => max(0, (int) $raw['catalog_category_id']),
            'business_start' => (string) $raw['business_start'],
            'business_end' => (string) $raw['business_end'],
            'business_days' => array_values(array_unique(array_map('intval', $days))),
            'holidays' => HolidayCalendar::normalize((string) $raw['holidays']),
            'timezone' => $timezone,
            'modal_title' => (string) $raw['modal_title'],
            'modal_body' => (string) $raw['modal_body'],
            'checkbox_text' => (string) $raw['checkbox_text'],
            'oncall_button_text' => (string) $raw['oncall_button_text'],
            'continue_button_text' => (string) $raw['continue_button_text'],
            'card_background' => (string) $raw['card_background'],
            'card_border' => (string) $raw['card_border'],
            'card_text' => (string) $raw['card_text'],
            'badge_text' => (string) $raw['badge_text'],
            'catalog_card_background' => (string) $raw['catalog_card_background'],
            'catalog_card_border' => (string) $raw['catalog_card_border'],
            'catalog_card_text' => (string) $raw['catalog_card_text'],
        ];
    }

    public static function install(): void
    {
        $existing = \Config::getConfigurationValues(self::CONTEXT);
        \Config::setConfigurationValues(self::CONTEXT, array_diff_key(self::DEFAULTS, $existing));
    }

    /** @param array<string, mixed> $input */
    public static function save(array $input, FormResolver $resolver): void
    {
        $values = self::normalize($input);
        $resolver->assertSelectable((int) $values['oncall_form_id']);

        \Config::setConfigurationValues(self::CONTEXT, $values);
    }

    /** @param list<array{date: string, name: string}> $holidays */
    public static function saveHolidays(array $holidays): void
    {
        \Config::setConfigurationValues(self::CONTEXT, [
            'holidays' => HolidayCalendar::encode($holidays),
        ]);
    }

    /** @param array<string, mixed> $input @return array<string, string> */
    public static function normalize(array $input): array
    {
        $oncallId = self::positiveId(
            $input['oncall_form_id'] ?? null,
            __('Seleccione un formulario de guardia válido.', 'oncallforms')
        );
        $categoryId = self::positiveId(
            $input['catalog_category_id'] ?? null,
            __('Introduzca una categoría del catálogo válida.', 'oncallforms')
        );
        $start = self::time($input['business_start'] ?? null);
        $end = self::time($input['business_end'] ?? null);
        if ($start >= $end) {
            throw new InvalidArgumentException(
                __('La hora de inicio debe ser anterior a la hora de fin.', 'oncallforms')
            );
        }

        $days = array_values(array_unique(array_map('intval', (array) ($input['business_days'] ?? []))));
        sort($days);
        if ($days === [] || array_diff($days, range(1, 7)) !== []) {
            throw new InvalidArgumentException(__('Seleccione al menos un día laborable válido.', 'oncallforms'));
        }

        $timezone = (string) ($input['timezone'] ?? '');
        if (!in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            throw new InvalidArgumentException(__('Seleccione una zona horaria válida.', 'oncallforms'));
        }

        return [
            'oncall_form_id' => (string) $oncallId,
            'catalog_category_id' => (string) $categoryId,
            'business_start' => $start,
            'business_end' => $end,
            'business_days' => json_encode($days, JSON_THROW_ON_ERROR),
            'holidays' => HolidayCalendar::encode(
                HolidayCalendar::normalize($input['holidays_json'] ?? '[]')
            ),
            'timezone' => $timezone,
            'modal_title' => self::plainText($input['modal_title'] ?? '', 160),
            'modal_body' => self::plainText($input['modal_body'] ?? '', 2000),
            'checkbox_text' => self::plainText($input['checkbox_text'] ?? '', 300),
            'oncall_button_text' => self::plainText($input['oncall_button_text'] ?? '', 160),
            'continue_button_text' => self::plainText($input['continue_button_text'] ?? '', 160),
            'card_background' => self::color($input['card_background'] ?? ''),
            'card_border' => self::color($input['card_border'] ?? ''),
            'card_text' => self::color($input['card_text'] ?? ''),
            'badge_text' => self::plainText($input['badge_text'] ?? '', 60),
            'catalog_card_background' => self::color($input['catalog_card_background'] ?? ''),
            'catalog_card_border' => self::color($input['catalog_card_border'] ?? ''),
            'catalog_card_text' => self::color($input['catalog_card_text'] ?? ''),
        ];
    }

    /** @param mixed $value */
    private static function positiveId($value, string $errorMessage): int
    {
        if (!is_scalar($value) || !preg_match('/^[1-9]\d*$/D', (string) $value)) {
            throw new InvalidArgumentException($errorMessage);
        }
        return (int) $value;
    }

    /** @param mixed $value */
    private static function time($value): string
    {
        $time = is_scalar($value) ? (string) $value : '';
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D', $time)) {
            throw new InvalidArgumentException(__('Introduzca una hora válida con el formato HH:MM.', 'oncallforms'));
        }
        return $time;
    }

    /** @param mixed $value */
    private static function color($value): string
    {
        $color = is_scalar($value) ? strtoupper((string) $value) : '';
        if (!preg_match('/^#[0-9A-F]{6}$/D', $color)) {
            throw new InvalidArgumentException(__('Los colores deben usar el formato #RRGGBB.', 'oncallforms'));
        }
        return $color;
    }

    /** @param mixed $value */
    private static function plainText($value, int $maxLength): string
    {
        $text = trim(strip_tags(is_scalar($value) ? (string) $value : ''));
        // Byte length is deliberately conservative and avoids relying on an
        // optional PHP extension for a security boundary.
        if ($text === '' || strlen($text) > $maxLength) {
            throw new InvalidArgumentException(
                __('Un texto obligatorio está vacío o es demasiado largo.', 'oncallforms')
            );
        }
        return $text;
    }
}
