<?php
require 'config.php';
require 'layout.php';
requireLogin();

// Búsqueda
$search = trim($_GET['q'] ?? '');
$sql    = "SELECT u.id, u.name, u.email, u.created_at FROM users u";
$params = [];
if ($search) {
    $sql   .= " WHERE u.name LIKE ? OR u.email LIKE ?";
    $params = ["%$search%", "%$search%"];
}
$sql .= " ORDER BY u.created_at DESC";

$stmt = getDB()->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

layoutHead('Usuarios');
layoutNav();
?>
<div class="container">
    <?= flash() ?>

    <div class="page-header">
        <h1 class="page-title">Usuarios</h1>
        <a href="create.php" class="btn btn-primary">+ Nuevo usuario</a>
    </div>

    <!-- Buscador -->
    <form method="GET" style="margin-bottom:16px;display:flex;gap:8px;">
        <input type="text" name="q" value="<?= h($search) ?>"
               placeholder="Buscar por nombre o correo..."
               style="max-width:320px;" />
        <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
        <?php if ($search): ?>
            <a href="index.php" class="btn btn-sm" style="background:#e2e8f0;color:#475569;">Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$users): ?>
                    <tr><td colspan="6" style="text-align:center;padding:32px;color:#94a3b8;">No se encontraron usuarios.</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $u):
                    $role      = getUserRole((int)$u['id']);
                    $badgeClass = match($role) {
                        'admin'   => 'badge-admin',
                        'manager' => 'badge-manager',
                        default   => 'badge-user',
                    };
                    $roleLabel = match($role) {
                        'admin'   => 'Admin',
                        'manager' => 'Gestor',
                        default   => 'Usuario',
                    };
                    $isSelf = ($u['id'] === $_SESSION['php_crud_user']['id']);
                ?>
                <tr>
                    <td class="text-muted"><?= h($u['id']) ?></td>
                    <td style="font-weight:500;"><?= h($u['name']) ?></td>
                    <td class="text-muted"><?= h($u['email']) ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= $roleLabel ?></span></td>
                    <td class="text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div class="actions">
                            <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <?php if (!$isSelf): ?>
                                <a href="delete.php?id=<?= $u['id'] ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('¿Eliminar a <?= h(addslashes($u['name'])) ?>?')">
                                    Eliminar
                                </a>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:.75rem;">Eres tú</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p class="text-muted" style="margin-top:12px;">Total: <?= count($users) ?> usuario<?= count($users) !== 1 ? 's' : '' ?></p>
</div>
<?php layoutFoot(); ?>
