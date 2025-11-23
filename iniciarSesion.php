<?php
require_once 'config.php';
require_once 'cart_helper.php';

// Inicializar variables de error
$email_error = '';
$password_error = '';
$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validación de campos vacíos
    if (empty($email)) {
        $email_error = "Debes llenar este campo.";
    }
    if (empty($password)) {
        $password_error = "Debes llenar este campo.";
    }

    // Solo si no hay errores de campos
    if (empty($email_error) && empty($password_error)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombres'] . ' ' . $user['apellidos'];
            $_SESSION['is_admin'] = intval($user['is_admin']);

            // Cargar carrito desde la base de datos
            $_SESSION['cart'] = loadCartFromDatabase($pdo, $user['id']);

            if ($user['is_admin']) {
                header("Location: dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $login_error = "Correo o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #000;
        }

        .container-fluid {
            height: 100%;
        }

        .left-side {
            background: url('imagenes/img-is.png') no-repeat center center;
            background-size: cover;
            background-position: center top;
            filter: brightness(0.5);
        }

        .right-side {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            color: #000;
        }

        .login-box {
            width: 100%;
            max-width: 380px;
        }

        .login-box img {
            width: 120px;
            display: block;
            margin: 0 auto 20px;
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #000;
        }

        .form-label {
            color: #000;
        }

        .form-control {
            color: #000;
        }

        .btn-primary {
            background-color: #6c757d;
            border: none;
            color: #fff;
            transition: background-color 0.2s;
        }

        .btn-primary:hover {
            background-color: #495057;
        }

        .links {
            text-align: center;
            margin-top: 10px;
        }

        .links a {
            color: #000;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .links a:hover {
            color: #6c757d;
            text-decoration: underline;
        }

        .error {
            color: #dc3545;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .left-side {
                display: none;
            }
            .right-side {
                width: 100%;
                padding: 30px;
            }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row h-100">
        <!-- Lado izquierdo con imagen -->
        <div class="col-md-6 left-side"></div>

        <!-- Lado derecho con formulario -->
        <div class="col-md-6 right-side">
            <div class="login-box">
                <img src="imagenes/logo.png" alt="Logo">
                <h2>Iniciar Sesión</h2>

                <?php if(!empty($login_error)): ?>
                    <div class="error text-center mb-3"><?= $login_error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" name="email" placeholder="ejemplo@correo.com" value="<?= htmlspecialchars($email ?? '') ?>">
                        <?php if(!empty($email_error)): ?>
                            <div class="error"><?= $email_error ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" placeholder="Tu contraseña">
                        <?php if(!empty($password_error)): ?>
                            <div class="error"><?= $password_error ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </form>

                <div class="links">
                    <a href="recuperarContrasena.php">¿Olvidaste tu contraseña?</a><br>
                    <a href="registro.php">¿No tienes cuenta? Regístrate</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
