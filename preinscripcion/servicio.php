<?php
declare(strict_types=1);

/** Reglas compartidas por formulario público y gestión interna. */
final class Preinscripciones
{
    public const ESTADOS = ['pendiente'=>'Pendiente','en_revision'=>'En revisión','documentacion_pendiente'=>'Documentación pendiente','formalizada'=>'Formalizada','cancelada'=>'Cancelada'];
    public function __construct(private AppDatabase $db) {}

    public function carreras(): array
    {
        return $this->db->all("SELECT o.id,o.titulo,o.imagen_url,o.duracion_valor,o.duracion_unidad,m.nombre modalidad FROM ofertas o
            JOIN modalidades m ON m.id=o.modalidad_id
            JOIN catalogo_categorias c ON c.id=o.categoria_id
            JOIN catalogo_niveles n ON n.id=c.nivel_id
            WHERE o.estado_publicacion='publicada' AND o.deleted_at IS NULL
            AND o.estado_oferta='inscripcion_abierta' AND c.activo=1 AND n.activo=1
            AND NOT EXISTS (SELECT 1 FROM catalogo_categorias p WHERE c.ruta LIKE CONCAT(p.ruta,'%') AND p.activo=0)
            ORDER BY o.titulo");
    }

    public function registrar(array $datos, string $ipHash): void
    {
        $nombre=trim((string)($datos['nombre']??''));
        $apellido=trim((string)($datos['apellido']??''));
        $dni=trim((string)($datos['dni']??''));
        $email=trim((string)($datos['email']??''));
        $telefono=trim((string)($datos['telefono']??''));
        $oferta=(int)($datos['oferta_id']??0);
        if ($nombre==='' || $apellido==='' || mb_strlen($nombre)>100 || mb_strlen($apellido)>100) throw new DomainException('Ingresá nombre y apellido (hasta 100 caracteres cada uno).');
        if (!preg_match('/^[0-9]{7,8}$/D',$dni)) throw new DomainException('Ingresá el DNI sin puntos, con 7 u 8 dígitos.');
        if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) throw new DomainException('Ingresá un correo válido.');
        if (!preg_match('/^[0-9]{4} [0-9]{6}$/D',$telefono)) throw new DomainException('Ingresá tu celular con el formato 0000 000000.');
        if (($datos['acepta_politica']??'')!=='1') throw new DomainException('Aceptá el uso de tus datos para gestionar la preinscripción.');
        $this->db->transaction(function () use ($oferta,$nombre,$apellido,$dni,$email,$telefono,$ipHash): void {
            // El bloqueo coordina altas simultáneas y cambios administrativos de la oferta.
            $this->db->one('SELECT id FROM ofertas WHERE id=? FOR UPDATE','i',[$oferta]);
            $carreras=array_column($this->carreras(),null,'id');
            if (!isset($carreras[$oferta])) throw new DomainException('Esta carrera no tiene la inscripción abierta. Elegí una carrera disponible.');
            $anio=(int)date('Y');
            // Reenvíos no duplican ni sobrescriben datos ya recibidos. No se revelan datos personales.
            if ($this->db->one('SELECT id FROM portal_preinscripciones WHERE oferta_id=? AND anio=? AND dni=?','iis',[$oferta,$anio,$dni])) return;
            $recientes=$this->db->one('SELECT COUNT(*) total FROM portal_preinscripciones WHERE ip_hash=? AND created_at>=DATE_SUB(NOW(),INTERVAL 1 HOUR)','s',[$ipHash]);
            if ((int)$recientes['total']>=5) throw new DomainException('Alcanzaste el límite de envíos. Intentá nuevamente dentro de una hora.');
            $id=$this->db->insert('INSERT INTO portal_preinscripciones (oferta_id,oferta_titulo,anio,nombre,apellido,dni,email,telefono,ip_hash,consentimiento_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())','isissssss',[$oferta,$carreras[$oferta]['titulo'],$anio,$nombre,$apellido,$dni,$email,$telefono,$ipHash]);
            $this->db->insert("INSERT INTO portal_preinscripcion_historial (preinscripcion_id,estado,nota) VALUES (?,'pendiente','Recibida desde el formulario institucional.')",'i',[$id]);
        });
    }

    public function gestionar(int $id, string $estado, string $nota, int $usuario, string $anterior): void
    {
        if (!isset(self::ESTADOS[$estado]) || mb_strlen($nota)>3000) throw new DomainException('Revisá el estado y la nota (hasta 3000 caracteres).');
        $this->db->transaction(function () use ($id,$estado,$nota,$usuario,$anterior): void {
            $actual=$this->db->one('SELECT estado FROM portal_preinscripciones WHERE id=? FOR UPDATE','i',[$id]);
            if (!$actual) throw new DomainException('La preinscripción no existe.');
            if ($actual['estado']!==$anterior) throw new DomainException('El estado cambió. Recargá el detalle antes de guardar.');
            $this->db->execute('UPDATE portal_preinscripciones SET estado=? WHERE id=?','si',[$estado,$id]);
            $this->db->insert('INSERT INTO portal_preinscripcion_historial (preinscripcion_id,estado,nota,usuario_id) VALUES (?,?,?,?)','issi',[$id,$estado,trim($nota),$usuario]);
        });
    }
}
