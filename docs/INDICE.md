# Documentación del proyecto ISFD

Actualizada el 4 de noviembre de 2026 contra el código hasta `50de00d`.

## Documentos principales

- [Manual técnico](MANUAL_TECNICO.md): arquitectura, recursos y tecnologías, APIs, IA, papel central de NEURIA, desarrollo híbrido humano/agente, datos, seguridad, operación y límites.
- [Manual básico del administrador](MANUAL_ADMINISTRADOR.md): uso cotidiano del panel, publicación, adjuntos, catálogo, consultas, preinscripciones y moderación.
- [Importación y despliegue en producción](../database/IMPORTACION_PRODUCCION.md): migraciones hasta 020, actualización por versión y comprobaciones.

## Guías por dominio

| Dominio | Documento |
|---|---|
| Noticias y adjuntos | [NOTICIAS.md](NOTICIAS.md) |
| API actual de NEURIA | [NEURIA_V1.md](NEURIA_V1.md) |
| Configuración de NEURIA y compatibilidad anterior | [CONFIGURACION_NEURIA.md](../CONFIGURACION_NEURIA.md) |
| Catálogo y categorías | [CATALOGO.md](CATALOGO.md), [modelo ER académico](catalogo-er.md) |
| Datos importados de referencia | [OFERTAS_IMPORTADAS_FAC.md](OFERTAS_IMPORTADAS_FAC.md) |
| Contenido institucional versionado | [CONTENIDO_INSTITUCIONAL.md](CONTENIDO_INSTITUCIONAL.md) |
| Autoridades | [AUTORIDADES.md](AUTORIDADES.md) |
| Videos institucionales | [VIDEOS_INSTITUCIONALES.md](VIDEOS_INSTITUCIONALES.md) |
| Chat y moderación | [CHAT_INSTITUCIONAL.md](CHAT_INSTITUCIONAL.md) |
| Transporte WebSocket/AJAX | [CHAT_WEBSOCKET.md](CHAT_WEBSOCKET.md) |
| Preinscripciones | [PREINSCRIPCIONES.md](PREINSCRIPCIONES.md) |

Los documentos de referencia aportados por el responsable y las notas locales no versionadas no sustituyen estos manuales. No incorporar credenciales reales al documentar ejemplos.

## Mantenimiento

Cada cambio funcional debe actualizar la guía de su dominio; si cambia un flujo administrativo, actualizar además el manual de usuario. Cambios en tablas, dependencias, configuración o rutas deben reflejarse en el manual técnico y la guía de despliegue. Registrar qué migraciones se ejecutaron realmente en cada entorno. La existencia de código o SQL en Git no prueba su instalación en producción.

- [Permisos por usuario y módulo](PERMISOS.md): acceso, ABME, jerarquía, migración 021 y pruebas.

- [Registro de acciones administrativas](AUDITORIA.md): historial exclusivo de Root, filtros y cobertura ABME.
