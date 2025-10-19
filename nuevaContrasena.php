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
    // Usamos la variable $pdo de config.php para la conexión.
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
    $stmt->execute([
        'password' => $hashed_password,
        'email' => $email
    ]);

    unset($_SESSION['recovery_email']);
    unset($_SESSION['recovery_success']);
    
    $_SESSION['login_message'] = "🎉 ¡Contraseña restablecida con éxito! Ya puedes iniciar sesión.";

    header('Location: iniciarSesion.php');

} catch (PDOException $e) {
    // Manejo de error de base de datos
    error_log("Error de BD al cambiar contraseña: " . $e->getMessage());
    $_SESSION['change_error'] = "⚠️ Error interno del sistema. No se pudo actualizar la contraseña.";
    header('Location: cambiarContrasena.php');
}
?>