<?php
declare(strict_types=1);
/** Importación puntual y revisable. CLI; no modifica convocatorias ni otras ofertas. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../lib/app.php';
$items = json_decode(file_get_contents(__DIR__.'/seeds/ofertas_referencia_fac.json'), true, 512, JSON_THROW_ON_ERROR);
$db = app_db();
$marker = __DIR__.'/../config_private/importacion_ofertas_fac_completada.json';
if (is_file($marker)) { echo "La importación ya fue aplicada. No se sobrescriben revisiones posteriores.\n"; exit; }
$backup = [
    'created_at'=>date(DATE_ATOM),
    'source'=>'https://galeanosantiago.github.io/FaC/carreras.html',
    'ofertas'=>$db->all('SELECT * FROM ofertas'),
    'plan_estudios'=>$db->all('SELECT * FROM oferta_plan_estudios'),
];
$backupPath = __DIR__.'/../config_private/ofertas_antes_fac_'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'.json';
if (file_put_contents($backupPath, json_encode($backup,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),LOCK_EX) === false) throw new RuntimeException('No se pudo crear el respaldo.');
$report = $db->transaction(function(AppDatabase $db) use ($items): array {
    $result = [];
    $modality = $db->one("SELECT id FROM modalidades WHERE codigo='presencial' AND activo=1");
    $unit = $db->one("SELECT id FROM unidades_academicas WHERE nombre='Nivel Superior' AND activo=1");
    if (!$modality || !$unit) throw new RuntimeException('Faltan modalidad o unidad académica.');
    foreach ($items as $item) {
        $category = $db->one('SELECT id FROM catalogo_categorias WHERE slug=? AND activo=1 AND admite_ofertas=1','s',[$item['type']]);
        if (!$category) throw new RuntimeException('Falta la categoría '.$item['type']);
        $existing = $db->all('SELECT * FROM ofertas WHERE deleted_at IS NULL AND (titulo=? OR titulo=?) FOR UPDATE','ss',[$item['title'],$item['match']??$item['title']]);
        if (count($existing)>1) throw new RuntimeException('Coincidencia ambigua para '.$item['title']);
        $row = $existing[0]??null;
        $image = '../uploads/ofertas/referencia-fac/'.$item['image'];
        if (!is_file(__DIR__.'/../uploads/ofertas/referencia-fac/'.$item['image'])) throw new RuntimeException('Falta imagen '.$item['image']);
        $description = $item['description']."\n\nHorario informado en la referencia: 19:00 a 23:00 hs. Consultá la organización vigente con el instituto.";
        $requirements = 'Consultar con la institución la documentación, las condiciones de ingreso y las fechas de inscripción vigentes.';
        if ($row) {
            $id = (int)$row['id'];
            // Reemplaza la descripción demostrativa; conserva contenidos particulares ya editados.
            $demo = trim((string)$row['descripcion'])==='' || str_contains((string)$row['descripcion'],'Contenido DEMO');
            $db->execute('UPDATE ofertas SET resumen=?,descripcion=?,perfil_egresado=?,requisitos_ingreso=?,duracion_valor=?,duracion_unidad=?,imagen_url=? WHERE id=?','ssssissi',[
                $demo?$item['summary']:($row['resumen']?:$item['summary']),
                $demo?$description:$row['descripcion'],
                $row['perfil_egresado']?:$item['audience'],
                $row['requisitos_ingreso']?:$requirements,
                $item['years'],'anios',$row['imagen_url']?:$image,$id,
            ]);
        } else {
            $slug = trim((string)preg_replace('/[^a-z0-9]+/','-',strtolower((string)iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$item['title']))),'-');
            $id = $db->insert("INSERT INTO ofertas (categoria_id,unidad_academica_id,modalidad_id,slug,titulo,resumen,descripcion,perfil_egresado,requisitos_ingreso,duracion_valor,duracion_unidad,imagen_url,estado_oferta,estado_publicacion) VALUES (?,?,?,?,?,?,?,?,?,?,'anios',?,'proximamente','borrador')",'iiissssssis',[
                (int)$category['id'],(int)$unit['id'],(int)$modality['id'],$slug,$item['title'],$item['summary'],$description,$item['audience'],$requirements,$item['years'],$image,
            ]);
        }
        $planCount=(int)$db->one('SELECT COUNT(*) total FROM oferta_plan_estudios WHERE oferta_id=?','i',[$id])['total'];
        if ($planCount===0) {
            foreach ($item['plans'] as $order=>[$title,$content]) $db->insert('INSERT INTO oferta_plan_estudios (oferta_id,titulo,contenido,orden) VALUES (?,?,?,?)','issi',[$id,$title,$content,$order]);
        }
        $result[]=['id'=>$id,'accion'=>$row?'actualizada':'creada en borrador','titulo'=>$row['titulo']??$item['title'],'fuente'=>'https://galeanosantiago.github.io/FaC/carreras.html?id='.$item['source_id'],'plan_anios_cargados'=>$planCount?:count($item['plans'])];
    }
    return $result;
});
if (file_put_contents($marker,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),LOCK_EX)===false) throw new RuntimeException('La BD se actualizó, pero no se pudo guardar el registro de importación.');
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
echo 'Respaldo: '.$backupPath.PHP_EOL;
