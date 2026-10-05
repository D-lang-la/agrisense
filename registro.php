<?php
require_once __DIR__ . '/config/sesiones.php';
require_once __DIR__ . '/config/conexion.php';

if (estaLogueado()) {
    header('Location: dashboard.php');
    exit;
}

$errores = [];
$exito   = false;

$nombre = $apellido = $correo = $usuario = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? null)) {
        $errores[] = 'Sesión de formulario inválida. Recarga la página e inténtalo de nuevo.';
    } else {
        $nombre      = trim($_POST['nombre'] ?? '');
        $apellido    = trim($_POST['apellido'] ?? '');
        $correo      = trim($_POST['correo'] ?? '');
        $usuario     = trim($_POST['usuario'] ?? '');
        $contrasena  = $_POST['contrasena'] ?? '';
        $confirmar   = $_POST['confirmar_contrasena'] ?? '';

        if ($nombre === '' || $apellido === '' || $correo === '' || $usuario === '' || $contrasena === '') {
            $errores[] = 'Todos los campos son obligatorios.';
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido.';
        }
        if (strlen($contrasena) < 8) {
            $errores[] = 'La contraseña debe tener al menos ocho caracteres.';
        }
        if ($contrasena !== $confirmar) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if (empty($errores)) {
            $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE usuario = :usuario');
            $stmt->execute(['usuario' => $usuario]);
            if ($stmt->fetch()) {
                $errores[] = 'Ese nombre de usuario ya está en uso.';
            }

            $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE correo = :correo');
            $stmt->execute(['correo' => $correo]);
            if ($stmt->fetch()) {
                $errores[] = 'Ese correo ya está registrado.';
            }
        }

        if (empty($errores)) {
            $hash = password_hash($contrasena, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO usuarios (nombre, apellido, correo, usuario, contraseña)
                 VALUES (:nombre, :apellido, :correo, :usuario, :contrasena)'
            );
            $stmt->execute([
                'nombre'     => $nombre,
                'apellido'   => $apellido,
                'correo'     => $correo,
                'usuario'    => $usuario,
                'contrasena' => $hash,
            ]);
            $exito = true;
        }
    }
}

$tituloPagina = 'Crear cuenta';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width:520px;">
        <div class="text-center mb-3">
            <img src="assets/img/logo.png" alt="AgriSense" class="logo-img-lg mb-2">
            <h4 class="brand-text mb-0">Crear cuenta</h4>
            <p class="text-muted small">Únete a AgriSense y monitorea tus cultivos.</p>
        </div>

        <?php if ($exito): ?>
            <div class="alert alert-success py-2">
                Cuenta creada correctamente. Ya puedes <a href="login.php">iniciar sesión</a>.
            </div>
        <?php else: ?>

            <?php if (!empty($errores)): ?>
                <div class="alert alert-danger py-2">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errores as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($nombre) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellido</label>
                        <input type="text" name="apellido" class="form-control" required value="<?= htmlspecialchars($apellido) ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" name="correo" class="form-control" required value="<?= htmlspecialchars($correo) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="usuario" class="form-control" required value="<?= htmlspecialchars($usuario) ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="contrasena" class="form-control" required minlength="8">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <input type="password" name="confirmar_contrasena" class="form-control" required minlength="8">
                    </div>
                </div>

                <button type="submit" class="btn btn-agrisense w-100 mt-2">Crear cuenta</button>
            </form>

            <p class="text-center mt-4 mb-0 small">
                ¿Ya tienes cuenta?
                <a href="login.php" class="auth-footer-link">Inicia sesión</a>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
