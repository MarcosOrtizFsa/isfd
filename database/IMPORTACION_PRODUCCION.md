# Importación en producción

La instalación se divide para que una restricción del hosting sobre rutinas no
interrumpa la creación de tablas.

## Orden de carga

Importar desde phpMyAdmin, uno por uno y en este orden:

1. `2026_10_17_portal_academico.sql`
2. `migrations/002_catalogo_ofertas_up.sql`
3. `migrations/003_ofertas_superiores_demo_up.sql`
4. `migrations/004_modulo_ofertas_admin_up.sql`
5. `migrations/005_noticias_admin_up.sql`
6. `migrations/006_vista_catalogo_phpmyadmin_up.sql`

Estos seis archivos crean la estructura necesaria para que el portal funcione.
La migración 002 detecta y elimina la clave foránea anterior aunque cPanel le
haya asignado un nombre diferente. También puede repetirse después de una carga
parcial sin detenerse porque las tablas ya existan.

## Último paso manual

Importar al final:

`migrations/007_catalogo_integridad_manual_up.sql`

Este archivo contiene únicamente los triggers y procedimientos de integridad
del catálogo. No declara `DEFINER`; los objetos quedan asociados al usuario que
ejecuta la importación.

Si el hosting rechaza `CREATE ROUTINE` o `TRIGGER`, no volver a cargar todo: las
tablas y los módulos ya están instalados. Se debe solicitar ese permiso al
proveedor y repetir solamente el archivo 007.

No se debe importar un volcado local que contenga
`DEFINER=\`root\`@\`localhost\``.
