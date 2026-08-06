<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

final readonly class Schedule
{
    /** @param list<int> $businessDays ISO-8601 days, Monday=1 and Sunday=7. */
    public function __construct(
        private string $start,
        private string $end,
        private array $businessDays,
        private DateTimeZone $timezone,
    ) {
        self::assertTime($start, 'start');
        self::assertTime($end, 'end');
        if ($start >= $end) {
            throw new InvalidArgumentException('The start time must be before the end time.');
        }
        if ($businessDays === []) {
            throw new InvalidArgumentException('At least one business day is required.');
        }
        foreach ($businessDays as $day) {
            if ($day < 1 || $day > 7) {
                throw new InvalidArgumentException('Business days must use ISO values from 1 to 7.');
            }
        }
    }

    public function isBusinessTime(DateTimeInterface $dateTime): bool
    {
        $local = DateTimeImmutable::createFromInterface($dateTime)->setTimezone($this->timezone);
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
            throw new InvalidArgumentException(sprintf('Invalid %s time.', $field));
        }
    }
}
