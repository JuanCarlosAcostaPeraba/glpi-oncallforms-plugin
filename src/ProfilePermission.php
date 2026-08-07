<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

final class ProfilePermission extends \Profile
{
    public static $rightname = 'profile';

    public function getTabNameForItem(\CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item instanceof \Profile && ($item->fields['interface'] ?? '') === 'central') {
            return self::createTabEntry(
                __('Formularios de guardia', 'oncallforms'),
                0,
                self::class,
                'ti ti-calendar-event'
            );
        }

        return '';
    }

    public static function displayTabContentForItem(
        \CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ): bool {
        if (!$item instanceof \Profile) {
            return false;
        }

        $canEdit = \Session::haveRight('profile', UPDATE);
        if ($canEdit) {
            echo "<form method='post' action='" . $item->getFormURL() . "'>";
        }

        $item->displayRightsChoiceMatrix(self::getRights(), [
            'canedit' => $canEdit,
            'default_class' => 'tab_bg_2',
            'title' => __('Configuración', 'oncallforms'),
        ]);

        if ($canEdit) {
            echo \Html::hidden('id', ['value' => $item->getID()]);
            echo \Html::submit(_sx('button', 'Guardar'), ['name' => 'update']);
            \Html::closeForm();
        }

        return true;
    }

    /** @return list<array{rights: array<int, string>, label: string, field: string}> */
    public static function getRights(): array
    {
        return [[
            'rights' => [
                READ => __('Ver la configuración', 'oncallforms'),
                UPDATE => __('Modificar la configuración', 'oncallforms'),
            ],
            'label' => __('Configuración de Formularios de guardia', 'oncallforms'),
            'field' => Access::RIGHT,
        ]];
    }

    public static function installRights(): void
    {
        if (countElementsInTable('glpi_profilerights', ['name' => Access::RIGHT]) === 0) {
            \ProfileRight::addProfileRights([Access::RIGHT]);
        }

        if (!isset($_SESSION['glpiactiveprofile']['id'])) {
            return;
        }

        $profileId = (int) $_SESSION['glpiactiveprofile']['id'];
        $profile = new \Profile();
        if (!$profile->getFromDB($profileId)) {
            return;
        }

        $profile->update([
            'id' => $profileId,
            '_' . Access::RIGHT => [
                READ => 1,
                UPDATE => 1,
            ],
        ]);
        $_SESSION['glpiactiveprofile'][Access::RIGHT] = READ | UPDATE;
    }

    public static function uninstallRights(): void
    {
        \ProfileRight::deleteProfileRights([Access::RIGHT]);
        unset($_SESSION['glpiactiveprofile'][Access::RIGHT]);
    }
}
