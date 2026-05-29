<?php

session_start();

define('DB_PATH', __DIR__ . '/../../database/database.sqlite');
define('APP_NAME', 'Gestión de Usuarios');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
    $color = $f['type'] === 'success' ? '#d1fae5' : '#fee2e2';
    $border = $f['type'] === 'success' ? '#6ee7b7' : '#fca5a5';
    $text  = $f['type'] === 'success' ? '#065f46' : '#991b1b';
    return "<div style='background:{$color};border:1px solid {$border};color:{$text};padding:10px 16px;border-radius:8px;margin-bottom:16px;'>"
        . h($f['msg']) . "</div>";
}

function getRoleId(string $roleName): ?int {
    $stmt = getDB()->prepare("SELECT id FROM roles WHERE name = ? LIMIT 1");
    $stmt->execute([$roleName]);
    $row = $stmt->fetch();
    return $row ? (int)$row['id'] : null;
}

function getUserRole(int $userId): string {
    $stmt = getDB()->prepare(
        "SELECT r.name FROM roles r
         JOIN model_has_roles mhr ON mhr.role_id = r.id
         WHERE mhr.model_id = ? AND mhr.model_type = 'App\\\\Models\\\\User'
         LIMIT 1"
    );
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ? $row['name'] : 'user';
}

function setUserRole(int $userId, string $roleName): void {
    $db     = getDB();
    $roleId = getRoleId($roleName);
    if (!$roleId) return;

    $del = $db->prepare(
        "DELETE FROM model_has_roles WHERE model_id = ? AND model_type = 'App\\\\Models\\\\User'"
    );
    $del->execute([$userId]);

    $ins = $db->prepare(
        "INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, 'App\\\\Models\\\\User', ?)"
    );
    $ins->execute([$roleId, $userId]);
}
