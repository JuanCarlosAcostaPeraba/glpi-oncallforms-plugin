# Configuración

Abra **Administración > Formularios de guardia**. El super-admin también puede usar **Configuración > Plugins > Formularios de guardia > Configurar**.

Para dar acceso a un perfil técnico, abra su ficha de perfil, entre en la pestaña **Formularios de guardia** y active los permisos de lectura y modificación. Estos permisos no conceden acceso a la configuración general de GLPI.

1. Seleccione el formulario de guardia e introduzca el ID de la categoría del catálogo donde debe aparecer el aviso.
2. Elija un intervalo normal y uno o más días laborables. El valor predeterminado es de lunes a viernes, desde las 08:00 incluidas hasta las 15:00 excluidas.
3. Mantenga la zona horaria efectiva de GLPI/PHP o seleccione una zona horaria IANA explícita.
4. Marque en el calendario los días festivos. Un festivo activa el modo de guardia durante las 24 horas.
5. Edite los textos planos del aviso y los tres colores seguros con formato `#RRGGBB`.

## Importación de festivos

El CSV debe estar codificado en UTF-8, separado por comas y usar exactamente esta cabecera:

```csv
fecha,nombre
2027-01-01,Año Nuevo
2027-05-30,Día de Canarias
2027-12-25,
```

La fecha es obligatoria y usa `AAAA-MM-DD`; el nombre es opcional. La importación valida el archivo completo antes de guardar, elimina fechas duplicadas y combina el resultado con los festivos existentes. Si una fecha ya existe, el valor importado actualiza su nombre.

El formulario de guardia debe permanecer activo, sin eliminar, disponible en el ámbito de la entidad actual y accesible para el usuario actual. Indique también el ID de la categoría del catálogo donde debe aparecer el aviso; para `/ServiceCatalog?category=27`, use `27`. La configuración es global en la versión 1.1.0.
