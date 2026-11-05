# Manual básico para administradores — ISFD

Actualizado: 5 de noviembre de 2026. Incluye permisos por usuario y módulo (migración 021).

## 1. Ingresar y orientarse

1. Abrí `https://isfd.com.ar/mipanel`.
2. Ingresá con la cuenta administrativa que te asignaron.
3. Elegí el módulo desde el menú. Su contenido se carga dentro del panel.
4. Al terminar, usá **Salir**.

Las opciones disponibles dependen del acceso asignado. **Módulos internos** está reservado a Root. Contactos, preinscripciones y chat requieren los permisos asignados por Root. Si no ves una función, consultá al responsable del sistema; no uses credenciales de otra persona.

La cuenta pública del chat no es una cuenta administrativa. Los archivos HTML de las plantillas no se abren directamente para administrar: utilizá siempre el sitio servido por Apache.

## 2. Noticias: crear y publicar

### Guardar primero el texto

1. Entrá a **Noticias → Nueva noticia**.
2. Completá título y texto completo. Agregá bajada, volanta y sección cuando correspondan.
3. Elegí fecha, hora y estado. Para preparar una nota sin publicarla, usá **Pendiente**.
4. Presioná **Guardar texto**.
5. El editor permanece abierto y se habilita **Adjuntos de la nota**.

| Estado | Resultado |
|---|---|
| Pendiente | Guardada para revisión; no se publica. |
| Activa | Disponible para publicación pública; se respeta la fecha/hora programada. |
| Suspendida | Conservada en administración sin publicación pública. |

Para publicar después de preparar texto y adjuntos, seleccioná **Activa** y guardá el texto. Si no indicás fecha/hora al activar, se utiliza la actual. Revisá siempre la vista pública. Una restricción proveniente de NEURIA puede impedir activar un contenido retirado o suspendido.

### Adjuntar imágenes

1. En **Imágenes**, seleccioná uno o varios archivos.
2. Revisá la vista previa: hasta ese momento todavía no están guardados.
3. Presioná **Cargar imágenes**.
4. Las imágenes cargadas aparecerán en el panel; podés abrirlas para verlas completas o quitarlas con la papelera.

Formatos: JPG, PNG, WebP y GIF. Máximo 8 MB por imagen y diez archivos por carga. La primera imagen guardada según su orden se utiliza como portada; al quitarla, la siguiente pasa a ocupar ese lugar. El hosting puede imponer un límite total menor.

### Adjuntar documentos

Usá **Archivos descargables**, seleccioná PDF, Word (`.doc`, `.docx`) o Excel (`.xls`, `.xlsx`) y presioná **Cargar documentos**. Máximo 15 MB por archivo y diez por carga. Los nombres guardados aparecen como enlaces de descarga.

### Agregar un video

En **Video de YouTube**, pegá la URL o el código del video. **Vista previa** permite comprobar el enlace; **Guardar video** lo asocia a la nota. **Quitar video guardado** lo retira. Actualmente se administra un video YouTube por noticia.

Cada panel guarda por separado. Guardar el texto no borra los adjuntos; cargar o quitar un adjunto no descarta el texto que estés editando. Una selección de archivo o una vista previa no equivalen a guardado. Para ver la lista actualizada, usá **Volver al listado actualizado**.

### Editar y eliminar

En el listado usá el lápiz para editar, el botón de cambio de estado para recorrer los estados disponibles y la papelera para retirar una nota. Las notas eliminadas se consultan con el filtro de registros eliminados y pueden restaurarse como pendientes. La papelera de un archivo lo retira de la nota; el panel no ofrece restauración individual de adjuntos.

## 3. Trabajar con NEURIA y Agente IA

NEURIA centraliza la producción asistida por IA y entrega contenidos al instituto. El administrador sigue siendo responsable de revisar información, nombres, fechas, fuentes, imágenes y tono institucional antes de publicar.

Al entrar a Noticias se comprueba la conexión:

- **Conectada y activa:** NEURIA respondió y aceptó las credenciales.
- **Comprobando:** la consulta está en curso.
- **Sin configurar / Desactivada:** consultar al responsable root.
- **Conexión no verificada:** leer el mensaje y usar **Volver a comprobar**. Las noticias locales siguen disponibles.

El indicador no garantiza que una solicitud de IA haya terminado ni que exista una sincronización automática programada.

### Solicitar un contenido

1. Entrá a **AGENTE IA**.
2. Indicá el tema/título y una descripción clara de lo que necesitás.
3. Agregá instrucciones, categoría, prioridad y referencias cuando sean útiles. Los iconos de ayuda y ejemplos orientan cada campo.
4. Enviá la solicitud y consultá su estado y progreso en **Solicitudes a Neuria**.
5. Usá **Sincronizar** para consultar novedades. El cron solo funciona si fue configurado por el responsable técnico.
6. Cuando se importe la nota, abrila con **Ver nota**, revisala y completá los datos editoriales y adjuntos antes de activarla.

Si una solicitud muestra **ERROR**, NEURIA puede reintentarla. No crees otra solicitud inmediatamente: al sincronizar, ISFD vuelve a consultar ese registro, actualiza su avance y limpia el error si se recuperó. Si el problema persiste, informá el tema y el mensaje de error al responsable técnico, sin compartir tokens.

El contenido de NEURIA se edita como texto plano. Separá párrafos con una línea en blanco; no hace falta escribir etiquetas `<p>`. La publicación requiere revisión humana. No copies información privada innecesaria en las instrucciones enviadas a la plataforma.

## 4. Ofertas académicas

1. Entrá a **Ofertas académicas → Nueva oferta** o elegí el lápiz de una existente.
2. Completá título, categoría hoja y modalidad. Agregá resumen, descripción, destinatarios y requisitos.
3. Cargá imagen, video YouTube y plan de estudios cuando correspondan. Revisá la vista previa de la imagen.
4. Completá los datos de duración, título otorgado, unidad académica y convocatoria que utilice la carrera.
5. Guardá como borrador mientras esté en preparación; publicá cuando la información esté verificada.

El identificador del enlace se genera automáticamente a partir del título. Antes de cambiar un título, verificá si existen enlaces ya difundidos y consultá al responsable técnico si deben conservarse.

**Estado de publicación** y **estado de la oferta** son cosas distintas: una carrera puede estar publicada como información aunque su inscripción esté cerrada.

### Habilitar preinscripción

Para mostrar el botón destacado en la carrera, la oferta debe estar publicada, pertenecer a una rama activa y tener el estado **Inscripción abierta**. No basta con cargar una fecha en la convocatoria.

Si la configuración institucional contiene una URL externa de preinscripción, el botón utiliza ese formulario. Si está vacía, abre el formulario propio con la carrera seleccionada. Desde la portada, la persona elige una carrera entre las que tienen inscripción abierta.

### Administrar categorías

Desde **CATEGORÍAS** se puede crear, editar, ordenar, activar/desactivar y eliminar categorías vacías.

- Al crear, elegí nivel y categoría superior si corresponde.
- **Admite ofertas directamente** identifica una categoría final donde cargar carreras.
- Una categoría que agrupa hijas no puede recibir ofertas directamente.
- El árbol admite hasta tres niveles. Al editar se conservan su ubicación y el identificador del enlace.
- Si tiene ofertas o subcategorías, desactivala en vez de eliminarla. Desactivar una rama oculta sus ofertas del catálogo público.
- El filtro **Tipo** de la portada solo muestra categorías activas con al menos una oferta publicada. Una categoría recién creada y vacía no aparecerá todavía.

## 5. Contactos: atender consultas

Ingresá a **Contactos** y filtrá por estado y origen. La bandeja reúne consultas generales y consultas realizadas desde una carrera; estas últimas conservan el tema de la oferta.

1. Abrí **Ver mensaje**.
2. Marcá **Atendida** cuando comiences a gestionarla.
3. Contactá a la persona por los medios registrados. El icono de correo abre tu aplicación de email; no envía una respuesta por sí solo.
4. Marcá **Respondida** cuando hayas contestado efectivamente.
5. Usá **Reabrir** si requiere seguimiento nuevamente.

Cambiar el estado no envía mensajes automáticamente. Las consultas quedan guardadas aunque falle un servicio externo de correo.

## 6. Contactos → Preinscripciones

El resumen superior muestra recibidas, pendientes, en trámite y formalizadas del año de recepción elegido. La tabla por carrera permite comparar cantidades y filtrar pulsando una carrera. El resumen anual es general; la lista inferior aplica además los filtros de carrera, estado y búsqueda.

1. Filtrá por año, carrera o estado, o buscá por nombre, apellido, DNI o correo.
2. Abrí **Gestionar** para ver datos de contacto y consentimiento registrado.
3. Contactá al postulante, comprobá los requisitos institucionales y registrá una nota interna.
4. Elegí el estado y presioná **Guardar seguimiento**.

| Estado | Uso sugerido |
|---|---|
| Pendiente | Solicitud recibida, todavía sin atender. |
| En revisión | El equipo está verificando los datos o contactando al postulante. |
| Documentación pendiente | Falta completar requisitos o entregar documentación. |
| Formalizada | El equipo confirmó el trámite formal según las reglas institucionales. |
| Cancelada | La solicitud no continuará. Registrar el motivo. |

El sistema registra estado, nota, fecha y administrador en el historial. No verifica por sí solo certificados ni adjudica vacantes. **Formalizada** debe marcarse después de la comprobación humana.

Se conserva un registro por DNI/carrera/año de recepción. Un reenvío público no reemplaza los datos existentes. No se ofrece edición pública posterior ni una nueva alta para el mismo caso cancelado; coordiná estas situaciones con el responsable institucional/técnico. El año mostrado no representa necesariamente el año de cursada.

## 7. Contactos → Administrar chat institucional

El acceso abre una ventana aparte y requiere Acceso a Contactos; las acciones dependen de M/B/E. Permite ver reportes, contexto de mensajes, participantes y últimas decisiones.

1. Leé el reporte y su contexto antes de actuar.
2. Elegí advertir, pausar, expulsar, rehabilitar, retirar mensaje o descartar reporte, según las opciones del caso.
3. Escribí un motivo claro y presioná **Aplicar**.
4. Verificá la decisión en el historial.

La detección automática usa reglas locales y puede equivocarse. Las pausas automáticas duran diez minutos; la pausa administrativa ofrecida es de 24 horas. La expulsión requiere decisión humana y puede revertirse. No compartas los correos ni el contenido privado de los participantes fuera del proceso de gestión.

El chat puede mostrar transporte WebSocket o AJAX. **AJAX no significa que el chat esté roto**: es su alternativa funcional cuando WebSocket no está disponible. El servidor WSS de producción necesita habilitación técnica del hosting.

## 8. Configuración institucional — solo root

Ruta: **Módulos internos → Configuración de dominio**.

Desde allí se administran identidad/dominio, contacto, redes, contenido institucional, enlaces e imágenes, autoridades y videos. Algunos datos se guardan como versiones: completá año y motivo cuando el formulario lo solicite. No cambies dominio, enlaces técnicos o parámetros de integración sin revisar su efecto.

### Enlaces e imágenes institucionales

- **Campus Virtual:** destino externo; se abre aparte.
- **Preinscripción:** URL del formulario externo. Dejarla vacía activa el formulario propio del instituto.
- Imágenes y enlaces restantes: revisar la portada después de guardar.
- No asignar nuevos usos a **Contador 1, 2, 3 y 4**; no forman parte de los cambios públicos acordados.

### Autoridades y videos

En **Autoridades**, completar nombre, apellido, cargo, profesión y datos básicos disponibles; cargar foto, enlace del saludo, orden y visibilidad. El estado visible controla su aparición en la portada. Conservar el historial al actualizar autoridades.

En **Historia institucional · Videos**, administrar la lista de videos y su visibilidad. La portada muestra uno por vez y permite pasar al siguiente. Utilizar enlaces admitidos por el formulario, no pegar código HTML de reproductores. Esta lista es distinta del video de una noticia.

### Credenciales NEURIA

Ruta: **Módulos internos → Credenciales Neuria**. Solo root modifica URL, token, timeout, límite y estado. El token privado es el emitido por NEURIA para el medio ISFD. Se guarda cifrado y no se vuelve a mostrar. La clave de cifrado se configura por el responsable técnico en el entorno privado.

Después de guardar, usar **Probar conexión**. No pegar tokens en capturas, consultas públicas, noticias ni documentación.

## 9. Control de usuarios

El módulo **Control** administra cuentas del equipo. Crear o modificar datos y acceso solo cuando corresponda; las restricciones dependen de los permisos asignados y del rol de la cuenta actual. Usar cuentas individuales y contraseñas propias. El usuario del chat se administra desde el chat, no desde Control.

### Permisos y accesos (solo Root)

1. En la fila del usuario, pulsá el icono de escudo/candado.
2. Marcá **Acceso** para cada módulo que deba ver.
3. Marcá **A** (agregar), **B** (borrar), **M** (modificar) y **E** (cambiar estado), según su responsabilidad.
4. Guardá. La autorización se aplica en la siguiente petición; recargar actualiza el menú.

Director y los demás roles no tienen permisos automáticos. Los usuarios nuevos comienzan sin accesos. Root conserva acceso total y no necesita asignación. Módulos internos no se delega. Un usuario no root solo puede gestionar cuentas de rol inferior.

Sin E no se puede publicar ni cambiar estados desde el formulario. Cargar adjuntos requiere M; quitarlos, B. Sincronizar NEURIA requiere A+M+E. Consultá [la matriz y las reglas de permisos](PERMISOS.md).

## 10. Problemas frecuentes

| Situación | Qué revisar |
|---|---|
| No aparece una categoría en la portada | Debe estar activa, con rama/nivel activos y oferta publicada. |
| No aparece el botón de preinscripción | Publicación de la oferta, estado Inscripción abierta y categoría activa. |
| Preinscripción abre otro sitio | Hay una URL externa configurada; vaciarla si corresponde usar el formulario propio. |
| No puedo cargar imágenes en una nota nueva | Guardar primero título y texto completo. |
| Un adjunto no sube | Formato, tamaño, panel correcto y límites del hosting. Probar un archivo por vez. |
| El video no reproduce | URL/código correcto y permiso del video para insertarse en otras páginas. |
| El captcha venció | Generar otro código y volver a completarlo. |
| El celular no valida | Ingresar diez dígitos; la máscara muestra `0000 000000`. |
| Sesión vencida | Volver a `/mipanel` e ingresar. |
| No se ve un cambio de interfaz | Recargar con Ctrl+F5; si persiste, informar al responsable técnico. |
| Error de NEURIA | Revisar el mensaje, volver a comprobar y sincronizar; evitar solicitudes duplicadas. |

Ante una falla persistente, informar módulo, acción realizada, fecha/hora y mensaje exacto. Compartir capturas sin contraseñas, tokens ni datos personales innecesarios.

## 11. Rutina sugerida

Al iniciar la jornada: revisar consultas y preinscripciones pendientes, solicitudes NEURIA y publicaciones programadas. Antes de publicar: verificar texto, fecha, imagen, archivos, video y presentación pública. Durante actividades del chat: mantener moderación humana. Al finalizar: guardar cambios, cerrar sesión y comunicar incidencias pendientes.

Para instalación, recursos y mantenimiento consultar el [Manual técnico](MANUAL_TECNICO.md). Índice general: [Documentación del proyecto](INDICE.md).

## Registro de acciones — solo Root

En Módulos internos → Registro de acciones podés consultar quién realizó cada operación, su fecha, módulo y detalle. Filtrá por administrador, fechas o A/B/M/E. Se muestran 50 registros por página. El historial es de solo lectura. Ver [Auditoría](AUDITORIA.md).
