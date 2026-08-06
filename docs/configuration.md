# Configuration

Open **Setup > Plugins > On-call Forms > Configure** with a profile that has the GLPI `config` UPDATE right.

1. Select the on-call form and normal incident form. Options show the native translated name and numeric ID.
2. Choose one normal interval and one or more business days. The default is Monday-Friday, 08:00 inclusive to 15:00 exclusive.
3. Keep the effective GLPI/PHP timezone or select an explicit IANA timezone.
4. Edit the plain-text warning labels and the three safe `#RRGGBB` colors.

The two forms must differ and must remain active, non-deleted, available in the current entity scope, and answerable by the current user. Configuration is global in 1.0.0; configure it from an entity where both forms are available.
