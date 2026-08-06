# Testing

## Automated checks

```bash
composer install
composer validate --strict
composer check
node --check js/oncallforms.js
```

Unit tests cover every required time boundary, timezone conversion, configurable days, date changes, invalid intervals, invalid IDs, empty days, invalid timezone identifiers, text sanitization, and CSS color injection.

## GLPI 11 integration checklist

Run against a disposable GLPI 11 instance with two active native forms in the same test entity.

- Verify users without `config` UPDATE receive access denied on both GET and POST.
- Submit without or with an invalid CSRF token and verify access is denied.
- Try a nonexistent, deleted, inactive, inaccessible, and cross-entity form ID by tampering with POST data.
- Verify Monday 08:00 hides only the on-call card; Monday 15:00 and weekends show and style only that card.
- Filter, sort, paginate, and enter categories; verify AJAX refreshes preserve the rule.
- Open `/ServiceCatalog?category={configured-id}` during on-call time. Check static backdrop, Escape, focus, Tab/Shift+Tab, Space on the checkbox, disabled Continue, enabled Continue after acceptance, and the on-call link.
- Open the same form during business time and other GLPI pages; verify no modal appears.
- Disable JavaScript and confirm native GLPI permissions still apply. The documented catalog presentation rule will not apply.
- Deactivate the plugin and verify native catalog and renderer behavior return immediately.
- Uninstall and verify only `glpi_configs.context = plugin:oncallforms` was removed.

The repository cannot truthfully certify installation or browser behavior without a running GLPI instance and database. These checks are therefore explicit release gates.
