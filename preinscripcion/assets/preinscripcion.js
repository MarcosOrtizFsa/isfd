(function ($) {
    'use strict';
    $('#carrera').on('change', function () {
        var elegida = $(this).find(':selected'), imagen = elegida.attr('data-imagen');
        $('#career-title').text(this.value ? elegida.text() : 'Seleccioná una carrera');
        $('#career-facts').text(elegida.attr('data-detalle') || 'Aquí verás un resumen de la propuesta elegida.');
        var foto = $('#career-image').addClass('d-none').removeAttr('src');
        if (imagen) {
            try {
                var url = new URL(imagen, window.location.href);
                if (url.protocol === 'https:' || url.protocol === 'http:') foto.attr('src', url.href).removeClass('d-none');
            } catch (error) { /* La preinscripción sigue disponible sin imagen. */ }
        }
    }).trigger('change');
    $('#career-image').on('error', function () { $(this).addClass('d-none'); });
    function captcha() {
        $('#captcha-image').attr('src', '../api/captcha?context=preinscripcion&_=' + Date.now());
        $('#captcha').val('');
    }
    $('#captcha-refresh').on('click', captcha);
    $('#telefono').on('input', function () {
        var numeros = this.value.replace(/\D/g, '').slice(0, 10);
        this.value = numeros.length > 4 ? numeros.slice(0, 4) + ' ' + numeros.slice(4) : numeros;
    });
    $('#preinscripcion-form').on('submit', function (event) {
        event.preventDefault();
        var form = $(this), boton = form.find('[type="submit"]'), resultado = $('#preinscripcion-result');
        boton.prop('disabled', true).text('Enviando…');
        resultado.removeClass('alert alert-danger alert-success').text('');
        $.ajax({url: 'api.php', method: 'POST', dataType: 'json', timeout: 20000,
            data: form.serialize() + '&csrf_token=' + encodeURIComponent($('meta[name="csrf-token"]').attr('content'))
        }).done(function (respuesta) {
            resultado.addClass('alert alert-success').text(respuesta.message).trigger('focus');
            form.hide();
        }).fail(function (xhr) {
            resultado.addClass('alert alert-danger').text((xhr.responseJSON || {}).message || 'No se pudo confirmar el envío. Podés reintentar sin duplicar tu preinscripción.').trigger('focus');
            captcha();
        }).always(function () { boton.prop('disabled', false).text('Enviar preinscripción'); });
    });
})(jQuery);
