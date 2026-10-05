<?php
require_once __DIR__ . '/config/sesiones.php';
requerirLoginRaiz();
require_once __DIR__ . '/config/conexion.php';

$idUsuario = $_SESSION['id_usuario'];

$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = :id');
$stmt->execute(['id' => $idUsuario]);
$usuario = $stmt->fetch();

$errores = [];
$exito   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? null)) {
        $errores[] = 'Sesión de formulario inválida. Recarga la página.';
    } else {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'datos') {
            $nombre   = trim($_POST['nombre'] ?? '');
            $apellido = trim($_POST['apellido'] ?? '');
            $correo   = trim($_POST['correo'] ?? '');

            if ($nombre === '' || $apellido === '' || $correo === '') {
                $errores[] = 'Todos los campos son obligatorios.';
            }
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El correo no es válido.';
            }

            if (empty($errores)) {
                $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE correo = :correo AND id_usuario != :id');
                $stmt->execute(['correo' => $correo, 'id' => $idUsuario]);
                if ($stmt->fetch()) {
                    $errores[] = 'Ese correo ya está en uso por otra cuenta.';
                }
            }

            if (empty($errores)) {
                $stmt = $pdo->prepare('UPDATE usuarios SET nombre = :nombre, apellido = :apellido, correo = :correo WHERE id_usuario = :id');
                $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'correo' => $correo, 'id' => $idUsuario]);
                $_SESSION['nombre'] = $nombre . ' ' . $apellido;
                $exito = 'Datos actualizados correctamente.';

                $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = :id');
                $stmt->execute(['id' => $idUsuario]);
                $usuario = $stmt->fetch();
            }
        }

        if ($accion === 'password') {
            $actual     = $_POST['actual'] ?? '';
            $nueva      = $_POST['nueva'] ?? '';
            $confirmar  = $_POST['confirmar'] ?? '';

            if (!password_verify($actual, $usuario['contraseña'])) {
                $errores[] = 'La contraseña actual es incorrecta.';
            }
            if (strlen($nueva) < 8) {
                $errores[] = 'La nueva contraseña debe tener al menos ocho caracteres.';
            }
            if ($nueva !== $confirmar) {
                $errores[] = 'Las contraseñas nuevas no coinciden.';
            }

            if (empty($errores)) {
                $hash = password_hash($nueva, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE usuarios SET contraseña = :hash WHERE id_usuario = :id');
                $stmt->execute(['hash' => $hash, 'id' => $idUsuario]);
                $exito = 'Contraseña actualizada correctamente.';
            }
        }
    }
}

$tituloPagina = 'Perfil';
$paginaActual = 'perfil';
require_once __DIR__ . '/includes/header.php';
?>

<div class="app-shell">
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <main class="main-content">
        <div class="topbar">
            <h1>Mi perfil</h1>
        </div>

        <?php if ($exito): ?>
            <div class="alert alert-success py-2"><?= htmlspecialchars($exito) ?></div>
        <?php endif; ?>
        <?php if (!empty($errores)): ?>
            <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3"><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="card-agrisense">
                    <h5 class="mb-3">Datos personales</h5>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">
                        <input type="hidden" name="accion" value="datos">

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellido</label>
                            <input type="text" name="apellido" class="form-control" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['usuario']) ?>" disabled>
                            <div class="form-text">El nombre de usuario no se puede modificar.</div>
                        </div>

                        <button type="submit" class="btn btn-agrisense">Guardar cambios</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-agrisense">
                    <h5 class="mb-3">Cambiar contraseña</h5>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">
                        <input type="hidden" name="accion" value="password">

                        <div class="mb-3">
                            <label class="form-label">Contraseña actual</label>
                            <input type="password" name="actual" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" name="nueva" class="form-control" minlength="8" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" name="confirmar" class="form-control" minlength="8" required>
                        </div>

                        <button type="submit" class="btn btn-agrisense-azul">Actualizar contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
