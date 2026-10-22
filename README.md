# Gestión institucional de activos fijos

Laravel 12.70.1 / PHP 8.2+ / Filament 5.10.3 / Livewire 4.4.8.

Portada pública en XAMPP: **http://localhost/inventario/** (también disponible en /inventario/public/). El botón superior «Iniciar sesión» abre el acceso de Filament. El panel permanece en /panel y, al cerrar sesión, se vuelve a la portada. Con `php artisan serve`, la portada está en http://127.0.0.1:8000/. La interfaz administrativa anterior conserva su ruta protegida /dashboard.

## Acceso inicial

Crear el administrador mediante `UserSeeder` (las migraciones y `FoundationSeeder` deben estar aplicados):

```powershell
C:\xampp\php\php.exe artisan db:seed --class=UserSeeder
```

En el entorno local, si no se configuró `INITIAL_ADMIN_EMAIL`, usa `admin@inventario.local`. Si falta `INITIAL_ADMIN_PASSWORD`, genera una contraseña aleatoria que muestra una sola vez al crear la cuenta. La contraseña se guarda como hash, nunca en el código ni en la auditoría. Conservarla al ejecutar el seeder.

`INITIAL_ADMIN_NAME`, `INITIAL_ADMIN_EMAIL` e `INITIAL_ADMIN_PASSWORD` permiten definir las credenciales propias desde .env. En producción no se crea una cuenta implícita: configurar correo y contraseña antes de ejecutar el seeder. Repetir el comando no cambia contraseñas, roles ni estado de cuentas existentes.

`DatabaseSeeder` ejecuta `FoundationSeeder` y después `UserSeeder`. `InitialAdminSeeder` se conserva como entrada compatible. El comando `app:create-admin` también permanece disponible para crear otras cuentas de forma interactiva.

## Instalación y operación

```powershell
composer install
C:\xampp\php\php.exe artisan test --compact
C:\xampp\php\php.exe vendor/laravel/pint/builds/pint --test --dirty
```

PHP necesita intl y zip. Ambas extensiones están habilitadas en C:/xampp/php/php.ini; se conservó una copia previa. Si Apache estaba iniciado al cambiar PHP, reiniciarlo desde XAMPP para que cargue las extensiones. No se cambió el .env existente ni su APP_KEY.

El panel utiliza assets nativos de Filament y no necesita npm para esta fase. Composer publica sus assets mediante `filament:upgrade` después del autoload. Servir con la raíz web en `public`. El .htaccess de la raíz permite el acceso /inventario/public y bloquea acceso directo al código, .env y .git al usar htdocs en XAMPP.

No ejecutar migrate:fresh ni db:wipe sobre la base institucional. Las pruebas utilizan SQLite en memoria. La verificación de navegador usa su propia base SQLite temporal y nunca ejecuta migraciones sobre MySQL.

## Alcance de esta entrega

Dashboard, sidebar, Activos (listado, búsqueda, filtros, alta, consulta y edición), Policies y auditoría de consulta. Los otros módulos existentes conservan sus pantallas anteriores y permisos. Las rutas de acceso y salida anteriores generan URLs válidas bajo la subcarpeta de XAMPP.

- [Auditoría y arquitectura prevista](docs/AUDITORIA_FILAMENT.md)
- [Resultados, archivos y límites de la primera fase](docs/RESULTADOS_FILAMENT.md)
- [Arquitectura funcional de la base existente](docs/ARQUITECTURA.md)

No se implementaron movimientos, reportes AF, parque automotor ni asignaciones históricas. El alcance de acceso continúa siendo global por permiso: el modelo actual de usuarios no tiene una asociación institucional con la cual imponer aislamiento por unidad.
