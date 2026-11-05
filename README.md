# ISFD — Portal institucional Félix Atilio Cabrera

Proyecto PHP/MySQL modular, con interfaz responsive Bootstrap y navegación parcial jQuery/AJAX. Repositorio: `MarcosOrtizFsa/isfd`. Documentación revisada al código `50de00d`, 4 de noviembre de 2026.

## Documentación

- [Manual técnico](docs/MANUAL_TECNICO.md): arquitectura, tecnologías, recursos, APIs, datos, operación y colaboración humano/agente IA.
- [Manual básico del administrador](docs/MANUAL_ADMINISTRADOR.md): instrucciones de uso del sistema.
- [Índice de documentación](docs/INDICE.md): guías por dominio.
- [Importación y despliegue](database/IMPORTACION_PRODUCCION.md): migraciones hasta 020 y actualización de producción.

## Áreas funcionales

| Área | Función |
|---|---|
| Home | Portada, catálogo, noticias locales activas, contenido institucional y contacto. |
| Login / Control | Acceso y administración de usuarios del equipo. |
| Ofertas | ABM de ofertas/categorías, multimedia, convocatorias y plan de estudios. |
| Noticias | Edición, adjuntos independientes, solicitudes IA y recepción/sincronización con NEURIA. |
| Contactos | Consultas, preinscripciones y acceso al administrador del chat. |
| Root | Módulos internos, configuración de dominio y credenciales de integración. |
| Chat | Comunidad con cuentas propias, moderación y transporte WebSocket con respaldo AJAX. |
| Preinscripción | Formulario institucional con selección de carrera y seguimiento interno. |

NEURIA centraliza la producción/distribución asistida por IA. ISFD conserva la revisión humana y publica desde su propia BD. El chat no es un chatbot de IA. El desarrollo del portal combina la dirección y validación del responsable humano con implementación, pruebas y documentación asistidas por Codex; las responsabilidades se explican en el manual técnico.

## Entorno

Apache con mod_rewrite; PHP 8.2+, mysqli/mysqlnd, cURL, mbstring, DOM, iconv, OpenSSL, GD, fileinfo y ZipArchive; MySQL/MariaDB con InnoDB. La instalación requiere el esquema histórico del sistema y las migraciones correspondientes: no es suficiente crear una base vacía y ejecutar únicamente 020.

La conexión se resuelve mediante `lib/mysql_conect.php`; conservar el adaptador y los valores del entorno de destino. Nunca versionar ni publicar contraseñas reales, `.env` o tokens. La configuración vigente de NEURIA se explica en [NEURIA_V1.md](docs/NEURIA_V1.md); [CONFIGURACION_NEURIA.md](CONFIGURACION_NEURIA.md) distingue el cliente actual del legado.

El shell `sistema/app_index.php` carga módulos dentro de `#content_seccion`. `lib/app.php` centraliza BD, sesiones, autorización, CSRF, escape y auditoría. Mantener este flujo al extender el proyecto.

Rutas públicas: `/`, `/nota/{id}/{slug}`, `/oferta/{slug}`, `/chat/`, `/preinscripcion/`. Acceso administrativo: `/mipanel`.

## WebSocket y procesos

El servidor WebSocket utiliza Ratchet/ReactPHP y Composer en `chat/server/`. Necesita un proceso persistente y proxy WSS para producción; su habilitación depende del hosting. Sin URL WSS se utiliza AJAX. Ver [Chat WebSocket](docs/CHAT_WEBSOCKET.md). No se necesita Node.js para servir PHP; se utiliza para comprobaciones y pruebas JavaScript en desarrollo.

## Verificación

Consultar requisitos y efectos de cada prueba antes de ejecutarla. Algunas crean bases efímeras; otras usan HTTP local y datos temporales. No ejecutar indiscriminadamente en producción.

```text
php -l lib/app.php
node --check scripts/app_ajax.js
node --check scripts/portal.js
php tests/noticias_adjuntos_test.php
php tests/categorias_test.php
php tests/preinscripciones_test.php
```

El manual técnico contiene el inventario de pruebas y las limitaciones conocidas. Git conserva código y migraciones; respaldar aparte BD, uploads y configuración privada.
