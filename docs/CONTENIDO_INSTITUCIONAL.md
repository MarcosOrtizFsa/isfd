# Contenido institucional versionado

Administración: Módulos internos → Configuración de dominio → Contenido institucional e historial. Solo root puede leer el administrador y guardar cambios; el guardado exige CSRF.

## Instalación

Con copia de respaldo de la BD, ejecutar desde la raíz del proyecto:

```sh
php database/migrar_institucional.php
```

El comando crea las tablas de la migración 014 y carga la primera revisión de cada sección desde los textos anteriores y la configuración existente. Se puede repetir: no reemplaza secciones que ya tienen versión vigente. Ejecutarlo antes de desplegar los controladores que leen estas tablas. No modifica los contadores de system_08_configuracion.

## Estructura y edición

- `institucional_secciones`: identifica cada sección y su versión publicada.
- `institucional_versiones`: sección, año, fecha, usuario responsable y motivo.
- `institucional_valores`: campos y valores de cada revisión, relacionados mediante clave foránea.

Cada guardado crea una instantánea completa de la sección y la publica dentro de una transacción. Las revisiones anteriores no se sobrescriben. Se bloquea la sección y se compara la versión enviada para evitar que una edición concurrente borre cambios.

El año es un período informativo, no una programación automática: guardar publica inmediatamente. Para recuperar contenido anterior, consultar el historial y copiarlo en una nueva revisión indicando el motivo. La historia admite un párrafo por línea. Los campos son texto plano y se escapan al mostrarlos.

Actualización del 4 de noviembre de 2026: las autoridades de la portada se administran mediante fichas propias, no mediante el antiguo texto `Cargo | Nombre`, que permanece por compatibilidad. Ver [AUTORIDADES.md](AUTORIDADES.md). Los videos institucionales tienen su lista versionada y controles específicos: [VIDEOS_INSTITUCIONALES.md](VIDEOS_INSTITUCIONALES.md).

En Enlaces e imágenes institucionales, `preinscripcion_url` vacía activa el formulario propio `/preinscripcion/`; una URL configurada conserva el destino externo. Desde una oferta abierta se preselecciona la carrera en el formulario interno. Los contadores 1–4 no recibieron un nuevo uso público.

La configuración técnica conserva dominio, metadatos y redes en system_08_configuracion. Los antiguos contactos permanecen en esa tabla como respaldo, pero la portada usa los contactos versionados. El archivo datos_institucionales.php adapta los datos de BD a las vistas existentes; portal_institucional.php mantiene la presentación segura de enlaces y redes.

Verificación sin modificar contenido: `php tests/institucional_test.php`.
