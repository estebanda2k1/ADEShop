<?php
// Iniciar la sesión y la conexión a la base de datos
require_once 'config.php';

// Inicializar un mensaje de error si existe en la sesión
$error_message = '';
if (isset($_SESSION['recovery_error'])) {
    $error_message = $_SESSION['recovery_error'];
    unset($_SESSION['recovery_error']); // Limpiar el error después de mostrarlo
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
                        <h2 class="card-title text-center fw-bold">Recuperar Contraseña</h2>
                        <p class="text-muted">Ingresa el correo asociado a tu cuenta.</p>
                    </div>
                    
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form action="verificarRecuperacion.php" method="POST">
                        <div class="mb-4">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" id="email" name="email" class="form-control form-control-lg" required placeholder="tu.correo@ejemplo.com">
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">Buscar Cuenta</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="iniciarSesion.php" class="text-secondary small">Volver al inicio de sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

include 'templates/footer.php'; 
?>