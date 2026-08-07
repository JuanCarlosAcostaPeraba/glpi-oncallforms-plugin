<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;

final class HolidayCalendar
{
    private const MAX_CSV_BYTES = 1_048_576;

    /**
     * @param mixed $value
     * @return list<array{date: string, name: string}>
     */
    public static function normalize($value): array
    {
        if (is_string($value)) {
            try {
                $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                throw new InvalidArgumentException(
                    __('La lista de festivos no tiene un formato válido.', 'oncallforms')
                );
            }
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException(__('La lista de festivos no es válida.', 'oncallforms'));
        }

        $byDate = [];
        foreach ($value as $holiday) {
            if (!is_array($holiday)) {
                throw new InvalidArgumentException(__('Hay un festivo con formato incorrecto.', 'oncallforms'));
            }

            $date = trim(is_scalar($holiday['date'] ?? null) ? (string) $holiday['date'] : '');
            if (!self::isValidDate($date)) {
                throw new InvalidArgumentException(sprintf(
                    __('La fecha de festivo «%s» no es válida. Use AAAA-MM-DD.', 'oncallforms'),
                    $date
                ));
            }

            $rawName = is_scalar($holiday['name'] ?? null) ? (string) $holiday['name'] : '';
            if (preg_match('//u', $rawName) !== 1) {
                throw new InvalidArgumentException(sprintf(
                    __('El nombre del festivo %s no está codificado en UTF-8.', 'oncallforms'),
                    $date
                ));
            }
            $name = trim(strip_tags($rawName));
            if (strlen($name) > 160) {
                throw new InvalidArgumentException(sprintf(
                    __('El nombre del festivo %s supera los 160 caracteres.', 'oncallforms'),
                    $date
                ));
            }

            $byDate[$date] = ['date' => $date, 'name' => $name];
        }

        ksort($byDate);
        return array_values($byDate);
    }

    /** @param list<array{date: string, name: string}> $holidays */
    public static function encode(array $holidays): string
    {
        return json_encode(self::normalize($holidays), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param list<array{date: string, name: string}> $existing
     * @param list<array{date: string, name: string}> $imported
     * @return list<array{date: string, name: string}>
     */
    public static function merge(array $existing, array $imported): array
    {
        return self::normalize(array_merge($existing, $imported));
    }

    /** @return list<array{date: string, name: string}> */
    public static function fromCsvFile(string $path): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new InvalidArgumentException(__('No se ha podido leer el archivo CSV.', 'oncallforms'));
        }

        $size = filesize($path);
        if ($size === false || $size > self::MAX_CSV_BYTES) {
            throw new InvalidArgumentException(__('El CSV no puede superar 1 MB.', 'oncallforms'));
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new InvalidArgumentException(__('No se ha podido abrir el archivo CSV.', 'oncallforms'));
        }

        try {
            return self::readCsv($handle);
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param resource $handle
     * @return list<array{date: string, name: string}>
     */
    private static function readCsv($handle): array
    {
        $header = fgetcsv($handle, 0, ',', '"', '');
        if (!is_array($header)) {
            throw new InvalidArgumentException(__('El CSV está vacío.', 'oncallforms'));
        }

        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($header[0] ?? '')) ?? '';
        $header = array_map(static fn ($value): string => strtolower(trim((string) $value)), $header);
        if ($header !== ['fecha', 'nombre']) {
            throw new InvalidArgumentException(
                __('La cabecera del CSV debe ser exactamente: fecha,nombre', 'oncallforms')
            );
        }

        $rows = [];
        $line = 1;
        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            ++$line;
            if ($row === [null] || (count($row) === 1 && trim((string) $row[0]) === '')) {
                continue;
            }
            if (count($row) !== 2) {
                throw new InvalidArgumentException(sprintf(
                    __('La línea %d del CSV debe contener fecha y nombre.', 'oncallforms'),
                    $line
                ));
            }
            $rows[] = ['date' => trim((string) $row[0]), 'name' => trim((string) $row[1])];
        }

        if ($rows === []) {
            throw new InvalidArgumentException(__('El CSV no contiene ningún festivo.', 'oncallforms'));
        }

        return self::normalize($rows);
    }

    private static function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
