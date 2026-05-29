<?php
function layoutHead(string $title): void { ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= h($title) ?> — <?= APP_NAME ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f8fafc; color: #1e293b; min-height: 100vh; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* Navbar */
        .navbar { background: #1e40af; color: white; padding: 0 24px; height: 56px; display: flex; align-items: center; justify-content: space-between; }
        .navbar .brand { font-size: 1.1rem; font-weight: 700; color: white; }
        .navbar .nav-links { display: flex; gap: 20px; align-items: center; font-size: 0.9rem; }
        .navbar .nav-links a { color: #bfdbfe; }
        .navbar .nav-links a:hover { color: white; text-decoration: none; }
        .navbar .user { color: #bfdbfe; font-size: 0.85rem; }

        /* Contenedor */
        .container { max-width: 960px; margin: 32px auto; padding: 0 20px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .page-title { font-size: 1.5rem; font-weight: 700; }

        /* Tarjeta */
        .card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }

        /* Tabla */
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        thead { background: #f1f5f9; }
        th { padding: 12px 16px; text-align: left; font-size: 0.75rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; font-weight: 600; }
        td { padding: 12px 16px; border-top: 1px solid #f1f5f9; vertical-align: middle; }
        tr:hover td { background: #f8fafc; }

        /* Badges */
        .badge { display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .badge-admin   { background: #ede9fe; color: #6d28d9; }
        .badge-manager { background: #dbeafe; color: #1d4ed8; }
        .badge-user    { background: #f0fdf4; color: #15803d; }

        /* Botones */
        .btn { display: inline-block; padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 500; cursor: pointer; border: none; transition: opacity .15s; }
        .btn:hover { opacity: .85; text-decoration: none; }
        .btn-primary { background: #2563eb; color: white; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-danger  { background: #ef4444; color: white; }
        .btn-sm { padding: 5px 12px; font-size: 0.8rem; }

        /* Formulario */
        .form-group { margin-bottom: 16px; }
        label { display: block; font-size: 0.85rem; font-weight: 500; margin-bottom: 6px; color: #374151; }
        input[type=text], input[type=email], input[type=password], select {
            width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 9px 12px;
            font-size: 0.9rem; outline: none; transition: border-color .15s;
        }
        input:focus, select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px #bfdbfe55; }
        .form-actions { display: flex; gap: 10px; margin-top: 20px; }
        .form-card { max-width: 520px; padding: 28px; }

        /* Helper */
        .text-muted { color: #94a3b8; font-size: 0.85rem; }
        .text-danger { color: #ef4444; font-size: 0.82rem; margin-top: 4px; }
        .actions { display: flex; gap: 8px; }
    </style>
</head>
<body>
<?php }

function layoutNav(): void {
    $user = $_SESSION['php_crud_user'] ?? null; ?>
<nav class="navbar">
    <span class="brand">👤 <?= APP_NAME ?></span>
    <div class="nav-links">
        <a href="index.php">Usuarios</a>
        <a href="create.php">+ Nuevo</a>
        <a href="../" style="color:#93c5fd;">← Volver a la app</a>
        <?php if ($user): ?>
            <span class="user"><?= h($user['name']) ?></span>
            <a href="logout.php">Salir</a>
        <?php endif; ?>
    </div>
</nav>
<?php }

function layoutFoot(): void { ?>
</body>
</html>
<?php }
