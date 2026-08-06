<?php

declare(strict_types=1);

namespace GlpiPlugin\Oncallforms;

use Glpi\Form\AccessControl\FormAccessParameters;
use Glpi\Form\AccessControl\FormAccessControlManager;
use Glpi\Form\Form;
use Glpi\Form\ServiceCatalog\ItemRequest;
use Glpi\Form\ServiceCatalog\Provider\FormProvider;
use InvalidArgumentException;
use Session;

final class FormResolver
{
    /** @return array<int, string> */
    public function getSelectableOptions(): array
    {
        $options = [];
        foreach ($this->getSelectableForms() as $form) {
            $id = $form->getID();
            $name = $form->getServiceCatalogItemTitle() ?: (string) ($form->fields['name'] ?? '');
            $options[$id] = sprintf('%s — ID %d', $name, $id);
        }
        natcasesort($options);
        return $options;
    }

    public function assertSelectable(int $id): void
    {
        if ($this->resolveAccessible($id) === null) {
            throw new InvalidArgumentException(
                __(
                    'El formulario seleccionado no existe, está inactivo, eliminado o fuera del ámbito permitido.',
                    'oncallforms'
                )
            );
        }
    }

    public function resolveAccessible(int $id): ?Form
    {
        if (Session::getCurrentSessionInfo() === null) {
            $form = Form::getById($id);
            if (
                !$form instanceof Form
                || !(bool) ($form->fields['is_active'] ?? false)
                || (bool) ($form->fields['is_deleted'] ?? false)
            ) {
                return null;
            }

            $allowed = FormAccessControlManager::getInstance()->canAnswerForm(
                $form,
                new FormAccessParameters()
            );
            return $allowed ? $form : null;
        }

        foreach ($this->getSelectableForms() as $form) {
            if ($form->getID() === $id) {
                return $form;
            }
        }
        return null;
    }

    /** @return list<Form> */
    private function getSelectableForms(): array
    {
        $session = Session::getCurrentSessionInfo();
        if ($session === null) {
            return [];
        }

        $request = new ItemRequest(
            access_parameters: new FormAccessParameters(session_info: $session),
            items_per_page: PHP_INT_MAX,
        );

        return FormProvider::getInstance()->getItems($request);
    }
}
