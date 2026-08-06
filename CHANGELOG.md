# Changelog

All notable changes follow [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and semantic versioning.

## [Unreleased]

## [1.0.5] - 2026-08-06

### Cambiado

- El aviso fuera de horario se muestra al abrir la categoría configurada del catálogo de servicios.
- La configuración usa el ID de categoría del catálogo en lugar de un formulario normal concreto.

## [1.0.4] - 2026-08-06

### Corregido

- La comprobación de configuración ya no carga clases del plugin antes de que GLPI registre su autoloader, evitando el error 500 al activarlo.

## [1.0.3] - 2026-08-06

### Corregido

- Se evita validar dos veces el token CSRF que GLPI 11 ya comprueba antes de cargar scripts heredados.

## [1.0.2] - 2026-08-06

### Corregido

- La URL de configuración usa el generador oficial de GLPI 11 y no depende de una variable global en el ámbito del script.

## [1.0.1] - 2026-08-06

### Corregido

- El formulario de configuración envía y redirige a su URL explícita de plugin en GLPI 11.
- La configuración inicial permite completar el estado previo a la activación.

### Cambiado

- Todos los textos visibles, mensajes de validación y valores predeterminados están en español.
- Los valores predeterminados ingleses de instalaciones previas se muestran en español sin sobrescribir personalizaciones.

## [1.0.0] - 2026-08-06

### Added

- GLPI 11 native-form selectors with entity/access validation.
- Server-side configurable schedule and required boundary tests.
- Catalog visibility/style adapter and accessible normal-form warning.
- Global configuration, gettext sources, documentation, CI, and release packaging.
