<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Unit;

use DateTimeImmutable;
use DateTimeZone;
use GlpiPlugin\Oncallforms\Schedule;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ScheduleTest extends TestCase
{
    private Schedule $schedule;

    protected function setUp(): void
    {
        $this->schedule = new Schedule(
            '08:00',
            '15:00',
            [1, 2, 3, 4, 5],
            new DateTimeZone('Atlantic/Canary'),
        );
    }

    /** @return iterable<string, array{string, bool}> */
    public static function boundaries(): iterable
    {
        yield 'Monday 00:00' => ['2026-08-03 00:00:00', true];
        yield 'Monday 07:59' => ['2026-08-03 07:59:59', true];
        yield 'Monday 08:00' => ['2026-08-03 08:00:00', false];
        yield 'Monday 14:59' => ['2026-08-03 14:59:59', false];
        yield 'Monday 15:00' => ['2026-08-03 15:00:00', true];
        yield 'Friday 23:59' => ['2026-08-07 23:59:59', true];
        yield 'Saturday 00:00' => ['2026-08-08 00:00:00', true];
        yield 'Saturday 12:00' => ['2026-08-08 12:00:00', true];
        yield 'Sunday 23:59' => ['2026-08-09 23:59:59', true];
    }

    #[DataProvider('boundaries')]
    public function testOnCallBoundaries(string $localDateTime, bool $expected): void
    {
        $dateTime = new DateTimeImmutable($localDateTime, new DateTimeZone('Atlantic/Canary'));
        self::assertSame($expected, $this->schedule->isOnCall($dateTime));
    }

    public function testConvertsInjectedInstantToConfiguredTimezone(): void
    {
        $utc = new DateTimeImmutable('2026-08-03 08:00:00', new DateTimeZone('UTC'));
        self::assertFalse($this->schedule->isOnCall($utc));
    }

    public function testConfigurableBusinessDaysAndDateChange(): void
    {
        $weekendSchedule = new Schedule('09:00', '10:00', [6], new DateTimeZone('UTC'));
        self::assertFalse($weekendSchedule->isOnCall(new DateTimeImmutable('2026-08-08 09:30:00 UTC')));
        self::assertTrue($weekendSchedule->isOnCall(new DateTimeImmutable('2026-08-09 09:30:00 UTC')));
    }

    /** @param list<int> $days */
    #[DataProvider('invalidConfigurations')]
    public function testRejectsInvalidConfiguration(string $start, string $end, array $days): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Schedule($start, $end, $days, new DateTimeZone('UTC'));
    }

    /** @return iterable<string, array{string, string, list<int>}> */
    public static function invalidConfigurations(): iterable
    {
        yield 'invalid start' => ['8:00', '15:00', [1]];
        yield 'invalid end' => ['08:00', '24:00', [1]];
        yield 'equal bounds' => ['08:00', '08:00', [1]];
        yield 'reversed bounds' => ['15:00', '08:00', [1]];
        yield 'no days' => ['08:00', '15:00', []];
        yield 'invalid day' => ['08:00', '15:00', [8]];
    }
}
