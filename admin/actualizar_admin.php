<?php
/**
 * Script para crear/actualizar el usuario administrador por defecto
 * Usuario: admin@admin.com
 * Contraseña: admin123
 */

require_once '../config.php';

try {
    // Datos del administrador
    $email = 'admin@admin.com';
    $username = 'admin';
    $password = 'admin123';
    $nombres = 'Administrador';
    $apellidos = 'Sistema';
    $cedula = '9999999999';
    
    // Hash de la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Verificar si ya existe el usuario con este email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $usuario_existente = $stmt->fetch();
    
    if ($usuario_existente) {
        // Actualizar usuario existente
        $stmt = $pdo->prepare('UPDATE users SET nombres = ?, apellidos = ?, username = ?, password = ?, is_admin = 1, cedula = ? WHERE email = ?');
        $stmt->execute([$nombres, $apellidos, $username, $hashed_password, $cedula, $email]);
        $mensaje = "Usuario administrador actualizado correctamente";
    } else {
        // Verificar si existe un usuario con el username 'admin'
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $usuario_username = $stmt->fetch();
        
        if ($usuario_username) {
            // Actualizar el usuario existente con username 'admin'
            $stmt = $pdo->prepare('UPDATE users SET nombres = ?, apellidos = ?, email = ?, password = ?, is_admin = 1, cedula = ? WHERE username = ?');
            $stmt->execute([$nombres, $apellidos, $email, $hashed_password, $cedula, $username]);
            $mensaje = "Usuario administrador actualizado correctamente";
        } else {
            // Crear nuevo usuario administrador
            $stmt = $pdo->prepare('INSERT INTO users (nombres, apellidos, email, cedula, username, password, is_admin) VALUES (?, ?, ?, ?, ?, ?, 1)');
            $stmt->execute([$nombres, $apellidos, $email, $cedula, $username, $hashed_password]);
            $mensaje = "Usuario administrador creado correctamente";
        }
    }
    
    $success = true;
    
} catch (PDOException $e) {
    $success = false;
    $mensaje = "Error al procesar el usuario administrador: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Administrador - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-<?php echo $success ? 'success' : 'danger'; ?> text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-<?php echo $success ? 'check-circle' : 'x-circle'; ?>"></i>
                            <?php echo $success ? 'Éxito' : 'Error'; ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                            <?php echo htmlspecialchars($mensaje); ?>
                        </div>
                        
                        <?php if ($success): ?>
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Credenciales de Administrador</h5>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                                    <p class="mb-1"><strong>Usuario:</strong> <?php echo htmlspecialchars($username); ?></p>
                                    <p class="mb-0"><strong>Contraseña:</strong> <?php echo htmlspecialchars($password); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 mt-3">
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="bi bi-speedometer2"></i> Ir al Dashboard
                            </a>
                            <a href="../index.php" class="btn btn-outline-secondary">
                                <i class="bi bi-house"></i> Ir a la tienda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
