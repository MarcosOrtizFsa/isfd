<?php
declare(strict_types=1);
require_once __DIR__.'/../../../../lib/app.php';
app_require_mode([0]);
$db=app_db();
$items=$db->all('SELECT * FROM institucional_autoridades ORDER BY orden,id');
?>
<div class="card admin-card my-4"><div class="card-body p-3 p-lg-4">
<div class="d-flex flex-wrap justify-content-between gap-3 mb-3"><div><h2 class="h4">Autoridades</h2><p class="text-secondary mb-0">Foto, presentación y saludo. En PC se muestran cinco tarjetas por fila.</p></div><button class="btn btn-primary" onclick="cargar_post('modulos/root/php/app_autoridades_form.php','authority-editor','')">Agregar autoridad</button></div>
<div id="authority-editor"></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Orden</th><th>Nombre y apellido</th><th>Cargo</th><th>Año</th><th>Portada</th><th></th></tr></thead><tbody>
<?php foreach($items as $row): ?><tr><td><?=(int)$row['orden']?></td><td><?=app_escape($row['nombre'].' '.$row['apellido'])?></td><td><?=app_escape($row['cargo'])?></td><td><?=(int)$row['anio']?></td><td><?=$row['visible']?'Visible':'Oculta'?></td><td><button class="btn btn-outline-primary btn-sm" onclick="cargar_post('modulos/root/php/app_autoridades_form.php','authority-editor',{id:<?=(int)$row['id']?>})">Editar / historial</button></td></tr><?php endforeach; ?>
</tbody></table></div>
<p class="small text-secondary mb-0">Para un cambio de autoridades, ocultá la persona saliente y agregá la nueva. Su ficha e historial permanecen disponibles.</p>
</div></div>
