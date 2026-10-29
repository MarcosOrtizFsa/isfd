<?php
declare(strict_types=1);
require __DIR__ . '/../../../../lib/app.php';
if (app_user() !== null) {
    echo '<div class="container admin-page"><div class="alert alert-info">Ya iniciaste sesion. Usa el menu superior para continuar.</div></div>';
    exit;
}
?>
<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-9 col-md-6 col-lg-4">
      <div class="card admin-card border-0"><div class="card-body p-4 p-lg-5">
        <div class="text-center mb-4"><i class="bi bi-shield-lock fs-1 text-primary"></i><h1 class="h3 mt-2">Administracion ISFD</h1><p class="text-secondary mb-0">Ingresa con tu usuario y clave.</p></div>
        <form id="login-form" novalidate>
          <div class="mb-3"><label class="form-label required" for="login-user">Usuario</label><input class="form-control form-control-lg" id="login-user" name="usuario" maxlength="40" autocomplete="username" required></div>
          <div class="mb-3"><label class="form-label required" for="login-password">Clave</label><input class="form-control form-control-lg" type="password" id="login-password" name="clave" maxlength="128" autocomplete="current-password" required></div>
          <div id="login-message" class="alert alert-danger d-none" role="alert"></div>
          <button class="btn btn-primary btn-lg w-100" type="submit">Ingresar</button>
        </form>
        <a class="d-block text-center mt-3" href="../">Volver al portal</a>
      </div></div>
    </div>
  </div>
</section>
<script>
$('#login-form').on('submit', function (event) {
  event.preventDefault();
  var $message = $('#login-message').addClass('d-none');
  appAction('modulos/login/php/app_login.php', $(this).serialize(), function () { window.location.href = '../mipanel'; })
    .fail(function (xhr) { $message.removeClass('d-none').text(xhr.responseJSON ? xhr.responseJSON.message : 'No se pudo iniciar sesion.'); });
});
</script>

