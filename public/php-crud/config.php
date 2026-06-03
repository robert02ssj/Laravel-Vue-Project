<?php

session_start();

// Lee una variable del fichero .env del proyecto raíz
function env(string $key, string $default = ''): string {
    static $vars = null;
    if ($vars === null) {
        $vars = [];
        $envFile = __DIR__ . '/../../.env';
        if (file_exists($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
                [$k, $v] = explode('=', $line, 2);
                $vars[trim($k)] = trim($v, " \t\n\r\"'");
            }
        }
    }
    return $vars[$key] ?? $default;
}

define('APP_NAME', 'Gestión de Usuarios');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $driver = env('DB_CONNECTION', 'mysql');

        if ($driver === 'sqlite') {
            $path = __DIR__ . '/../../database/database.sqlite';
            $dsn  = 'sqlite:' . $path;
            $pdo  = new PDO($dsn);
        } else {
            $host   = env('DB_HOST',     'mysql');
            $port   = env('DB_PORT',     '3306');
            $dbname = env('DB_DATABASE', 'reservas');
            $user   = env('DB_USERNAME', 'sail');
            $pass   = env('DB_PASSWORD', 'password');
            $dsn    = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $pdo    = new PDO($dsn, $user, $pass);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE,       PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}

function isLoggedIn(): bool {
    return isset($_SESSION['php_crud_user']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url, string $msg = '', string $type = 'success'): void {
    if ($msg) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    }
    header("Location: $url");
    exit;
}

function flash(): string {
    if (!isset($_SESSION['flash'])) return '';
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $color  = $f['type'] === 'success' ? '#d1fae5' : '#fee2e2';
    $border = $f['type'] === 'success' ? '#6ee7b7' : '#fca5a5';
    $text   = $f['type'] === 'success' ? '#065f46' : '#991b1b';
    return "<div style='background:{$color};border:1px solid {$border};color:{$text};padding:10px 16px;border-radius:8px;margin-bottom:16px;'>"
        . h($f['msg']) . "</div>";
}

function getRoleId(string $roleName): ?int {
    $stmt = getDB()->prepare("SELECT id FROM roles WHERE name = ? LIMIT 1");
    $stmt->execute([$roleName]);
    $row = $stmt->fetch();
    return $row ? (int)$row['id'] : null;
}

define('MODEL_TYPE', 'App\Models\User');

function getUserRole(int $userId): string {
    $stmt = getDB()->prepare(
        "SELECT r.name FROM roles r
         JOIN model_has_roles mhr ON mhr.role_id = r.id
         WHERE mhr.model_id = ? AND mhr.model_type = ?
         LIMIT 1"
    );
    $stmt->execute([$userId, MODEL_TYPE]);
    $row = $stmt->fetch();
    return $row ? $row['name'] : 'user';
}

function setUserRole(int $userId, string $roleName): void {
    $db     = getDB();
    $roleId = getRoleId($roleName);
    if (!$roleId) return;

    $db->prepare(
        "DELETE FROM model_has_roles WHERE model_id = ? AND model_type = ?"
    )->execute([$userId, MODEL_TYPE]);

    $db->prepare(
        "INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, ?, ?)"
    )->execute([$roleId, MODEL_TYPE, $userId]);
}
