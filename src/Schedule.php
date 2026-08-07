<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

final readonly class Schedule
{
    /**
     * @param list<int> $businessDays ISO-8601 days, Monday=1 and Sunday=7.
     * @param list<array{date: string, name: string}> $holidays
     */
    public function __construct(
        private string $start,
        private string $end,
        private array $businessDays,
        private DateTimeZone $timezone,
        private array $holidays = [],
    ) {
        self::assertTime($start, 'inicio');
        self::assertTime($end, 'fin');
        if ($start >= $end) {
            throw new InvalidArgumentException('La hora de inicio debe ser anterior a la hora de fin.');
        }
        if ($businessDays === []) {
            throw new InvalidArgumentException('Se requiere al menos un día laborable.');
        }
        foreach ($businessDays as $day) {
            if ($day < 1 || $day > 7) {
                throw new InvalidArgumentException('Los días laborables deben usar valores ISO del 1 al 7.');
            }
        }
    }

    public function isBusinessTime(DateTimeInterface $dateTime): bool
    {
        $local = DateTimeImmutable::createFromInterface($dateTime)->setTimezone($this->timezone);
        foreach ($this->holidays as $holiday) {
            if ($holiday['date'] === $local->format('Y-m-d')) {
                return false;
            }
        }
        $day = (int) $local->format('N');
        $time = $local->format('H:i');

        return in_array($day, $this->businessDays, true)
            && $time >= $this->start
            && $time < $this->end;
    }

    public function isOnCall(DateTimeInterface $dateTime): bool
    {
        return !$this->isBusinessTime($dateTime);
    }

    public function getTimezone(): DateTimeZone
    {
        return $this->timezone;
    }

    private static function assertTime(string $time, string $field): void
    {
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D', $time)) {
            throw new InvalidArgumentException(sprintf('La hora de %s no es válida.', $field));
        }
    }
}
