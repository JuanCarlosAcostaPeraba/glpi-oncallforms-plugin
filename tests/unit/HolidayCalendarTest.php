<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Unit;

use GlpiPlugin\Oncallforms\HolidayCalendar;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HolidayCalendarTest extends TestCase
{
    public function testNormalizesSortsAndDeduplicatesHolidays(): void
    {
        $result = HolidayCalendar::normalize([
            ['date' => '2027-05-30', 'name' => 'Día de Canarias'],
            ['date' => '2027-01-01', 'name' => 'Año Nuevo'],
            ['date' => '2027-05-30', 'name' => 'Canarias actualizado'],
        ]);

        self::assertSame([
            ['date' => '2027-01-01', 'name' => 'Año Nuevo'],
            ['date' => '2027-05-30', 'name' => 'Canarias actualizado'],
        ], $result);
    }

    public function testImportsUtf8CsvWithOptionalNames(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'oncallforms-');
        self::assertIsString($path);
        file_put_contents($path, "\xEF\xBB\xBFfecha,nombre\n2027-01-01,Año Nuevo\n2027-04-02,\n");

        try {
            self::assertSame([
                ['date' => '2027-01-01', 'name' => 'Año Nuevo'],
                ['date' => '2027-04-02', 'name' => ''],
            ], HolidayCalendar::fromCsvFile($path));
        } finally {
            unlink($path);
        }
    }

    #[DataProvider('invalidCsvFiles')]
    public function testRejectsInvalidCsv(string $contents): void
    {
        $path = tempnam(sys_get_temp_dir(), 'oncallforms-');
        self::assertIsString($path);
        file_put_contents($path, $contents);

        try {
            $this->expectException(InvalidArgumentException::class);
            HolidayCalendar::fromCsvFile($path);
        } finally {
            unlink($path);
        }
    }

    /** @return iterable<string, array{string}> */
    public static function invalidCsvFiles(): iterable
    {
        yield 'wrong header' => ["date,name\n2027-01-01,New year\n"];
        yield 'invalid date' => ["fecha,nombre\n2027-02-29,Fecha imposible\n"];
        yield 'extra column' => ["fecha,nombre\n2027-01-01,Año Nuevo,extra\n"];
        yield 'no rows' => ["fecha,nombre\n"];
        yield 'invalid utf8' => ["fecha,nombre\n2027-01-01,\xC3\x28\n"];
    }
}
