# Architecture

## Verified GLPI 11 integration points

The implementation was checked against `glpi-project/glpi`, branch `11.0/bugfixes`, commit `55260a67b17cc19dd6c79f4c422b4b952c042cdb` (2026-08-06).

- Native forms are `Glpi\Form\Form` records in `glpi_forms_forms`. Relevant fields include `entities_id`, `is_recursive`, `is_active`, `is_deleted`, `forms_categories_id`, `is_pinned`, `name`, and `description`.
- The service catalog page is `GET /ServiceCatalog`; AJAX catalog refreshes use `GET /ServiceCatalog/Items`.
- A form is filled at `GET /Form/Render/{id}`. `RendererController` loads the numeric route ID and delegates authorization to `FormAccessControlManager`.
- `Glpi\Form\ServiceCatalog\Provider\FormProvider` selects active, non-deleted forms in the active entity scope, then calls `FormAccessControlManager::canAnswerForm()`.
- Card rendering is performed by `templates/components/helpdesk_forms/service_catalog_item.html.twig` and its nested variant. The card has no form-ID data attribute; its `href` comes from `Form::getServiceCatalogLink()` and is `/Form/Render/{id}`.
- Official asset hooks `ADD_JAVASCRIPT`, `ADD_CSS`, and `ADD_HEADER_TAG` are used. `DISPLAY_SERVICE_CATALOG` can append content but cannot filter the provider result.

## Design

`Config` stores global values in GLPI's `glpi_configs` through `Config::getConfigurationValues()` and `Config::setConfigurationValues()` under `plugin:oncallforms`. `Schedule` contains the only time decision. `FormResolver` reuses the official native form provider. `FrontendContext` converts server-side decisions into a minimal JSON meta tag. The JavaScript only renders those decisions.

The context is request-scoped. On the configured catalog category it contains the accessible on-call form ID, whether its card must be hidden, and validated colors/text. It also contains the warning data only when the server-side schedule says the current instant is on-call time.

## Catalog limitation

GLPI 11 has no public hook to remove an item from the built-in `FormProvider`, and `ServiceCatalogManager` is final. Replacing internal services would be more fragile than a narrow DOM adapter. The plugin therefore finds only links whose normalized pathname ends with `/Form/Render/{validated-id}`. It never matches display text, order, or generic card classes alone. A `MutationObserver` reapplies the rule after catalog filtering, category navigation, sorting, and pagination.

This is presentation control. A user who disables JavaScript can still see the card, and direct access remains allowed by design in 1.0.7. GLPI remains responsible for all form permissions.

## Access warning

PHP accepts a positive integer from the `category` query parameter only on `/ServiceCatalog` and compares it with the configured category ID. The modal uses GLPI's bundled Bootstrap implementation with a static backdrop, Escape disabled, no close button, initial focus on the acceptance checkbox, and a disabled Continue button. Acceptance exists only in the current DOM and is not audited.

## Configuration scope

Configuration is global in 1.0.7. Assets are served from the plugin's GLPI 11 `public/` directory and loaded on every `/ServiceCatalog` request. The warning is triggered by the configured `category` query parameter or by clicking its category card. The on-call form can only be selected when it is active, non-deleted, visible in the administrator's active entity scope, and answerable under native access policies.

## Security boundaries

- GLPI `config` UPDATE right is checked on both GET and POST.
- POST relies on GLPI 11's central CSRF validation for legacy plugin endpoints and declares the plugin CSRF-compliant.
- IDs, time values, days, timezone identifiers, message lengths, and colors are allow-list validated.
- Configurable text is stored and emitted as plain text. The frontend creates nodes with `textContent`.
- No SQL is concatenated. Uninstall deletes only the plugin configuration context.
- No core files, forms, tickets, acceptance records, IP addresses, or external services are touched.
