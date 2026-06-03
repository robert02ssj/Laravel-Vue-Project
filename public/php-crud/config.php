<?php

// Archivo: config.php
// Propósito: configuración mínima y funciones helper para la pequeña sección "php-crud".
// Comentarios en español para que cualquiera entienda su uso.

// Inicia la sesión para gestionar autenticación y flashes.
session_start();

// Ruta al fichero SQLite que usa este mini-crud (relativa al proyecto).
define('DB_PATH', __DIR__ . '/../../database/database.sqlite');
// Nombre de la aplicación que se muestra en la interfaz.
define('APP_NAME', 'Gestión de Usuarios');

// Obtiene una instancia PDO compartida hacia la base de datos SQLite.
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . DB_PATH);
        // Lanzar excepciones en errores para programación más segura.
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Devolver filas como arrays asociativos por defecto.
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    return $pdo;
}

// Comprueba si hay un usuario autenticado en la sesión.
function isLoggedIn(): bool {
    return isset($_SESSION['php_crud_user']);
}

// Redirige a `login.php` si no hay sesión iniciada.
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Escapa HTML (helper) para evitar XSS al mostrar valores en las vistas.
function h(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Redirección con mensaje flash opcional. `type` puede ser 'success' o 'error'.
function redirect(string $url, string $msg = '', string $type = 'success'): void {
    if ($msg) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
    }
    header("Location: $url");
    exit;
}

// Renderiza y consume el mensaje flash guardado en la sesión.
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

// Devuelve el id de un rol por su nombre, o null si no existe.
function getRoleId(string $roleName): ?int {
    $stmt = getDB()->prepare("SELECT id FROM roles WHERE name = ? LIMIT 1");
    $stmt->execute([$roleName]);
    $row = $stmt->fetch();
    return $row ? (int)$row['id'] : null;
}

// Obtiene el nombre del rol asociado a un usuario (tabla `model_has_roles`).
// Si no encuentra nada, devuelve 'user' por defecto.
function getUserRole(int $userId): string {
    $stmt = getDB()->prepare(
        "SELECT r.name FROM roles r
         JOIN model_has_roles mhr ON mhr.role_id = r.id
         WHERE mhr.model_id = ? AND mhr.model_type = 'App\\Models\\User'
         LIMIT 1"
    );
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return $row ? $row['name'] : 'user';
}

// Asigna (o reasigna) un rol a un usuario: borra roles previos y añade el nuevo.
function setUserRole(int $userId, string $roleName): void {
    $db     = getDB();
    $roleId = getRoleId($roleName);
    if (!$roleId) return;

    // Borramos cualquier asociación previa del usuario.
    $del = $db->prepare(
        "DELETE FROM model_has_roles WHERE model_id = ? AND model_type = 'App\\Models\\User'"
    );
    $del->execute([$userId]);

    // Insertamos la nueva relación rol-usuario.
    $ins = $db->prepare(
        "INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES (?, 'App\\Models\\User', ?)"
    );
    $ins->execute([$roleId, $userId]);
}
