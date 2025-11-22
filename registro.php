<?php
require 'config.php';

// Función para validar cédula ecuatoriana
function validarCedulaEcuatoriana($cedula) {
    // 1. Debe tener 10 dígitos
    if (!preg_match('/^[0-9]{10}$/', $cedula)) {
        return false;
    }

    // 2. Validar código de provincia (01-24 o 30 para extranjeros)
    $provincia = intval(substr($cedula, 0, 2));
    if (!(($provincia >= 1 && $provincia <= 24) || $provincia == 30)) {
        return false;
    }

    // 3. Validar tercer dígito (0-5)
    $tercerDigito = intval(substr($cedula, 2, 1));
    if ($tercerDigito < 0 || $tercerDigito > 5) {
        return false;
    }

    // 4. Validar dígito verificador (módulo 10)
    $digitoVerificador = intval(substr($cedula, -1));
    $total = 0;

    // Recorrer los primeros 9 dígitos
    for ($i = 0; $i < 9; $i++) {
        $num = intval($cedula[$i]);

        // Posiciones impares (0,2,4,6,8) -> multiplicar por 2
        if ($i % 2 == 0) {
            $num *= 2;
            if ($num > 9) $num -= 9;
        }

        $total += $num;
    }

    // Calcular dígito verificador real
    $decenaSuperior = ceil($total / 10) * 10;
    $digitoCalculado = $decenaSuperior - $total;

    if ($digitoCalculado == 10) $digitoCalculado = 0;

    return $digitoCalculado == $digitoVerificador;
}

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
    } elseif (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombres)) {
        $error = 'El nombre solo puede contener letras y espacios';
    } elseif (empty($apellidos)) {
        $error = 'Los apellidos son requeridos';
    } elseif (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $apellidos)) {
        $error = 'Los apellidos solo pueden contener letras y espacios';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido';
    } elseif (empty($cedula)) {
        $error = 'La cédula es requerida';
    } elseif (!preg_match('/^[0-9]{10}$/', $cedula)) {
        $error = 'La cédula debe contener exactamente 10 dígitos numéricos';
    } elseif (!validarCedulaEcuatoriana($cedula)) {
        $error = 'La cédula ecuatoriana no es válida.';
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
                               maxlength="100"
                               placeholder="Ej: Juan Carlos">
                        <small class="form-text text-muted">Solo se permiten letras y espacios</small>
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
                               maxlength="100"
                               placeholder="Ej: Pérez García">
                        <small class="form-text text-muted">Solo se permiten letras y espacios</small>
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
                               maxlength="255"
                               placeholder="ejemplo@correo.com">
                        <small class="form-text text-muted">Formato válido: usuario@dominio.com</small>
                        <div class="invalid-feedback">
                            Por favor ingresa un correo electrónico válido.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cedula" class="form-label">Cédula Ecuatoriana <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control" 
                               id="cedula" 
                               name="cedula" 
                               value="<?php echo htmlspecialchars($cedula ?? ''); ?>"
                               required
                               pattern="[0-9]{10}"
                               maxlength="10"
                               minlength="10"
                               placeholder="1234567890">
                        <small class="form-text text-muted">Debe tener exactamente 10 dígitos numéricos.</small>
                        <div class="invalid-feedback">
                            Por favor ingresa una cédula ecuatoriana válida (10 dígitos).
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
                               maxlength="255"
                               placeholder="Mínimo 6 caracteres">
                        <small class="form-text text-muted">Debe contener al menos 6 caracteres. Puede incluir letras, números y símbolos</small>
                        <div class="invalid-feedback">
                            La contraseña debe tener al menos 6 caracteres.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required
                               minlength="6"
                               maxlength="255"
                               placeholder="Repite tu contraseña">
                        <small class="form-text text-muted">Debe coincidir con la contraseña anterior</small>
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
// Función para validar cédula ecuatoriana en JavaScript
function validarCedulaEcuatorianaJS(cedula) {
    // 1. Debe tener 10 dígitos
    if (!/^[0-9]{10}$/.test(cedula)) {
        return false;
    }

    // 2. Validar código de provincia (01-24 o 30 para extranjeros)
    const provincia = parseInt(cedula.substring(0, 2));
    if (!((provincia >= 1 && provincia <= 24) || provincia === 30)) {
        return false;
    }

    // 3. Validar tercer dígito (0-5)
    const tercerDigito = parseInt(cedula.charAt(2));
    if (tercerDigito < 0 || tercerDigito > 5) {
        return false;
    }

    // 4. Validar dígito verificador (módulo 10)
    const digitoVerificador = parseInt(cedula.charAt(9));
    let total = 0;

    // Recorrer los primeros 9 dígitos
    for (let i = 0; i < 9; i++) {
        let num = parseInt(cedula.charAt(i));

        // Posiciones impares (0,2,4,6,8) -> multiplicar por 2
        if (i % 2 === 0) {
            num *= 2;
            if (num > 9) num -= 9;
        }

        total += num;
    }

    // Calcular dígito verificador real
    const decenaSuperior = Math.ceil(total / 10) * 10;
    let digitoCalculado = decenaSuperior - total;

    if (digitoCalculado === 10) digitoCalculado = 0;

    return digitoCalculado === digitoVerificador;
}

// Validación personalizada del formulario
(function() {
    'use strict';
    
    const form = document.getElementById('registroForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const cedulaInput = document.getElementById('cedula');
    
    // Validar que las contraseñas coincidan
    function validatePasswords() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    // Validar cédula ecuatoriana
    function validateCedula() {
        const cedula = cedulaInput.value;
        if (cedula.length === 10) {
            if (!validarCedulaEcuatorianaJS(cedula)) {
                cedulaInput.setCustomValidity('La cédula ecuatoriana no es válida');
            } else {
                cedulaInput.setCustomValidity('');
            }
        }
    }
    
    password.addEventListener('change', validatePasswords);
    confirmPassword.addEventListener('keyup', validatePasswords);
    cedulaInput.addEventListener('input', validateCedula);
    cedulaInput.addEventListener('blur', validateCedula);
    
    // Validación al enviar el formulario
    form.addEventListener('submit', function(event) {
        validatePasswords();
        validateCedula();
        
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
    
    // Solo permitir números en el campo de cédula
    cedulaInput.addEventListener('input', function(e) {
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
