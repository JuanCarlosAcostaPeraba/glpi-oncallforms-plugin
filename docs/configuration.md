# Configuración

Abra **Configuración > Plugins > Formularios de guardia > Configurar** con un perfil que tenga permiso para modificar la configuración de GLPI.

1. Seleccione el formulario de guardia e introduzca el ID de la categoría del catálogo donde debe aparecer el aviso.
2. Elija un intervalo normal y uno o más días laborables. El valor predeterminado es de lunes a viernes, desde las 08:00 incluidas hasta las 15:00 excluidas.
3. Mantenga la zona horaria efectiva de GLPI/PHP o seleccione una zona horaria IANA explícita.
4. Edite los textos planos del aviso y los tres colores seguros con formato `#RRGGBB`.

El formulario de guardia debe permanecer activo, sin eliminar, disponible en el ámbito de la entidad actual y accesible para el usuario actual. Indique también el ID de la categoría del catálogo donde debe aparecer el aviso; para `/ServiceCatalog?category=27`, use `27`. La configuración es global en la versión 1.0.5.
