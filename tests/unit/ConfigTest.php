<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms\Tests\Unit;

use GlpiPlugin\Oncallforms\Config;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function testUsesSpanishDefaultTexts(): void
    {
        self::assertSame('Fuera del horario laboral habitual', Config::DEFAULTS['modal_title']);
        self::assertSame('He leído y acepto las condiciones.', Config::DEFAULTS['checkbox_text']);
        self::assertSame('Ir al formulario de guardia', Config::DEFAULTS['oncall_button_text']);
        self::assertSame('Continuar en el catálogo', Config::DEFAULTS['continue_button_text']);
    }

    public function testNormalizesSafeConfiguration(): void
    {
        $result = Config::normalize($this->validInput());

        self::assertSame('27', $result['oncall_form_id']);
        self::assertSame('27', $result['catalog_category_id']);
        self::assertSame('[1,2,3,4,5]', $result['business_days']);
        self::assertSame('#AABBCC', $result['card_background']);
        self::assertSame('Text only', $result['modal_body']);
    }

    /** @param array<string, mixed> $changes */
    #[DataProvider('invalidInputs')]
    public function testRejectsInvalidValues(array $changes): void
    {
        $this->expectException(InvalidArgumentException::class);
        Config::normalize(array_replace($this->validInput(), $changes));
    }

    /** @return iterable<string, array{array<string, mixed>}> */
    public static function invalidInputs(): iterable
    {
        yield 'non integer form' => [['oncall_form_id' => '1 OR 1=1']];
        yield 'zero category' => [['catalog_category_id' => '0']];
        yield 'invalid category' => [['catalog_category_id' => '27 OR 1=1']];
        yield 'invalid time' => [['business_start' => '8am']];
        yield 'reversed interval' => [['business_start' => '16:00']];
        yield 'empty days' => [['business_days' => []]];
        yield 'invalid day' => [['business_days' => [1, 8]]];
        yield 'invalid timezone' => [['timezone' => 'Mars/Olympus']];
        yield 'short hex color' => [['card_border' => '#FFF']];
        yield 'css injection' => [['card_text' => '#000000;display:none']];
        yield 'empty text' => [['checkbox_text' => '']];
    }

    /** @return array<string, mixed> */
    private function validInput(): array
    {
        return [
            'oncall_form_id' => '27',
            'catalog_category_id' => '27',
            'business_start' => '08:00',
            'business_end' => '15:00',
            'business_days' => ['5', '1', '2', '3', '4', '1'],
            'timezone' => 'Atlantic/Canary',
            'modal_title' => 'Warning',
            'modal_body' => '<b>Text</b> only',
            'checkbox_text' => 'I accept',
            'oncall_button_text' => 'On call',
            'continue_button_text' => 'Continue',
            'card_background' => '#aabbcc',
            'card_border' => '#123456',
            'card_text' => '#000000',
            'badge_text' => 'ON CALL',
        ];
    }
}
