# Catálogos y datos de prueba

## Carga inicial completa

`php artisan db:seed --class=InitialCatalogSeeder` carga 9 categorías, 9 partidas de referencia, 31 grados (carrera y servicios), 6 cargos, 7 procedencias y 8 formas de incorporación. No modifica usuarios ni crea estructura ficticia. También está incluido en DatabaseSeeder. Las categorías y partidas se pueden cargar individualmente con AssetCategorySeeder / BudgetItemSeeder; grados con RankSeeder y cargos con PositionSeeder.

Los códigos `CAT-`, `REF-` y `CARGO-` son identificadores internos. Las partidas REF- permiten probar el inventario y deben adecuarse al clasificador presupuestario institucional; no representan numeración oficial. Los grados se toman de la escala del artículo 12 de la [Ley 1387 publicada por el Ministerio de Gobierno](https://web.mingobierno.gob.bo/wp-content/uploads/2025/09/LEY-1387-DE-CARRERA-DE-GENERALES-Y-DE-ASCENSOS-DE-LA-POLICIA-BOLIVIANA.pdf), como etiquetas configurables; no se programan reglas legales ni requisitos de ascenso. Para personas civiles puede dejarse el grado vacío.

`AssetOriginSeeder` crea las siete procedencias solicitadas. `IncorporationMethodSeeder` crea las ocho formas de incorporación. `AssetCatalogSeeder` agrupa ambos en una transacción y DatabaseSeeder lo incluye.

`DemoOrganizationSeeder` es independiente: crea 4 unidades (una raíz y tres dependientes), 5 oficinas, 6 ubicaciones (una es depósito) y 3 cargos. Los códigos comienzan con DEMO- y los nombres indican que son pruebas. No crea activos, usuarios adicionales ni asignaciones ficticias. No se ejecuta automáticamente en DatabaseSeeder.

Preparar las tablas y cargar exclusivamente estos datos:

```powershell
php artisan migrate
php artisan db:seed --class=AssetCatalogSeeder
php artisan db:seed --class=DemoOrganizationSeeder
```

Los seeders usan firstOrCreate por código: se pueden repetir sin duplicar ni sobrescribir nombres, relaciones o estados editados. Si se cambia un código, pasa a considerarse otro registro. No se han inventado códigos oficiales de partidas ni grados institucionales.

Los catálogos se administran en Filament → Catálogos → Procedencias / Formas de incorporación. El formulario del activo presenta selectores. Los campos existentes `origin` e `incorporation_method` conservan texto como valor histórico: esta entrega no transforma ni borra información previa, ni agrega claves foráneas a esos campos. Valores nuevos deben existir y estar activos en los catálogos; un valor histórico sin cambios sigue permitido. Una renombración del catálogo no reescribe activos anteriores.
