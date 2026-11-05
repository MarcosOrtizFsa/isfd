# Configuración de NEURIA

Actualizado: 4 de noviembre de 2026.

## Integración vigente: API v1

La integración editorial y el Agente IA utilizan el cliente de `sistema/modulos/noticias/php/neuria_v1_cliente.php`. Ver [NEURIA_V1.md](docs/NEURIA_V1.md) para contratos, estados y sincronización.

Root administra la credencial en **Módulos internos → Credenciales Neuria**. El token es el emitido por NEURIA para ISFD; se cifra en BD con AES-256-GCM. La clave maestra se mantiene en el entorno privado:

```dotenv
ISFD_CREDENTIAL_KEY=CLAVE-ALEATORIA-PRIVADA-DE-AL-MENOS-32-CARACTERES
NEURIA_API_URL=https://neuriar.com.ar
NEURIA_API_TOKEN=TOKEN-DEL-MEDIO
NEURIA_API_TIMEOUT=30
NEURIA_SYNC_LIMIT=50
```

Son ejemplos; no reemplazar una clave maestra existente ni guardar valores reales en Git. La configuración activa y descifrable de BD tiene prioridad. Si no se obtiene, el cliente puede usar las variables del entorno. Revisar también esas variables y los cron al deshabilitar operativamente una integración.

La API v1 no requiere `NEURIA_CLIENT_ID`. La base se configura sin repetir `/api/v1`; el cliente agrega las rutas. Los lectores v1 cargan el `.env` de la raíz, mientras que otras partes del proyecto pueden depender directamente del entorno del proceso.

Las migraciones 008, 009 y 010 agregan sincronización, solicitudes y credenciales respectivamente. El indicador de Noticias comprueba identidad/autenticación, no ejecuta sincronización.

## Compatibilidad histórica

`home/php/neuria_cliente.php` y `config_private/neuria.local.php` corresponden al cliente anterior. Aquel protocolo utilizaba `NEURIA_CLIENT_ID`, el endpoint `/sistema/modulos/api_clientes/php/api_contenido.php` y caché temporal. Se conserva para recorridos heredados; no utilizarlo como guía para configurar la integración Laravel/API v1.

No copiar archivos privados locales al hosting ni incluir tokens en HTML, JavaScript, URLs, capturas o documentación. Conservar la clave de cifrado y la conexión de producción al desplegar código.
