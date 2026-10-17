<?php
declare(strict_types=1);

/**
 * Nucleo compartido de la aplicacion.
 * Centraliza base de datos, sesion, seguridad y respuestas HTTP.
 */

date_default_timezone_set('America/Argentina/Buenos_Aires');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('ISFDSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

final class AppDatabase
{
    private mysqli $connection;

    public function __construct(string $host, string $user, string $password, string $database)
    {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        $this->connection = new mysqli($host, $user, $password, $database);
        $this->connection->set_charset('utf8mb4');
    }

    public function all(string $sql, string $types = '', array $params = []): array
    {
        $statement = $this->prepare($sql, $types, $params);
        $result = $statement->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function one(string $sql, string $types = '', array $params = []): ?array
    {
        $rows = $this->all($sql, $types, $params);
        return $rows[0] ?? null;
    }

    public function execute(string $sql, string $types = '', array $params = []): int
    {
        $statement = $this->prepare($sql, $types, $params);
        return $statement->affected_rows;
    }

    public function insert(string $sql, string $types = '', array $params = []): int
    {
        $this->prepare($sql, $types, $params);
        return (int) $this->connection->insert_id;
    }

    public function transaction(callable $operation): mixed
    {
        $this->connection->begin_transaction();
        try {
            $result = $operation($this);
            $this->connection->commit();
            return $result;
        } catch (Throwable $error) {
            $this->connection->rollback();
            throw $error;
        }
    }

    private function prepare(string $sql, string $types, array $params): mysqli_stmt
    {
        $statement = $this->connection->prepare($sql);
        if ($types !== '') {
            $statement->bind_param($types, ...$params);
        }
        $statement->execute();
        return $statement;
    }
}

function app_db(): AppDatabase
{
    static $database;
    if (!$database instanceof AppDatabase) {
        require __DIR__ . '/mysql_conect.php';
        $database = new AppDatabase(HOST, USU, CLA, BD);
    }
    return $database;
}

function app_escape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function app_int(string $key, int $default = 0): int
{
    $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
    return $value === false || $value === null ? $default : $value;
}

function app_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => (int) $_SESSION['user_id'],
        'role_id' => (int) ($_SESSION['role_id'] ?? 0),
        'site_id' => (int) ($_SESSION['site_id'] ?? 0),
        'mode' => (int) ($_SESSION['user_mode'] ?? 3),
        'name' => (string) ($_SESSION['user_name'] ?? 'Usuario'),
    ];
}

function app_require_login(): array
{
    $user = app_user();
    if ($user === null) {
        http_response_code(401);
        exit('<div class="alert alert-warning">Tu sesion vencio. <a href="../mipanel">Volver a ingresar</a>.</div>');
    }
    return $user;
}

function app_require_mode(array $modes): array
{
    $user = app_require_login();
    if (!in_array($user['mode'], $modes, true)) {
        http_response_code(403);
        exit('<div class="alert alert-danger">No tenes permisos para realizar esta accion.</div>');
    }
    return $user;
}

function app_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function app_verify_csrf(): void
{
    $received = (string) ($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($received === '' || !hash_equals(app_csrf_token(), $received)) {
        http_response_code(403);
        exit('La solicitud vencio. Recarga la pagina e intenta nuevamente.');
    }
}

function app_log(string $action, string $details = '', string $message = 'OK', int $moduleId = 0): void
{
    $user = app_user();
    if ($user === null) {
        return;
    }
    app_db()->insert(
        'INSERT INTO system_05_logistica (rela_system_03, rela_system_07, rela_system_06, rela_system_01, system_05_fecha, system_05_hora, system_05_accion, system_05_detalles, system_05_mensaje) VALUES (?, ?, ?, ?, CURDATE(), CURTIME(), ?, ?, ?)',
        'iiiisss',
        [$user['id'], $user['role_id'], $user['site_id'], $moduleId, mb_substr($action, 0, 22), $details, mb_substr($message, 0, 33)]
    );
}

function app_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

