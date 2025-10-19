<?php
require_once 'config.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: recuperarContrasena.php');
}

// Recibir y sanitizar el correo
$email = $_POST['email'] ?? '';
$email = trim($email);

//Validar formato básico del correo
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['recovery_error'] = "El formato del correo electrónico es inválido.";
    header('Location: recuperarContrasena.php');
}

// Para verificar si existe el correo
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    
    if ($stmt->rowCount() === 1) {
        $_SESSION['recovery_email'] = $email;
        header('Location: cambiarContrasena.php'); 
    } else {
        // CORREO NO REGISTRADO: Saltar un error
        $_SESSION['recovery_error'] = "❌ Correo no registrado. Verifica la dirección e intenta de nuevo.";
        header('Location: recuperarContrasena.php');
    }

} catch (PDOException $e) {
    // Error en la conexión o consulta
    error_log("Error de BD en recuperación: " . $e->getMessage());
    $_SESSION['recovery_error'] = "⚠️ Ocurrió un error en el sistema. Intenta más tarde.";
    header('Location: recuperarContrasena.php');
}
?>