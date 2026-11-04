(function ($) {
    'use strict';
    $('#news-form').on('submit', function (event) {
        event.preventDefault();
        var formulario = $(this), boton = formulario.find('[type="submit"]');
        var esNueva = Number(formulario.find('[name="id"]').val()) === 0;
        var datos = formulario.serialize() + '&csrf_token=' + encodeURIComponent($('meta[name="csrf-token"]').attr('content') || '');
        var resultado = $('#news-save-result');
        boton.prop('disabled', true);
        $.ajax({url: 'modulos/noticias/php/app_action.php', method: 'POST', data: datos, dataType: 'json'})
            .done(function (respuesta) {
                resultado.attr('class', 'alert ' + (respuesta.ok ? 'alert-success' : 'alert-danger')).text(respuesta.message || 'No se pudo guardar.');
                if (!respuesta.ok) return;
                formulario.find('[name="id"]').val(respuesta.id);
                // La nota recién guardada habilita adjuntos sin cerrar el editor ni recargar la lista.
                if (esNueva) {
                    $('#news-editor-title').text('Editar noticia');
                    formulario.find('select[name="estado"]').prop('disabled', false).val(formulario.find('input[name="estado"]').val() || formulario.find('select[name="estado"]').val());
                    formulario.find('input[name="estado"]').remove();
                    cargar_post('modulos/noticias/php/app_adjuntos.php', 'news-attachments-panel', {noticia_id: respuesta.id});
                }
            }).fail(function (xhr) {
                resultado.attr('class', 'alert alert-danger').text((xhr.responseJSON || {}).message || 'No se pudo guardar el texto.');
            }).always(function () { boton.prop('disabled', false); });
    });
})(jQuery);
