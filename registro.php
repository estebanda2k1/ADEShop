<?php
require 'config.php';

// Variables para mensajes
$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar datos
    $nombres = trim($_POST['nombres'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
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
                // Crear username basado en email (parte antes del @)
                $username = explode('@', $email)[0];
                
                // Verificar si el username ya existe y modificarlo si es necesario
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    // Agregar timestamp al username para hacerlo único
                    $username = $username . time();
                }
                
                // Hash de la contraseña
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar usuario en la base de datos
                try {
                    $stmt = $pdo->prepare('INSERT INTO users (nombres, apellidos, email, cedula, username, password, is_admin) VALUES (?, ?, ?, ?, ?, ?, 0)');
                    $stmt->execute([$nombres, $apellidos, $email, $cedula, $username, $hashed_password]);
                    
                    $success = 'Registro exitoso! Ya puedes iniciar sesión.';
                    
                    // Limpiar el formulario
                    $nombres = $apellidos = $email = $cedula = '';
                } catch (PDOException $e) {
                    $error = 'Error al registrar el usuario. Por favor intenta nuevamente.';
                }
            }
        }
    }
}

require 'templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4">Registro de Usuario</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Éxito:</strong> <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-primary">Ir a la tienda</a>
                <a href="admin/login.php" class="btn btn-secondary">Iniciar sesión</a>
            </div>
        <?php endif; ?>
        
        <?php if (empty($success)): ?>
        <div class="card shadow">
            <div class="card-body p-4">
                <form method="post" id="registroForm" novalidate>
                    <div class="mb-3">
                        <label for="nombres" class="form-label">Nombres <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="nombres" 
                               name="nombres" 
                               value="<?php echo htmlspecialchars($nombres ?? ''); ?>"
                               required
                               pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                               maxlength="100">
                        <div class="invalid-feedback">
                            Por favor ingresa tu nombre (solo letras).
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="apellidos" 
                               name="apellidos" 
                               value="<?php echo htmlspecialchars($apellidos ?? ''); ?>"
                               required
                               pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                               maxlength="100">
                        <div class="invalid-feedback">
                            Por favor ingresa tus apellidos (solo letras).
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
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
                    
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
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
                            Por favor ingresa una cédula válida (mínimo 6 dígitos, solo números).
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
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
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
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
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Registrarse</button>
                        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <p class="text-center mb-0">
                    ¿Ya tienes una cuenta? <a href="admin/login.php">Inicia sesión aquí</a>
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Validación personalizada del formulario
(function() {
    'use strict';
    
    const form = document.getElementById('registroForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    // Validar que las contraseñas coincidan
    function validatePasswords() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePasswords);
    confirmPassword.addEventListener('keyup', validatePasswords);
    
    // Validación al enviar el formulario
    form.addEventListener('submit', function(event) {
        validatePasswords();
        
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
    
    // Solo permitir números en el campo de cédula
    document.getElementById('cedula').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
    
    // Solo permitir letras y espacios en nombres y apellidos
    document.getElementById('nombres').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
    });
    
    document.getElementById('apellidos').addEventListener('input', function(e) {
        this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
    });
})();
</script>

<style>
.card {
    border: none;
}
.form-label {
    font-weight: 500;
}
.text-danger {
    color: #dc3545;
}
</style>

<?php require 'templates/footer.php'; ?>
