(function ($) {
  'use strict';
  var csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
  var $status = $('#app-status');
  var fieldSequence = 0;

  function prepareAdminForms(container) {
    // Etiquetas asociadas a sus controles, incluso en fragmentos cargados por AJAX.
    $(container).find('.admin-editor .form-label').each(function () {
      if (this.htmlFor || this.querySelector('input,select,textarea')) return;
      var control = this.parentElement.querySelector('input:not([type="hidden"]),select,textarea');
      if (!control) return;
      if (!control.id) control.id = 'admin-field-' + (++fieldSequence);
      this.htmlFor = control.id;
    });
  }

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
      .done(function (html) {
        var container = document.getElementById(target);
        $(container).html(html);
        prepareAdminForms(container);
        var editor = container && container.querySelector('.admin-editor');
        if (editor && target !== 'content_seccion') {
          editor.scrollIntoView({block: 'start', behavior: 'auto'});
        }
        if (typeof callback === 'function') callback(html);
      })
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
    var header = document.querySelector('.admin-shell');
    function updateHeaderSpace() {
      var height = header ? header.getBoundingClientRect().height : 0;
      document.documentElement.style.setProperty('--admin-shell-height', height + 'px');
      document.body.classList.toggle('has-admin-session', height > 0);
    }
    updateHeaderSpace();
    if (header && window.ResizeObserver) new ResizeObserver(updateHeaderSpace).observe(header);
    window.addEventListener('resize', updateHeaderSpace);
    var $content = $('#content_seccion');
    cargar_post($content.data('initial-url'), 'content_seccion', $content.data('initial-data') || '');
    $('#module-navigation').on('click', '[data-module]', function () {
      $('#module-navigation [data-module]').removeClass('active').removeAttr('aria-current');
      $(this).addClass('active').attr('aria-current', 'page');
      cargar_post('modulos/' + $(this).data('module') + '/php/app_home.php', 'content_seccion', {id_system_01: $(this).data('module-id')});
      // Solo cerrar el menú administrativo; no alterar el de la portada.
      var menu = document.getElementById('admin-nav');
      if (menu && window.bootstrap) bootstrap.Collapse.getOrCreateInstance(menu, {toggle: false}).hide();
    });
    $('#logout-button').on('click', function () { appAction('php/app_logout.php', {}, function () { window.location.href = '../mipanel'; }); });
  });
})(jQuery);

