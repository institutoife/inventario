# Auditoría previa a Filament — 22/10/2026

## Estado comprobado antes de modificar código

- PHP CLI 8.2.12, Laravel 12.70.1, Composer 2.9.8. Filament y Livewire ausentes.
- composer.json requiere PHP ^8.2 y Laravel ^12.0; PHPUnit 11 y Pint instalados. Vite 7, Tailwind ^4.0 y Axios declarados; las vistas actuales no usan compilación frontend. Sin componentes Livewire, API, Actions ni Policies.
- La simulación de Composer resuelve Filament 5.10.3 y Livewire 4.4.8: 36 instalaciones, ninguna actualización ni eliminación. intl y zip existen como DLL pero están deshabilitadas. No se necesita actualizar PHP/Laravel ni instalar plugins de permisos.
- MySQL responde y contiene 25 tablas. Conteos: assets 0, users 0, audit_logs 0, roles 6, permissions 18, permission_role 45, physical_statuses 4 y administrative_statuses 7. Se calcularon SHA-256 por tabla para comparar al finalizar. Sesiones/caché son datos operativos variables.
- Tablas: users, password_reset_tokens, sessions, cache, cache_locks, jobs, job_batches, failed_jobs, migrations, roles, permissions, permission_role, role_user, organizational_units, offices, locations, budget_items, asset_categories, physical_statuses, administrative_statuses, positions, ranks, people, assets, audit_logs.
- Modelos: User, Role, Permission, OrganizationalUnit, Office, Location, BudgetItem, AssetCategory, PhysicalStatus, AdministrativeStatus, Position, Rank, Person, Asset, AuditLog.

## Funcionalidad real y navegación

Estructura institucional, responsables, catálogos, usuarios/roles, inventario base y auditoría. No existen todavía movimientos, asignaciones históricas, AF, vehículos, documentos, importaciones ni adjuntos. No se crearán menús ni relaciones ficticias para ellos.

La pantalla no es literalmente una única vista: layout.blade.php concentra una barra horizontal con todos los módulos y admin/index + admin/form renderizan cualquier entidad. FoundationController y FoundationRequest concentran las diferencias de todos los maestros. welcome.blade.php es la plantilla original sin ruta funcional.

Rutas actuales: GET /, GET/POST /login, POST /logout, GET /audit, GET /roles, PUT /roles/{role}, GET/POST /admin/{resource}, GET /admin/{resource}/create, GET /admin/{resource}/{id}/edit, PUT /admin/{resource}/{id}. Se conservarán. /panel evita colisión con /admin/{resource}.

## Código reutilizable

Todos los modelos y relaciones, tablas y datos, FoundationSeeder y permisos existentes, Audit::record, autenticación web y ActiveUser, validaciones de unicidad/relaciones, control de versión y transacciones. Activos no ofrece hoy cambios de responsable o situación: deben seguir fuera de la edición base.

## Hallazgos y riesgos

1. Sin Policies; los Gates existentes protegen rutas pero Filament requiere autorización explícita por recurso y operación.
2. Alta/edición de activos, bloqueo, versión, auditoría y reglas están mezclados en FoundationController. Se extraerán a una acción de dominio compartida por ambas interfaces.
3. Validación mezclada con validación genérica de maestros; se centralizarán las reglas de activos para evitar dos implementaciones.
4. La vista de listado ejecuta consultas por celda y presenta demasiadas columnas. Filament usará eager loading, filtros combinables y columnas secundarias ocultables.
5. Alcance vigente global por permiso: users no tiene unidad/oficina asociada. No se inventará aislamiento institucional. Se centralizará la consulta de visibilidad y se documentará el aislamiento pendiente.
6. No hay que instalar Spatie ni Shield: ya existe RBAC persistido. Mantener denegación predeterminada, usuarios activos y permisos existentes; no introducir un bypass global que habilite eliminaciones.
7. La edición descriptiva no debe permitir asignar responsables, modificar situación, ni encubrir movimientos institucionales.
8. Auditoría de modelo inmutable, pero SQL privilegiado puede alterarla. SQLite no prueba bloqueo concurrente InnoDB.
9. APP_URL local contiene /inventario/public. Ocho pruebas existentes fallan con 404 porque PHPUnit hereda esa subruta. Dar a las pruebas APP_URL=http://localhost sin modificar .env.
10. El primer control Pint --dirty pasa, pero no evalúa los archivos ya versionados. Se ejecutará Pint sobre todo el código al cerrar.
11. La app conserva enlaces absolutos antiguos; se verificará navegación desde la raíz public y se favorecerán rutas generadas.

## Plan de la primera entrega

1. Habilitar solamente intl/zip, conservando copia de php.ini. Instalar Filament 5 compatible sin actualizaciones mayores.
2. Panel nativo en /panel con sidebar, autenticación compartida, acceso explícito, autorización estricta y eventos de acceso auditados.
3. Dashboard con widgets autorizados sobre datos reales y consulta central de visibilidad.
4. Separar AssetResource, Pages, Schemas, Tables y un Relation Manager de auditoría de solo lectura.
5. Acción SaveAsset compartida por el controlador antiguo y Filament: autorización, validación, transacción, bloqueo, versión y auditoría atómica.
6. AssetPolicy y AuditLogPolicy explícitas, sin eliminación, asignación ni movimientos en esta fase.
7. Pruebas PHPUnit/Livewire de listado, búsqueda, filtros, alta, detalle, edición, permisos, URL directa, concurrencia lógica, manipulación de campos y rollback de auditoría. SQLite exclusivamente en memoria.
8. Ejecutar pruebas/formato, revisar dependencias y comparar hashes de MySQL. No ejecutar migraciones ni seeders contra MySQL.

## Árbol previsto

```text
app/
  Actions/Assets/SaveAsset.php
  Domain/Assets/AssetData.php
  Queries/AssetVisibility.php
  Policies/{AssetPolicy,AuditLogPolicy}.php
  Filament/
    Pages/{Dashboard,AuditTrail}.php
    Widgets/AssetStats.php
    Resources/Assets/
      AssetResource.php
      Pages/{ListAssets,CreateAsset,ViewAsset,EditAsset}.php
      Schemas/{AssetForm,AssetInfolist}.php
      Tables/AssetsTable.php
      RelationManagers/AuditLogsRelationManager.php
  Listeners/AuditAuthentication.php
  Providers/Filament/AdminPanelProvider.php
tests/Feature/Filament/{Assets,Panel}Test.php
docs/{AUDITORIA_FILAMENT,RESULTADOS_FILAMENT}.md
```

## Sidebar previsto en esta fase

- Inicio (Dashboard).
- Inventario: Activos (Filament), Responsables (ruta vigente).
- Organización: Estructura institucional, Oficinas, Ubicaciones (rutas vigentes).
- Administración: Usuarios, Roles y permisos, Catálogos agrupados (rutas vigentes), Auditoría de solo lectura.

Cada entrada se filtra por permiso y la ruta destino conserva autorización en servidor. No se implementan Resources adicionales en esta fase. Movimientos, Incorporaciones, Parque automotor y Reportes se añadirán al migrar sus procesos reales.

## Fuentes oficiales consultadas

- https://filamentphp.com/docs/5.x/introduction/installation
- https://filamentphp.com/docs/5.x/resources/overview
- https://filamentphp.com/docs/5.x/users/overview
- https://filamentphp.com/docs/5.x/advanced/security
- https://filamentphp.com/docs/5.x/testing/testing-resources

La documentación 5.x exige PHP 8.2+, Laravel 11.28+ y Tailwind 4.1+. El panel usa sus assets nativos precompilados; no necesita introducir un tema ni compilar los assets anteriores. Consultar también el código de la versión instalada al implementar APIs.

## Hallazgo adicional durante la verificación de cierre

Las solicitudes HEAD a /inventario/.env, /inventario/.git/HEAD y /inventario/composer.json respondían HTTP 200 en Apache. Se añadió un .htaccess en la raíz que expone exclusivamente public y bloquea las rutas privadas. Después, las cuatro rutas privadas comprobadas responden 403; /inventario/public/panel/login y los CSS de Filament responden 200. No se descargó ni mostró el contenido de las credenciales.

También se corrigieron los enlaces absolutos de acceso, salida y navegación anteriores para usar rutas Laravel y funcionar bajo /inventario/public.
