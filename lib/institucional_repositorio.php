<?php
declare(strict_types=1);

/** Definiciones del formulario; los textos y valores se almacenan exclusivamente en BD. */
function institucional_campos(): array
{
    return [
        'identidad' => ['Identidad institucional', ['nombre'=>'Nombre completo','nombre_corto'=>'Nombre corto','sigla'=>'Sigla']],
        'contacto' => ['Contacto y ubicación', ['direccion'=>'Dirección','entorno'=>'Entorno / calles','telefono'=>'Teléfono','email'=>'Correo público','celular'=>'Celular','whatsapp'=>'WhatsApp internacional','contactos_visibles'=>'Información adicional (sin repetir horarios)','jardin'=>'Dirección del jardín']],
        'horarios' => ['Horarios de atención', ['manana'=>'Mañana','tarde'=>'Tarde','noche'=>'Noche']],
        'historia' => ['Historia institucional', ['historia_titulo'=>'Título','historia_resumen'=>'Resumen de portada','historia_texto'=>'Historia completa (un párrafo por línea)']],
        'videos' => ['Historia institucional · Videos', ['videos_json'=>'Videos institucionales']],
        'autoridades' => ['Autoridades', ['autoridades_texto'=>'Una autoridad por línea: Cargo | Nombre completo']],
        'portada' => ['Presentación institucional', ['hero_etiqueta'=>'Texto superior','hero_titulo'=>'Título principal','hero_nombre'=>'Nombre destacado','hero_descripcion'=>'Presentación','pie_texto'=>'Descripción del pie']],
        'recursos' => ['Enlaces e imágenes institucionales', ['campus_url'=>'Campus virtual','preinscripcion_url'=>'Preinscripción','revista_url'=>'Revista','mapa_url'=>'URL del mapa embebido','hero_imagen'=>'Imagen de cabecera (ruta o URL)','institucional_imagen'=>'Imagen institucional (ruta o URL)']],
    ];
}

final class InstitucionalRepositorio
{
    public function __construct(private AppDatabase $db) {}

    public function actuales(): array
    {
        $result = [];
        foreach ($this->db->all('SELECT s.clave, v.id, v.anio, d.campo, d.valor FROM institucional_secciones s JOIN institucional_versiones v ON v.id=s.version_actual AND v.seccion_id=s.id JOIN institucional_valores d ON d.version_id=v.id') as $row) {
            $result[$row['campo']] = $row['valor'];
        }
        return $result;
    }

    public function guardar(string $section, int $year, string $reason, array $values, int $expected, ?int $user): int
    {
        $definition = institucional_campos()[$section] ?? null;
        if (!$definition || $year < 1900 || $year > 2200 || $reason === '' || mb_strlen($reason) > 250) throw new InvalidArgumentException('Revisá la sección, el año y el motivo del cambio.');
        $clean = [];
        foreach ($definition[1] as $key => $label) {
            if (!isset($values[$key]) || !is_string($values[$key])) throw new InvalidArgumentException('Falta el campo: '.$label);
            $value = trim(str_replace("\r\n", "\n", $values[$key]));
            if ($key === 'videos_json') {
                require_once __DIR__.'/videos_institucionales.php';
                $value=institucional_videos_validar($value);
            }
            if (mb_strlen($value) > 12000) throw new InvalidArgumentException($label.': máximo 12000 caracteres.');
            if ($key === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('El correo no es válido.');
            if (str_ends_with($key, '_url') || str_ends_with($key, '_imagen')) {
                if ($value !== '' && !preg_match('~^(https?://[^\s<>"\x27]+|\.\./[^\s<>"\x27]*|/(?!/)[^\s<>"\x27]*)$~i', $value)) throw new InvalidArgumentException($label.': usá una URL http(s) o una ruta local que comience con / o ../.');
            }
            if ($key === 'autoridades_texto') {
                foreach (explode("\n", $value) as $line) {
                    if (trim($line) !== '' && (!str_contains($line, '|') || count(array_filter(array_map('trim', explode('|', $line, 2)))) !== 2)) throw new InvalidArgumentException('Autoridades: cada línea debe indicar Cargo | Nombre.');
                }
            }
            $clean[$key] = $value;
        }
        return $this->db->transaction(function (AppDatabase $db) use ($section, $year, $reason, $clean, $expected, $user): int {
            $row = $db->one('SELECT id,version_actual FROM institucional_secciones WHERE clave=? FOR UPDATE', 's', [$section]);
            if (!$row || (int) $row['version_actual'] !== $expected) throw new UnexpectedValueException('El contenido cambió. Recargá el formulario antes de guardar.');
            $id = $db->insert('INSERT INTO institucional_versiones (seccion_id,anio,usuario_id,motivo) VALUES (?,?,?,?)', 'iiis', [(int)$row['id'],$year,$user,$reason]);
            foreach ($clean as $key=>$value) $db->execute('INSERT INTO institucional_valores (version_id,campo,valor) VALUES (?,?,?)', 'iss', [$id,$key,$value]);
            $db->execute('UPDATE institucional_secciones SET version_actual=? WHERE id=?', 'ii', [$id,(int)$row['id']]);
            return $id;
        });
    }
}
