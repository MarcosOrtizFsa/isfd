# Configuración privada de NEURIA

La integración lee estas variables del entorno del proceso PHP/Apache:

```text
NEURIA_API_URL=https://dominio-base-de-neuria.example
NEURIA_CLIENT_ID=identificador-asignado
NEURIA_API_TOKEN=token-privado
```

No guardar valores reales en este repositorio, JavaScript, HTML, `.htaccess` público ni URLs.

## Prueba temporal dentro del proyecto

Durante el desarrollo local se puede completar:

```text
config_private/neuria.local.php
```

La carpeta está bloqueada por Apache y el archivo se encuentra excluido mediante
`.gitignore`. Las variables de entorno completas siempre tienen prioridad sobre
esta configuración local.

En Apache pueden definirse en la configuración privada del VirtualHost o mediante
variables del servicio, siempre fuera del directorio público. Después de cambiarlas,
reiniciar Apache para que PHP reciba los nuevos valores.

El cliente agrega exactamente esta ruta a `NEURIA_API_URL`:

```text
/sistema/modulos/api_clientes/php/api_contenido.php
```

Por compatibilidad local también se acepta el endpoint completo; internamente se
normaliza antes de efectuar la consulta.

La caché dura 300 segundos y se guarda fuera del sitio público, dentro del directorio
temporal del servidor. Si NEURIA falla y existe una copia anterior, se utiliza esa copia.

## Verificación local

Con PHP CLI disponible:

```text
php tests/neuria_cliente_test.php
node --check scripts/js_codex.js
```

El primer test requiere iniciar el mock incluido en `tests/neuria_mock_router.php` en
`127.0.0.1:18087`, tal como se hace durante la verificación de desarrollo.
