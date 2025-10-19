<?php
require_once 'config.php';

// Verificar si hay un correo en sesión
if (!isset($_SESSION['recovery_email'])) {
    header('Location: recuperarContrasena.php');
    exit;
}

$email = $_SESSION['recovery_email'];

// Manejar mensajes de éxito/error
$success_message = '';
if (isset($_SESSION['recovery_success'])) {
    $success_message = $_SESSION['recovery_success'];
    unset($_SESSION['recovery_success']); 
}

$error_message = '';
if (isset($_SESSION['change_error'])) {
    $error_message = $_SESSION['change_error'];
    unset($_SESSION['change_error']); 
}

include 'templates/header.php'; 
?>

<div class="container mt-5" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6"> 
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <img src="imagenes/logo.png" alt="Logo ADESHOP" style="max-height: 90px;" class="mb-3">
                        <h2 class="card-title text-center fw-bold">Paso 2: Restablecer Contraseña</h2>
                    </div>

                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success text-center" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <p class="text-center text-muted">Ingresa tu nueva contraseña para la cuenta: <strong><?php echo htmlspecialchars($email); ?></strong>.</p>

                    <form action="nuevaContrasena.php" method="POST">
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input type="password" id="password" name="password" class="form-control form-control-lg" required autocomplete="new-password" minlength="6">
                            <div class="form-text">Mínimo 6 caracteres.</div>
                        </div>
                         <div class="mb-4">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control form-control-lg" required autocomplete="new-password">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">Restablecer </button>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'templates/footer.php'; 
?>