# Acceso y administración

- `/`: portada pública, accesible sin sesión. Muestra el enlace al login del panel.
- `/panel/login`: único acceso de autenticación.
- `/panel`: panel de Filament. Inventario, responsables, unidades, oficinas, ubicaciones, catálogos, usuarios, roles/permisos y auditoría se gestionan aquí.
- Las rutas antiguas `/admin/*`, `/roles`, `/audit`, `/dashboard`, `/login` y `/logout` fueron retiradas. Ya no existe una administración Blade paralela.

Los maestros usan formularios y tablas nativos de Filament. Las escrituras pasan por SaveFoundation con autorización en servidor, validación, control de edición concurrente, transacción y auditoría. Los activos conservan su acción SaveAsset. Roles permite modificar permisos con motivo y control de concurrencia; superadministrador mantiene sus permisos de recuperación. No hay acciones de borrado.

## Usuario inicial

Editar directamente las variables `$name`, `$email` y `$password` en `database/seeders/UserSeeder.php`. La contraseña debe tener al menos 12 caracteres. No se usan variables INITIAL_ADMIN de `.env` ni claves aleatorias.

Ejecutar `php artisan db:seed`: primero prepara roles/catálogos y después llama a UserSeeder. Si los roles ya existen también puede ejecutarse `php artisan db:seed --class=UserSeeder`.

El usuario nuevo recibe el rol superadmin y la contraseña se almacena cifrada con hash. Si el correo ya existe, se conservan su contraseña, roles y estado. Cambiar la clave en el seeder no cambia una cuenta ya creada; editar esa cuenta desde Usuarios en Filament. No se ejecutó este seeder sobre la base de trabajo como parte de esta migración.

Este documento actualiza las descripciones de navegación y autenticación de ARQUITECTURA.md, AUDITORIA_FILAMENT.md y RESULTADOS_FILAMENT.md, que describen etapas anteriores.
