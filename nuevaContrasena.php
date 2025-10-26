<?php
require_once 'config.php'; 

if (!isset($_SESSION['recovery_email']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: recuperarContrasena.php');
}

$email = $_SESSION['recovery_email'];
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validaciones
if (empty($password) || empty($confirm_password)) {
    $_SESSION['change_error'] = "Todos los campos de contraseña son obligatorios.";
    header('Location: cambiarContrasena.php');
}

if ($password !== $confirm_password) {
    $_SESSION['change_error'] = "Las contraseñas no coinciden. Intenta de nuevo.";
    header('Location: cambiarContrasena.php');
}

if (strlen($password) < 6) {
    $_SESSION['change_error'] = "La contraseña debe tener al menos 6 caracteres.";
    header('Location: cambiarContrasena.php');
}

// Hash de la Contraseña
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

//Actualizar la contraseña en la base de datos
try {
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
    $stmt->execute([
        'password' => $hashed_password,
        'email' => $email
    ]);
 
    // Eliminamos las variables de sesión usadas para la recuperación (seguridad)
    unset($_SESSION['recovery_email']);
    unset($_SESSION['recovery_success']);
    
    // ** CAMBIO CLAVE AQUÍ: Establecemos bandera de éxito y redirigimos a la pantalla de confirmación **
    $_SESSION['password_reset_success'] = true;

    // Redirigir a la nueva pantalla de confirmación HTML
    header('Location: confirmacionCambio.php'); 
    exit;

} catch (PDOException $e) {
    // Manejo de error de base de datos
    error_log("Error de BD al cambiar contraseña: " . $e->getMessage());
    $_SESSION['change_error'] = "⚠️ Error interno del sistema. No se pudo actualizar la contraseña.";
    header('Location: cambiarContrasena.php');
    exit;
}
?>