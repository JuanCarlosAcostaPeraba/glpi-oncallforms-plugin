# Configuración

Abra **Configuración > Plugins > Formularios de guardia > Configurar** con un perfil que tenga permiso para modificar la configuración de GLPI.

1. Seleccione el formulario de guardia y el formulario normal de incidencias. Las opciones muestran el nombre nativo y su identificador numérico.
2. Elija un intervalo normal y uno o más días laborables. El valor predeterminado es de lunes a viernes, desde las 08:00 incluidas hasta las 15:00 excluidas.
3. Mantenga la zona horaria efectiva de GLPI/PHP o seleccione una zona horaria IANA explícita.
4. Edite los textos planos del aviso y los tres colores seguros con formato `#RRGGBB`.

Los dos formularios deben ser diferentes y permanecer activos, sin eliminar, disponibles en el ámbito de la entidad actual y accesibles para el usuario actual. La configuración es global en la versión 1.0.3; configúrela desde una entidad donde estén disponibles ambos formularios.
