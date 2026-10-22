# Primera fase Filament — resultados del 22/10/2026

## Resultado y arquitectura

Implementado el patrón de referencia del módulo Activos, sin avanzar a los demás procesos. Panel en /panel, con navegación izquierda Inventario / Organización / Administración, Dashboard nativo y autenticación web compartida. Filament 5.10.3 y Livewire 4.4.8 instalados sobre Laravel 12.70.1 y PHP 8.2.12. Las [exigencias oficiales de Filament 5](https://filamentphp.com/docs/5.x/introduction/installation) son compatibles con este entorno.

El árbol y la auditoría previa están en [AUDITORIA_FILAMENT.md](AUDITORIA_FILAMENT.md). Resource, Pages, Schemas y Tables están separados; SaveAsset y AssetData contienen el dominio reutilizado por Filament y la interfaz anterior. AssetVisibility centraliza la consulta y AssetPolicy controla la operación. No se instalaron Shield, Spatie Permission ni plugins adicionales.

## Funcionalidades migradas

- Listado paginado, ordenamiento, búsqueda por código/descripción/marca/modelo/serie y columnas secundarias ocultables.
- Filtros combinables por unidad, oficina, responsable, partida, categoría, estado físico, situación y rango de fechas.
- Alta, detalle e interfaz de edición independientes; formularios por secciones con selects de búsqueda acotados a 50 resultados.
- Responsable y situación de consulta: la ficha no asigna ni formaliza movimientos. Un activo asignado o no disponible no puede trasladarse mediante la edición base.
- Guardado autorizado y validado en servidor, transacción, bloqueo, versión, token de concurrencia y auditoría atómica. Si falla la auditoría se revierte el activo.
- Acceso explícito al panel, usuarios activos, autorización estricta, URL directa protegida y revocación de permisos aplicada en Livewire.
- Auditoría global y del activo de solo lectura. Ingreso/salida auditados una sola vez mediante eventos Laravel, con actor explícito y exclusión de contraseñas/tokens.
- Bloqueo de acceso HTTP directo a .env, .git y archivos de aplicación en XAMPP. Enlaces de la interfaz anterior corregidos para la subcarpeta actual.

## Validación ejecutada

- `php artisan test --compact`: **57 pruebas aprobadas, 306 aserciones**. Incluye las 13 originales y 44 casos de Filament/dominio/acceso. SQLite en memoria.
- `php vendor/laravel/pint/builds/pint --test --dirty`: aprobado sobre todos los archivos nuevos/modificados.
- `composer check-platform-reqs`: todas las exigencias aprobadas, incluidas intl y zip.
- `composer audit`: sin avisos de vulnerabilidades en las dependencias bloqueadas.
- Comparación de composer.lock: **0 dependencias anteriores actualizadas o eliminadas**; 36 paquetes añadidos.
- Chrome real: login, Dashboard, listado, búsqueda Livewire, formulario, detalle, modal de auditoría y pantallas móvil/escritorio; sin errores JavaScript ni respuestas HTTP fallidas. Formulario móvil sin desbordamiento horizontal. Ejecutable reproducible: tools/verify-filament.cjs; requiere PHP XAMPP, Chrome y runtime Node disponible.
- Apache: /inventario/public/panel/login y CSS: HTTP 200; .env, .git/HEAD, composer.json y app/Models/User.php fuera de public: HTTP 403.
- Comparación SHA-256 de las 22 tablas no operativas de MySQL: sin cambios. Catálogos, permisos, roles, inventario, usuarios, auditoría e historial de migraciones conservados. Sesiones y caché se consideran operativos y pueden variar al abrir la app.
- .env conserva SHA-256 FEDC476E70AA5BE07AC96150E43F89FA62A04D3DCAAFAA39D7F1357411D30584. No se modificó su configuración ni la clave.

Las capturas y results.json se conservan localmente en storage/app/private/filament-review (fuera del acceso web y de Git), con datos ficticios de SQLite. El script guarda su base y perfil de revisión en una carpeta temporal independiente y cierra los procesos que inicia.

## Migraciones y cambios de entorno

**Ninguna migración nueva ni ejecutada sobre MySQL.** No se borraron ni cambiaron tablas, columnas, relaciones o datos. No se ejecutaron seeders sobre MySQL. Las migraciones de las pruebas corrieron solamente en SQLite aislado.

Se habilitaron intl y zip en C:/xampp/php/php.ini mediante cambios puntuales, con respaldo C:/xampp/php/php.ini.before-filament-20261022.bak. Reiniciar Apache si estaba ejecutándose para que cargue las extensiones. No se reinició el servidor institucional ni se cambiaron sus VirtualHosts.

## Archivos antiguos todavía utilizados

- Modelos existentes y todas sus tablas/relaciones; solo se agregaron relaciones de consulta de auditoría y el contrato de acceso a Filament.
- FoundationController y FoundationRequest: maestros, responsables, estructura y usuarios. Activos permanece accesible por las rutas anteriores, con el mismo dominio de guardado.
- AdminResources y vistas admin/index, admin/form, layout, login, roles, audit y dashboard: interfaz anterior conservada.
- AuthController, ActiveUser, Gates existentes, seeders y comando app:create-admin: continúan operativos. Los eventos centralizan la auditoría de acceso para ambas interfaces.
- routes/web.php y routes/console.php: conservados; no se retiraron rutas previas. Las rutas nuevas son registradas por Filament.

## Pendientes y riesgos

- No se migraron Responsables, catálogos, estructura, usuarios ni roles a Resources: sus enlaces del sidebar abren la interfaz vigente.
- Asignaciones, transferencias, relevos, depósito, faltantes, reposiciones, revalúos, incorporaciones, vehículos y reportes AF siguen pendientes. No se crearon menús ni funcionalidades ficticias.
- No existen documentos, adjuntos ni historial de movimientos aún. El historial mostrado es auditoría de cambios de ficha, no un historial institucional de movimientos.
- Alcance vigente global por permiso. Antes de requerir aislamiento entre unidades hay que definir la relación usuario/unidad y extender AssetVisibility, políticas y búsquedas de relaciones coherentemente.
- SQLite prueba conflictos lógicos y rollback; no demuestra concurrencia real de bloqueos InnoDB. Verificar esa concurrencia al incorporar movimientos.
- La auditoría conserva su protección de modelo, pero un administrador SQL privilegiado puede alterarla. Se mantienen pendientes las garantías externas de retención/respaldos y privilegios de producción.
- En MySQL hay cero usuarios, tal como al comenzar. Crear la cuenta institucional mediante app:create-admin; no usar las credenciales ficticias de las pruebas.
- La exposición HTTP anterior de archivos privados fue corregida; valorar la rotación de credenciales si este XAMPP estuvo accesible desde una red no confiable. No se rotó APP_KEY para evitar invalidar datos o sesiones existentes.

## Siguiente fase recomendada

Migrar Responsables, catálogos y estructura institucional reutilizando este patrón, después de definir el alcance por unidad. Luego implementar asignaciones y movimientos como acciones explícitas transaccionales, con pruebas de concurrencia InnoDB y snapshots documentales. No se avanzó automáticamente a estas fases.

## Archivos creados

- `.gitignore`
- `.htaccess`
- `app/Actions/Assets/SaveAsset.php`
- `app/Domain/Assets/AssetData.php`
- `app/Filament/Navigation/LegacyNavigation.php`
- `app/Filament/Pages/AuditTrail.php`
- `app/Filament/Pages/Dashboard.php`
- `app/Filament/Resources/Assets/AssetResource.php`
- `app/Filament/Resources/Assets/Pages/Concerns/CallsAssetAction.php`
- `app/Filament/Resources/Assets/Pages/CreateAsset.php`
- `app/Filament/Resources/Assets/Pages/EditAsset.php`
- `app/Filament/Resources/Assets/Pages/ListAssets.php`
- `app/Filament/Resources/Assets/Pages/ViewAsset.php`
- `app/Filament/Resources/Assets/RelationManagers/AuditLogsRelationManager.php`
- `app/Filament/Resources/Assets/Schemas/AssetForm.php`
- `app/Filament/Resources/Assets/Schemas/AssetInfolist.php`
- `app/Filament/Resources/Assets/Schemas/AssetSelect.php`
- `app/Filament/Resources/Assets/Tables/AssetsTable.php`
- `app/Filament/Tables/AuditLogsTable.php`
- `app/Filament/Widgets/AssetStats.php`
- `app/Http/Middleware/EnsurePanelAccess.php`
- `app/Listeners/AuditAuthentication.php`
- `app/Policies/AssetPolicy.php`
- `app/Policies/AuditLogPolicy.php`
- `app/Providers/Filament/AdminPanelProvider.php`
- `app/Queries/AssetVisibility.php`
- `docs/AUDITORIA_FILAMENT.md`
- `docs/RESULTADOS_FILAMENT.md`
- `resources/views/filament/pages/audit-trail.blade.php`
- `tests/Feature/Filament/AccessBoundaryTest.php`
- `tests/Feature/Filament/AssetsTest.php`
- `tests/Feature/Filament/FilamentTestCase.php`
- `tests/Feature/Filament/PanelTest.php`
- `tools/verify-filament.cjs`

## Archivos modificados

- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/FoundationController.php`
- `app/Http/Requests/FoundationRequest.php`
- `app/Models/Asset.php`
- `app/Models/AuditLog.php`
- `app/Models/User.php`
- `app/Providers/AppServiceProvider.php`
- `app/Services/Audit.php`
- `bootstrap/providers.php`
- `composer.json`
- `composer.lock`
- `phpunit.xml`
- `README.md`
- `resources/views/layout.blade.php`
- `resources/views/login.blade.php`

## Dependencias añadidas

- `anourvalar/eloquent-serialize` 1.3.12
- `blade-ui-kit/blade-heroicons` 2.7.0
- `blade-ui-kit/blade-icons` 1.10.1
- `chillerlan/php-qrcode` 5.0.5
- `chillerlan/php-settings-container` 3.3.0
- `composer/class-map-generator` 1.8.0
- `composer/pcre` 3.4.0
- `danharrin/date-format-converter` v0.3.1
- `danharrin/livewire-rate-limiting` v2.3.0
- `filament/actions` v5.10.3
- `filament/filament` v5.10.3
- `filament/forms` v5.10.3
- `filament/infolists` v5.10.3
- `filament/notifications` v5.10.3
- `filament/query-builder` v5.10.3
- `filament/schemas` v5.10.3
- `filament/support` v5.10.3
- `filament/tables` v5.10.3
- `filament/widgets` v5.10.3
- `kirschbaum-development/eloquent-power-joins` 4.3.5
- `league/csv` 9.28.0
- `league/uri-components` 7.9.0
- `livewire/livewire` v4.4.8
- `masterminds/html5` 2.11.0
- `nette/php-generator` v4.2.3
- `openspout/openspout` v4.28.5
- `paragonie/constant_time_encoding` v3.1.3
- `pragmarx/google2fa` v9.1.0
- `pragmarx/google2fa-qrcode` v4.0.0
- `ryangjchandler/blade-capture-directive` v1.1.1
- `scrivo/highlight.php` v9.18.1.10
- `spatie/invade` 2.2.0
- `spatie/laravel-package-tools` 1.93.3
- `spatie/shiki-php` 2.4.0
- `symfony/html-sanitizer` v7.4.20
- `ueberdosis/tiptap-php` 2.2.1
