(function ($) {
  'use strict';
  var csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
  var $status = $('#app-status');

  function payload(data) {
    var value = typeof data === 'string' ? data : $.param(data || {});
    return value + (value ? '&' : '') + 'csrf_token=' + encodeURIComponent(csrfToken);
  }
  function loading(active) {
    $status.toggleClass('is-visible', active).text(active ? 'Cargando…' : '');
  }
  window.cargar_post = function (url, target, data, callback) {
    loading(true);
    return $.ajax({url: url, method: 'POST', data: payload(data), cache: false})
      .done(function (html) { $('#' + target).html(html); if (typeof callback === 'function') callback(html); })
      .fail(function (xhr) { $('#' + target).html($('<div class="container py-4"><div class="alert alert-danger"></div></div>').find('.alert').text(xhr.responseText || 'No se pudo completar la solicitud.').end()); })
      .always(function () { loading(false); });
  };
  window.appAction = function (url, data, success) {
    loading(true);
    return $.ajax({url: url, method: 'POST', data: payload(data), dataType: 'json', cache: false})
      .done(function (response) { if (!response.ok) return window.alert(response.message || 'La operacion no pudo completarse.'); if (response.message) window.alert(response.message); if (typeof success === 'function') success(response); })
      .fail(function (xhr) { window.alert(xhr.responseText || 'No se pudo completar la solicitud.'); })
      .always(function () { loading(false); });
  };
  $(function () {
    var $content = $('#content_seccion');
    cargar_post($content.data('initial-url'), 'content_seccion', $content.data('initial-data') || '');
    $('#module-navigation').on('click', '[data-module]', function () { cargar_post('modulos/' + $(this).data('module') + '/php/app_home.php', 'content_seccion', {id_system_01: $(this).data('module-id')}); $('.navbar-collapse').collapse('hide'); });
    $('#logout-button').on('click', function () { appAction('php/app_logout.php', {}, function () { window.location.href = '../mipanel'; }); });
  });
})(jQuery);

