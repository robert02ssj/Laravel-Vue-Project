<?php
require 'config.php';
require 'layout.php';
requireLogin();

$errors = [];
$form   = ['name' => '', 'email' => '', 'password' => '', 'password_confirm' => '', 'role' => 'user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = [
        'name'             => trim($_POST['name']             ?? ''),
        'email'            => trim($_POST['email']            ?? ''),
        'password'         => $_POST['password']              ?? '',
        'password_confirm' => $_POST['password_confirm']      ?? '',
        'role'             => $_POST['role']                  ?? 'user',
    ];

    // Validación
    if (!$form['name'])
        $errors['name'] = 'El nombre es obligatorio.';

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL))
        $errors['email'] = 'Correo inválido.';
    else {
        $stmt = getDB()->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$form['email']]);
        if ($stmt->fetch()) $errors['email'] = 'Ese correo ya está registrado.';
    }

    if (strlen($form['password']) < 8)
        $errors['password'] = 'La contraseña debe tener al menos 8 caracteres.';
    elseif ($form['password'] !== $form['password_confirm'])
        $errors['password_confirm'] = 'Las contraseñas no coinciden.';

    if (!in_array($form['role'], ['admin', 'manager', 'user']))
        $errors['role'] = 'Rol inválido.';

    if (!$errors) {
        $db   = getDB();
        $hash = password_hash($form['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        $now  = date('Y-m-d H:i:s');

        $stmt = $db->prepare(
            "INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$form['name'], $form['email'], $hash, $now, $now]);
        $newId = (int)$db->lastInsertId();

        setUserRole($newId, $form['role']);

        redirect('index.php', "Usuario \"{$form['name']}\" creado correctamente.");
    }
}

layoutHead('Nuevo usuario');
layoutNav();
?>
<div class="container">
    <?= flash() ?>
    <div class="page-header">
        <h1 class="page-title">Nuevo usuario</h1>
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
                <label for="password">Contraseña * <span class="text-muted">(mínimo 8 caracteres)</span></label>
                <input type="password" id="password" name="password" required />
                <?php if (isset($errors['password'])): ?>
                    <p class="text-danger"><?= h($errors['password']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirmar contraseña *</label>
                <input type="password" id="password_confirm" name="password_confirm" required />
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
                <button type="submit" class="btn btn-primary">Crear usuario</button>
                <a href="index.php" class="btn" style="background:#e2e8f0;color:#475569;">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php layoutFoot(); ?>
