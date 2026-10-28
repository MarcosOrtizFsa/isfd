# ISFD

Portal PHP del ISFD con módulos funcionales independientes:

- `home`: portada publica y contenidos provenientes de NEURIA.
- `login`: autenticacion del equipo administrador.
- `control`: administracion de usuarios y perfiles.
- `ofertas`: administración del catálogo académico, publicación y convocatorias.
- `noticias`: producción editorial y revisión de contenidos ofrecidos por NEURIA.
- `root`: configuracion interna de modulos, exclusiva del desarrollador root.

## Requisitos

- Apache con `mod_rewrite`.
- PHP 8.2 o superior con `mysqli`, `curl` y `mbstring`.
- MySQL/MariaDB con la base existente `isfd_formosa`.

## Configuracion

La conexion utiliza estas variables de entorno. Los valores locales conservan
compatibilidad con XAMPP, pero no deben utilizarse en produccion.

```text
ISFD_DB_HOST=localhost
ISFD_DB_USER=usuario_isfd
ISFD_DB_PASSWORD=clave-segura
ISFD_DB_NAME=isfd_formosa
```

La configuracion privada de NEURIA se documenta en
`CONFIGURACION_NEURIA.md`. Su cliente, sanitizador y endpoint interno son una
unidad independiente del administrador.

## Flujo de la aplicacion

`index.php` carga el shell de `sistema/app_index.php`. El shell inyecta un token
CSRF y carga cada modulo dentro de `#content_seccion` mediante `cargar_post()`.
Las mutaciones usan `appAction()` y siempre validan sesion, rol y CSRF en PHP.

El codigo comun esta en `lib/app.php`:

- conexion MySQL con consultas preparadas;
- sesion y cookies seguras;
- autenticacion y autorizacion por modo;
- proteccion CSRF;
- respuestas JSON y registro de auditoria.

## Claves

Las cuentas nuevas usan `password_hash()`. Una cuenta historica con SHA-1 se
migra automaticamente al primer ingreso correcto; no es necesario conocer ni
reiniciar su clave.

## Verificacion

```text
php -l lib/app.php
php -l sistema/app_index.php
node --check scripts/app_ajax.js
node --check scripts/js_codex.js
```

La portada se abre en `/` y el acceso administrativo en `/mipanel`.

## Catálogo académico

El árbol de categorías, las reglas de carga, los filtros y las migraciones del
catálogo están documentados en [docs/CATALOGO.md](docs/CATALOGO.md). El modelo
entidad-relación se encuentra en [docs/catalogo-er.md](docs/catalogo-er.md).

## Noticias y Neuria

El flujo editorial, los estados, la importación controlada y las reglas de
archivos están documentados en [docs/NOTICIAS.md](docs/NOTICIAS.md).

La sincronización incremental con la API v1 de Neuria se documenta en
[docs/NEURIA_V1.md](docs/NEURIA_V1.md).

## Importación en producción

El orden de las migraciones y el último paso manual para hosting compartido se
detallan en [database/IMPORTACION_PRODUCCION.md](database/IMPORTACION_PRODUCCION.md).

