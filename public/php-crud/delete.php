<?php
// Archivo: delete.php
// Propósito: eliminar un usuario y sus relaciones (roles, tokens) de la BD.
// Notas: evita que el usuario autenticado se borre a sí mismo.

require 'config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php', 'Usuario no válido.', 'error');

// No permitir que un usuario se elimine a sí mismo.
if ($id === (int)$_SESSION['php_crud_user']['id']) {
    redirect('index.php', 'No puedes eliminarte a ti mismo.', 'error');
}

// Comprobamos que el usuario exista y recuperamos su nombre para el flash.
$stmt = getDB()->prepare("SELECT name FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) redirect('index.php', 'Usuario no encontrado.', 'error');

$db = getDB();

// Eliminar roles asociados (tabla intermedia model_has_roles).
$db->prepare("DELETE FROM model_has_roles WHERE model_id = ? AND model_type = ?")->execute([$id, MODEL_TYPE]);

// Eliminar tokens personales (si se usa Laravel Sanctum en la DB de ejemplo).
$db->prepare("DELETE FROM personal_access_tokens WHERE tokenable_id = ? AND tokenable_type = ?")->execute([$id, MODEL_TYPE]);

// Eliminar el registro del usuario.
$db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

// Redirigimos con mensaje de éxito.
redirect('index.php', "Usuario \"{$user['name']}\" eliminado.");
