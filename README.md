# Formularios de guardia para GLPI 11

On-call Forms adapts two GLPI 11 native forms to a business/on-call schedule without modifying GLPI core. It hides the on-call card during normal hours, visually distinguishes it during on-call hours, and shows an accessible acknowledgement modal before a user continues to the normal incident form outside business hours.

## Screenshots

- `docs/images/configuration.png` — configuration page (placeholder; capture from a real GLPI instance before publication).
- `docs/images/on-call-card.png` — styled catalog card (placeholder).
- `docs/images/warning-modal.png` — warning modal (placeholder).

No fabricated screenshots are included.

## Features

- Native GLPI 11 forms and access policies; no FormCreator dependency.
- Server-side schedule decision with an injectable date/time for tests.
- Configurable forms, weekdays, holidays, one daily interval, IANA timezone, message labels, badge, and safe colors.
- Administration menu and dedicated profile rights for authorized technicians.
- Multi-select holiday calendar and UTF-8 CSV import using `fecha,nombre`.
- Bootstrap/Tabler-compatible accessible modal with explicit acknowledgement.
- Global configuration protected by GLPI permissions and CSRF.
- Textos originales en español; los catálogos de localización se añadirán más adelante.

## Requirements

- GLPI `>= 11.0.0` and `< 12.0.0`
- PHP `>= 8.2` (the minimum declared by GLPI 11)
- Two active native GLPI forms accessible in the intended entity scope

## Installation

1. Download a release archive and extract its root `oncallforms` directory into `GLPI_ROOT/plugins/`.
2. In GLPI, open **Setup > Plugins**.
3. Install and activate **Formularios de guardia**.
4. Open its configuration page and select both forms.

No Composer command or external service is required in production.

## Updating

Deactivate the plugin, replace the `oncallforms` directory with the new release, then reactivate it. Existing keys are preserved by the idempotent installer.

## Schedule behavior

The default interval is Monday-Friday, `08:00-15:00` in the configured/effective server timezone.

| Local time | Mode | On-call card |
|---|---|---|
| Monday 07:59 | On call | Visible |
| Monday 08:00 | Business | Hidden |
| Wednesday 14:59 | Business | Hidden |
| Wednesday 15:00 | On call | Visible |
| Saturday/Sunday | On call | Visible all day |

`08:00` is inclusive business time. `15:00` is inclusive on-call time. Browser time is never used.

## Configuration

See [docs/configuration.md](docs/configuration.md). Version 1.1.0 stores one global configuration. The warning targets a configurable service-catalog category, while the form selector shows only active, non-deleted native forms allowed by GLPI's current entity and access policies. Holidays activate on-call mode for the whole local calendar day.

## Security

The plugin uses integer/allow-list validation, explicit CSRF and permission checks, plain-text messages, safe CSS colors, GLPI database abstractions, and no external calls. Report vulnerabilities according to [SECURITY.md](SECURITY.md).

The card hiding rule is visual because GLPI 11 exposes no public server hook for removing a built-in catalog item. Disabling JavaScript reveals the card but does not bypass GLPI form permissions. Direct access to the on-call form during business hours is intentionally allowed.

## Known limitations

- Global configuration only; no per-entity inheritance.
- One interval per day; no split shifts, teams, notifications, ticket automation, or acceptance audit.
- DOM adaptation depends on the public `/Form/Render/{id}` link shape and is isolated in `public/js/oncallforms.js`.
- Browser and install verification requires a real GLPI 11 test environment.

## Development and tests

```bash
composer install
composer check
node --check public/js/oncallforms.js
```

See [docs/architecture.md](docs/architecture.md) and [docs/testing.md](docs/testing.md). Generate gettext files with `xgettext`, update `.po` files with `msgmerge`, and compile `.mo` files with `msgfmt`; generated `.mo` files are release artifacts, not hand-written files.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) and the [Code of Conduct](CODE_OF_CONDUCT.md).

## License

GPL-3.0-or-later. See [LICENSE](LICENSE).
