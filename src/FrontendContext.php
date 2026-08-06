<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

use DateTimeImmutable;
use DateTimeZone;
use Html;
use JsonException;

final readonly class FrontendContext
{
    /** @param array<string, mixed> $data */
    private function __construct(private array $data)
    {
    }

    public static function fromCurrentRequest(): self
    {
        $config = Config::get();
        $path = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?: '';
        $schedule = new Schedule(
            $config['business_start'],
            $config['business_end'],
            $config['business_days'],
            new DateTimeZone($config['timezone']),
        );
        $isOnCall = $schedule->isOnCall(new DateTimeImmutable('now', $schedule->getTimezone()));
        $normalId = self::renderedFormId($path);
        $isCatalog = preg_match('~/ServiceCatalog(?:/|$)~', $path) === 1;
        $needsOncallForm = $isCatalog || ($isOnCall && $normalId === $config['normal_form_id']);
        $oncall = $needsOncallForm
            ? (new FormResolver())->resolveAccessible($config['oncall_form_id'])
            : null;

        $data = [
            'catalog' => [
                'enabled' => $isCatalog && $oncall !== null,
                'formId' => $oncall?->getID(),
                'hidden' => !$isOnCall,
            ],
            'warning' => [
                'enabled' => $isOnCall && $normalId === $config['normal_form_id'] && $oncall !== null,
                'title' => __($config['modal_title'], 'oncallforms'),
                'body' => __($config['modal_body'], 'oncallforms'),
                'checkbox' => __($config['checkbox_text'], 'oncallforms'),
                'oncallButton' => __($config['oncall_button_text'], 'oncallforms'),
                'continueButton' => __($config['continue_button_text'], 'oncallforms'),
                'oncallUrl' => $oncall?->getServiceCatalogLink(),
                'cancelUrl' => Html::getPrefixedUrl('/ServiceCatalog'),
            ],
            'appearance' => [
                'background' => $config['card_background'],
                'border' => $config['card_border'],
                'text' => $config['card_text'],
                'badge' => $config['badge_text'],
            ],
        ];

        return new self($data);
    }

    public function isRelevant(): bool
    {
        return (bool) ($this->data['catalog']['enabled'] ?? false)
            || (bool) ($this->data['warning']['enabled'] ?? false);
    }

    public function toJson(): string
    {
        try {
            return json_encode(
                $this->data,
                JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            );
        } catch (JsonException) {
            return '{}';
        }
    }

    private static function renderedFormId(string $path): ?int
    {
        if (preg_match('~/Form/Render/(\d+)(?:/|$)~', $path, $matches) !== 1) {
            return null;
        }
        return (int) $matches[1];
    }
}
