<?php
declare(strict_types=1);
require_once __DIR__.'/neuria_requests.php';

/** Panel y cron reciben las entregas del medio sin depender de solicitudes locales. */
final class NeuriaCoordinador
{
    public function __construct(private AppDatabase $db, private NeuriaV1Cliente $client) {}

    public function sincronizar(): array
    {
        // Comparte bloqueo con bin/neuria_sync.php para evitar carreras entre panel y cron.
        $lock = fopen(sys_get_temp_dir().DIRECTORY_SEPARATOR.'isfd_neuria_sync.lock','c');
        if (!$lock) throw new RuntimeException('No se pudo preparar el bloqueo de sincronización.');
        if (!flock($lock,LOCK_EX|LOCK_NB)) {
            fclose($lock);
            throw new RuntimeException('Ya existe una sincronización de NEURIA en curso.');
        }
        try {
            if (!$this->client->estaConfigurado()) throw new RuntimeException('NEURIA no está configurada o está desactivada.');
            // Un fallo de solicitudes no impide recibir entregas independientes.
            $solicitudes = $this->ejecutar(fn() => (new NeuriaSolicitudes($this->db,$this->client))->sincronizarPendientes());
            $sync = new NeuriaSincronizador($this->db,$this->client);
            $contenidos = $this->ejecutar(fn() => $sync->sincronizarContenidos());
            $cambios = $this->ejecutar(fn() => $sync->sincronizarCambios());
            return [
                'ok'=>$solicitudes['ok'] && $contenidos['ok'] && $cambios['ok'],
                'actualizadas'=>(int)($solicitudes['actualizadas'] ?? 0),
                'importadas'=>(int)($solicitudes['importadas'] ?? 0)+(int)($contenidos['importados'] ?? 0)+(int)($cambios['importados'] ?? 0),
                'errores'=>(int)$solicitudes['errores']+(int)$contenidos['errores']+(int)$cambios['errores'],
                'solicitudes'=>$solicitudes,
                'contenidos'=>$contenidos,
                'cambios'=>$cambios,
            ];
        } finally {
            flock($lock,LOCK_UN);
            fclose($lock);
        }
    }

    private function ejecutar(callable $operation): array
    {
        try {
            $result = $operation();
            if (!$result['ok']) $result['errores'] = max(1,(int)($result['errores'] ?? 0));
            return $result;
        } catch (Throwable $error) {
            error_log('Sincronización NEURIA: '.$error->getMessage());
            return ['ok'=>false,'errores'=>1,'error'=>'No se pudo completar este flujo. Revisá el registro del servidor.'];
        }
    }
}
