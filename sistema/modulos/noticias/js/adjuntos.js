(function ($) {
    'use strict';
    var panel = $('#news-attachments-panel'), contenido = panel.find('.news-attachments');
    var noticiaId = contenido.attr('data-noticia-id');
    var resultado = contenido.find('.news-attachments-result');
    var ocupado = false;

    function informar(mensaje, error) {
        resultado.attr('class', 'news-attachments-result alert ' + (error ? 'alert-danger' : 'alert-success')).text(mensaje);
    }
    function enviar(datos) {
        if (ocupado) return;
        ocupado = true;
        datos.append('noticia_id', noticiaId);
        datos.append('csrf_token', $('meta[name="csrf-token"]').attr('content') || '');
        contenido.find('button').prop('disabled', true);
        $.ajax({url: 'modulos/noticias/php/app_adjuntos_action.php', method: 'POST', data: datos,
            processData: false, contentType: false, dataType: 'json'
        }).done(function (respuesta) {
            if (!respuesta.ok) { informar(respuesta.message || 'No se pudo guardar.', true); return; }
            // Actualiza solo adjuntos; conserva el texto editorial que se esté editando.
            cargar_post('modulos/noticias/php/app_adjuntos.php', 'news-attachments-panel', {noticia_id: noticiaId});
        }).fail(function (xhr) {
            informar((xhr.responseJSON || {}).message || 'La carga no se completó. Revisá el tamaño de los archivos y los límites del servidor.', true);
        }).always(function () { ocupado = false; contenido.find('button').prop('disabled', false); });
    }
    contenido.find('.news-attachment-upload').on('submit', function (event) {
        event.preventDefault(); enviar(new FormData(this));
    });
    contenido.find('input[type="file"]').on('change', function () {
        var vista = $(this).closest('form').find('.news-local-preview').empty();
        Array.from(this.files).slice(0, 10).forEach(function (archivo) {
            var item = $('<div class="col-sm-6 col-xl-4"></div>');
            if (/^image\/(jpeg|png|webp|gif)$/.test(archivo.type)) {
                var url = URL.createObjectURL(archivo);
                $('<img class="w-100 rounded mb-1" style="height:130px;object-fit:contain" alt="Vista previa antes de cargar">')
                    .on('load error', function () { URL.revokeObjectURL(url); }).attr('src', url).appendTo(item);
            }
            $('<small class="d-block text-break"></small>').text(archivo.name + ' · sin guardar').appendTo(item);
            vista.append(item);
        });
    });
    contenido.find('.news-attachment-remove').on('click', function () {
        if (!confirm('¿Quitar este archivo de la noticia?')) return;
        var datos = new FormData(); datos.append('action', 'file_remove'); datos.append('archivo_id', $(this).attr('data-file-id')); enviar(datos);
    });
    function youtube(valor) {
        valor = valor.trim();
        if (/^[A-Za-z0-9_-]{11}$/.test(valor)) return valor;
        try {
            var url = new URL(valor), codigo = '', ruta = url.pathname.replace(/^\/+|\/+$/g, '');
            if (!['http:', 'https:'].includes(url.protocol)) return '';
            if (url.hostname === 'youtu.be') codigo = ruta.split('/')[0];
            else if (['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'].includes(url.hostname)) {
                if (ruta === 'watch') codigo = url.searchParams.get('v') || '';
                else if (/^(embed|shorts|live)\/[^/]+$/.test(ruta)) codigo = ruta.split('/')[1];
            }
            return /^[A-Za-z0-9_-]{11}$/.test(codigo) ? codigo : '';
        } catch (error) { return ''; }
    }
    contenido.find('.news-video-preview-button').on('click', function () {
        var codigo = youtube(contenido.find('[name="youtube"]').val());
        if (!codigo) { informar('Ingresá una URL o código válido de YouTube.', true); return; }
        var marco = $('<iframe title="Vista previa del video (sin guardar)" allowfullscreen></iframe>').attr('src', 'https://www.youtube-nocookie.com/embed/' + codigo);
        contenido.find('.news-video-preview').empty().append($('<div class="ratio ratio-16x9"></div>').append(marco));
    });
    contenido.find('.news-video-form').on('submit', function (event) { event.preventDefault(); enviar(new FormData(this)); });
    contenido.find('.news-video-remove').on('click', function () {
        if (!confirm('¿Quitar el video guardado de esta noticia?')) return;
        var datos = new FormData(); datos.append('action', 'video_remove'); enviar(datos);
    });
})(jQuery);
