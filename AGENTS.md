# Convenciones del proyecto

- `/` es la portada pública y debe poder visitarse sin autenticación.
- Toda gestión autenticada pertenece al panel de Filament en `/panel`, incluido el login en `/panel/login`, usuarios, roles, catálogos, estructura, responsables, inventario y auditoría.
- Crear recursos, páginas, formularios, tablas y acciones nativos de Filament. No agregar un panel Blade paralelo ni rutas administrativas externas como `/admin/*`, `/roles`, `/audit` o `/dashboard`.
- Las operaciones requieren autorización en servidor, validación y auditoría. Las escrituras de varias tablas deben ser transaccionales; conservar el historial y el control de concurrencia.
- Las credenciales del usuario inicial se editan directamente en `database/seeders/UserSeeder.php` (`$name`, `$email`, `$password`). No trasladarlas a `.env` ni generar contraseñas aleatorias sin indicación del usuario.
- El seeder inicial no debe sobrescribir contraseñas, roles o estado de cuentas existentes. No ejecutar seeders sobre la base de trabajo salvo que el usuario lo solicite.
- Mantener el desarrollo por fases; no implementar fases posteriores por efecto de una migración de interfaz.
