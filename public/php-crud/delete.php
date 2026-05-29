<?php
require 'config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php', 'Usuario no válido.', 'error');

// No puede borrarse a sí mismo
if ($id === (int)$_SESSION['php_crud_user']['id']) {
    redirect('index.php', 'No puedes eliminarte a ti mismo.', 'error');
}

$stmt = getDB()->prepare("SELECT name FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) redirect('index.php', 'Usuario no encontrado.', 'error');

$db = getDB();

// Eliminar roles asociados
$db->prepare("DELETE FROM model_has_roles WHERE model_id = ? AND model_type = 'App\\Models\\User'")->execute([$id]);

// Eliminar tokens Sanctum
$db->prepare("DELETE FROM personal_access_tokens WHERE tokenable_id = ? AND tokenable_type = 'App\\Models\\User'")->execute([$id]);

// Eliminar usuario
$db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

redirect('index.php', "Usuario \"{$user['name']}\" eliminado.");
