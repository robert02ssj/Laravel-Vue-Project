<?php
require 'config.php';
require 'layout.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php', 'Usuario no encontrado.', 'error');

$stmt = getDB()->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) redirect('index.php', 'Usuario no encontrado.', 'error');

$currentRole = getUserRole($id);
$errors      = [];
$form = [
    'name'  => $user['name'],
    'email' => $user['email'],
    'role'  => $currentRole,
    'password'         => '',
    'password_confirm' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'name'             => trim($_POST['name']             ?? ''),
        'email'            => trim($_POST['email']            ?? ''),
        'role'             => $_POST['role']                  ?? 'user',
        'password'         => $_POST['password']              ?? '',
        'password_confirm' => $_POST['password_confirm']      ?? '',
    ];

    // Validación
    if (!$form['name'])
        $errors['name'] = 'El nombre es obligatorio.';

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Correo inválido.';
    } else {
        $stmt = getDB()->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $stmt->execute([$form['email'], $id]);
        if ($stmt->fetch()) $errors['email'] = 'Ese correo ya está en uso.';
    }

    if ($form['password'] !== '') {
        if (strlen($form['password']) < 8)
            $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
        elseif ($form['password'] !== $form['password_confirm'])
            $errors['password_confirm'] = 'Las contraseñas no coinciden.';
    }

    if (!in_array($form['role'], ['admin', 'manager', 'user']))
        $errors['role'] = 'Rol inválido.';

    if (!$errors) {
        $now  = date('Y-m-d H:i:s');
        $db   = getDB();

        if ($form['password'] !== '') {
            $hash = password_hash($form['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $db->prepare("UPDATE users SET name=?, email=?, password=?, updated_at=? WHERE id=?");
            $stmt->execute([$form['name'], $form['email'], $hash, $now, $id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET name=?, email=?, updated_at=? WHERE id=?");
            $stmt->execute([$form['name'], $form['email'], $now, $id]);
        }

        setUserRole($id, $form['role']);

        redirect('index.php', "Usuario \"{$form['name']}\" actualizado correctamente.");
    }
}

layoutHead('Editar usuario');
layoutNav();
?>
<div class="container">
    <?= flash() ?>
    <div class="page-header">
        <h1 class="page-title">Editar usuario</h1>
        <a href="index.php" class="btn btn-sm" style="background:#e2e8f0;color:#475569;">← Volver</a>
    </div>

    <div class="card form-card">
        <form method="POST" novalidate>
            <div class="form-group">
                <label for="name">Nombre *</label>
                <input type="text" id="name" name="name"
                       value="<?= h($form['name']) ?>" required />
                <?php if (isset($errors['name'])): ?>
                    <p class="text-danger"><?= h($errors['name']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico *</label>
                <input type="email" id="email" name="email"
                       value="<?= h($form['email']) ?>" required />
                <?php if (isset($errors['email'])): ?>
                    <p class="text-danger"><?= h($errors['email']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Nueva contraseña <span class="text-muted">(dejar en blanco para no cambiar)</span></label>
                <input type="password" id="password" name="password" />
                <?php if (isset($errors['password'])): ?>
                    <p class="text-danger"><?= h($errors['password']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmar nueva contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" />
                <?php if (isset($errors['password_confirm'])): ?>
                    <p class="text-danger"><?= h($errors['password_confirm']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="role">Rol *</label>
                <select id="role" name="role">
                    <option value="user"    <?= $form['role'] === 'user'    ? 'selected' : '' ?>>Usuario</option>
                    <option value="manager" <?= $form['role'] === 'manager' ? 'selected' : '' ?>>Gestor</option>
                    <option value="admin"   <?= $form['role'] === 'admin'   ? 'selected' : '' ?>>Administrador</option>
                </select>
                <?php if (isset($errors['role'])): ?>
                    <p class="text-danger"><?= h($errors['role']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="index.php" class="btn" style="background:#e2e8f0;color:#475569;">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php layoutFoot(); ?>
