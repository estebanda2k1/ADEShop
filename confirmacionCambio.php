<?php
// Pantalla de Confirmación (HTML/PHP)
require_once 'config.php'; 

// 1. Verificar si el proceso fue exitoso. Si no, redirigir al inicio de la recuperación.
if (!isset($_SESSION['password_reset_success']) || $_SESSION['password_reset_success'] !== true) {
    header('Location: recuperarContrasena.php');
    exit;
}

// Limpiamos la bandera de éxito después de verificarla
unset($_SESSION['password_reset_success']);

// Incluimos los templates
include 'templates/header.php'; 
?>

<div class="container mt-5" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6"> 
            <div class="card text-center shadow-lg border-0 p-5">
                <div class="mb-4">
                    <img src="imagenes/logo.png" alt="Logo ADESHOP" style="max-height: 80px;" class="mb-3">
                </div>
                
                <h2 class="card-title fw-bold text-success mb-3">¡Contraseña Restablecida con Éxito!</h2>
                
                <p class="text-muted mb-5">
                    Tu nueva contraseña ha sido actualizada exitosamente. 
                    Haz clic en el botón de abajo para continuar e iniciar sesión con tu nueva contraseña.
                </p>

                <a href="iniciarSesion.php" class="btn btn-primary btn-lg w-100">
                    Continuar e Iniciar Sesión
                </a>
                
            </div>
        </div>
    </div>
</div>

<?php
include 'templates/footer.php'; 
?>