<?php
require_once __DIR__ . '/config/sesiones.php';
require_once __DIR__ . '/config/conexion.php';

if (estaLogueado()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCSRF($_POST['csrf_token'] ?? null)) {
        $error = 'Sesión de formulario inválida. Recarga la página e inténtalo de nuevo.';
    } else {
        $identificador = trim($_POST['identificador'] ?? '');
        $contrasena    = $_POST['contrasena'] ?? '';

        if ($identificador === '' || $contrasena === '') {
            $error = 'Completa todos los campos.';
        } else {
            $stmt = $pdo->prepare(
                'SELECT * FROM usuarios WHERE usuario = :ident1 OR correo = :ident2 LIMIT 1'
            );
            $stmt->execute(['ident1' => $identificador, 'ident2' => $identificador]);
            $usuario = $stmt->fetch();

            if (!$usuario) {
                $error = 'El usuario no existe.';
            } elseif (!password_verify($contrasena, $usuario['contraseña'])) {
                $error = 'La contraseña es incorrecta.';
            } else {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre']     = $usuario['nombre'] . ' ' . $usuario['apellido'];
                $_SESSION['usuario']    = $usuario['usuario'];
                header('Location: dashboard.php');
                exit;
            }
        }
    }
}

$tituloPagina = 'Iniciar sesión';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-3">
            <img src="assets/img/logo.png" alt="AgriSense" class="logo-img-lg mb-2">
            <h4 class="brand-text mb-0">AgriSense</h4>
            <p class="text-muted small">Cultivos más saludables, cosechas más inteligentes.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <input type="hidden" name="csrf_token" value="<?= tokenCSRF() ?>">

            <div class="mb-3">
                <label class="form-label">Usuario o correo</label>
                <input type="text" name="identificador" class="form-control" required
                       value="<?= htmlspecialchars($_POST['identificador'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <div class="input-group">
                    <input type="password" name="contrasena" id="contrasena" class="form-control" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('contrasena', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-agrisense w-100 mt-2">Iniciar sesión</button>
        </form>

        <p class="text-center mt-4 mb-0 small">
            ¿No tienes cuenta?
            <a href="registro.php" class="auth-footer-link">Regístrate aquí</a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
