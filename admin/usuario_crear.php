<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombres)) {
        $error = 'El nombre es requerido';
    } elseif (empty($apellidos)) {
        $error = 'Los apellidos son requeridos';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido';
    } elseif (empty($cedula)) {
        $error = 'La cédula es requerida';
    } elseif (!preg_match('/^[0-9]{6,20}$/', $cedula)) {
        $error = 'La cédula debe contener solo números (mínimo 6 dígitos)';
    } elseif (empty($password)) {
        $error = 'La contraseña es requerida';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    } elseif ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    } else {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Este correo electrónico ya está registrado';
        } else {
            // Verificar si la cédula ya existe
            $stmt = $pdo->prepare('SELECT id FROM users WHERE cedula = ?');
            $stmt->execute([$cedula]);
            if ($stmt->fetch()) {
                $error = 'Esta cédula ya está registrada';
            } else {
                // Crear username basado en email
                $username = explode('@', $email)[0];
                
                // Verificar si el username ya existe
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $username = $username . time();
                }
                
                // Hash de la contraseña
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar usuario
                try {
                    $stmt = $pdo->prepare('INSERT INTO users (nombres, apellidos, email, cedula, username, password, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$nombres, $apellidos, $email, $cedula, $username, $hashed_password, $is_admin]);
                    
                    $_SESSION['message'] = 'Usuario creado exitosamente';
                    $_SESSION['message_type'] = 'success';
                    header('Location: usuarios.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Error al crear el usuario. Por favor intenta nuevamente.';
                }
            }
        }
    }
}

require '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="bi bi-person-plus"></i> Crear Nuevo Usuario</h1>
                <p class="mb-0">Agrega un nuevo cliente a la plataforma</p>
            </div>
            <a href="usuarios.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body p-4">
                    <form method="post" id="formCrearUsuario" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nombres" class="form-label">
                                    Nombres <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="nombres" 
                                       name="nombres" 
                                       value="<?php echo htmlspecialchars($nombres ?? ''); ?>"
                                       required
                                       pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                                       maxlength="100">
                                <div class="invalid-feedback">
                                    Por favor ingresa el nombre (solo letras).
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="apellidos" class="form-label">
                                    Apellidos <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="apellidos" 
                                       name="apellidos" 
                                       value="<?php echo htmlspecialchars($apellidos ?? ''); ?>"
                                       required
                                       pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                                       maxlength="100">
                                <div class="invalid-feedback">
                                    Por favor ingresa los apellidos (solo letras).
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                       required
                                       maxlength="255">
                                <div class="invalid-feedback">
                                    Por favor ingresa un correo electrónico válido.
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="cedula" class="form-label">
                                    Cédula <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="cedula" 
                                       name="cedula" 
                                       value="<?php echo htmlspecialchars($cedula ?? ''); ?>"
                                       required
                                       pattern="[0-9]{6,20}"
                                       maxlength="20"
                                       placeholder="Solo números">
                                <div class="invalid-feedback">
                                    Por favor ingresa una cédula válida (mínimo 6 dígitos).
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required
                                       minlength="6"
                                       maxlength="255">
                                <div class="invalid-feedback">
                                    La contraseña debe tener al menos 6 caracteres.
                                </div>
                                <small class="form-text text-muted">Mínimo 6 caracteres</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">
                                    Confirmar Contraseña <span class="text-danger">*</span>
                                </label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required
                                       minlength="6"
                                       maxlength="255">
                                <div class="invalid-feedback">
                                    Las contraseñas deben coincidir.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_admin" 
                                       name="is_admin">
                                <label class="form-check-label" for="is_admin">
                                    <i class="bi bi-shield-check"></i> Otorgar privilegios de administrador
                                </label>
                                <small class="form-text text-muted d-block">
                                    Los administradores tienen acceso completo al panel de control
                                </small>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="usuarios.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Crear Usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    'use strict';
    
    const form = document.getElementById('formCrearUsuario');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePasswords() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePasswords);
    confirmPassword.addEventListener('keyup', validatePasswords);
    
    form.addEventListener('submit', function(event) {
        validatePasswords();
        
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
    
    document.getElementById('cedula').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    document.getElementById('nombres').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
    });
    
    document.getElementById('apellidos').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
    });
})();
</script>
</body>
</html>
