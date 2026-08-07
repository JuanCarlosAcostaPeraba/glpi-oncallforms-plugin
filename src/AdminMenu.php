<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

final class AdminMenu extends \CommonGLPI
{
    public static function getTypeName($nb = 0): string
    {
        return __('Formularios de guardia', 'oncallforms');
    }

    public static function getMenuName(): string
    {
        return self::getTypeName();
    }

    public static function getIcon(): string
    {
        return 'ti ti-calendar-event';
    }

    public static function canView(): bool
    {
        return Access::canRead();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getSearchURL($full = true): string
    {
        return \Html::getPrefixedUrl('/plugins/oncallforms/front/config.form.php');
    }

    /** @return array<string, mixed> */
    public static function getAdditionalMenuOptions(): array
    {
        $url = self::getSearchURL(false);

        return [
            'menu' => [
                'title' => self::getTypeName(),
                'page' => $url,
                'icon' => self::getIcon(),
            ],
            'config' => [
                'title' => __('Configuración', 'oncallforms'),
                'page' => $url,
                'icon' => 'ti ti-settings',
            ],
        ];
    }
}
