<?php
require 'config.php';
require 'layout.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = 'Rellena todos los campos.';
    } else {
        $stmt = getDB()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $role = getUserRole((int)$user['id']);
            if (!in_array($role, ['admin', 'manager'])) {
                $error = 'No tienes permiso para acceder a esta sección.';
            } else {
                $_SESSION['php_crud_user'] = [
                    'id'   => $user['id'],
                    'name' => $user['name'],
                    'role' => $role,
                ];
                redirect('index.php', 'Bienvenido, ' . $user['name']);
            }
        } else {
            $error = 'Credenciales incorrectas.';
        }
    }
}

layoutHead('Acceder');
?>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f1f5f9;">
    <div style="background:white;border-radius:16px;padding:36px;width:100%;max-width:380px;border:1px solid #e2e8f0;box-shadow:0 4px 16px rgba(0,0,0,.08);">
        <h1 style="font-size:1.4rem;font-weight:700;margin-bottom:6px;">👤 <?= APP_NAME ?></h1>
        <p style="color:#64748b;font-size:0.875rem;margin-bottom:24px;">Accede con una cuenta de admin o gestor</p>

        <?php if ($error): ?>
            <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:.875rem;">
                <?= h($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" required
                       value="<?= h($_POST['email'] ?? '') ?>"
                       placeholder="admin@reservas.es" />
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="••••••••" />
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:10px;">Acceder</button>
        </form>

        <p style="margin-top:16px;font-size:.8rem;color:#94a3b8;text-align:center;">
            Usuarios de prueba: admin@reservas.es / password
        </p>
    </div>
</div>
<?php layoutFoot(); ?>
