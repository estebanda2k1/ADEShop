<?php
// Conexión directa a la base de datos sin usar config.php para evitar redirección
$DB_HOST = '127.0.0.1';
$DB_NAME = 'adeshop';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    die("Error de conexión: " . htmlspecialchars($e->getMessage()));
}

// Función para validar cédula ecuatoriana
function validarCedulaEcuatoriana($cedula) {
    // Verificar que tenga 10 dígitos
    if (strlen($cedula) != 10) {
        return false;
    }
    
    // Verificar que todos sean números
    if (!ctype_digit($cedula)) {
        return false;
    }
    
    // Los dos primeros dígitos deben estar entre 01 y 24 (provincias de Ecuador)
    $provincia = substr($cedula, 0, 2);
    if ($provincia < 1 || $provincia > 24) {
        return false;
    }
    
    // El tercer dígito debe ser menor a 6 (para cédulas de personas naturales)
    $tercerDigito = substr($cedula, 2, 1);
    if ($tercerDigito > 5) {
        return false;
    }
    
    // Extraer los primeros 9 dígitos y el dígito verificador
    $digitosIniciales = substr($cedula, 0, 9);
    $digitoVerificador = substr($cedula, 9, 1);
    
    // Coeficientes para la validación
    $coeficientes = array(2, 1, 2, 1, 2, 1, 2, 1, 2);
    $suma = 0;
    
    // Aplicar el algoritmo de validación
    for ($i = 0; $i < 9; $i++) {
        $valor = $digitosIniciales[$i] * $coeficientes[$i];
        if ($valor > 9) {
            $valor -= 9;
        }
        $suma += $valor;
    }
    
    // Calcular el dígito verificador esperado
    $residuo = $suma % 10;
    $resultado = $residuo == 0 ? 0 : 10 - $residuo;
    
    // Comparar con el dígito verificador de la cédula
    return $resultado == $digitoVerificador;
}

// Verificar si ya existe un administrador
$stmt = $pdo->prepare('SELECT COUNT(*) as count FROM users WHERE is_admin = 1');
$stmt->execute();
$result = $stmt->fetch();

if ($result['count'] > 0) {
    // Ya existe un administrador, redirigir al login
    header('Location: iniciarSesion.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validaciones individuales
    if (empty($username)) {
        $errors['username'] = 'El campo usuario es obligatorio.';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'El usuario debe tener al menos 3 caracteres.';
    }
    
    if (empty($email)) {
        $errors['email'] = 'El campo correo electrónico es obligatorio.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'El correo electrónico no tiene un formato válido.';
    }
    
    if (empty($cedula)) {
        $errors['cedula'] = 'El campo cédula es obligatorio.';
    } elseif (!preg_match('/^[0-9]{10}$/', $cedula)) {
        $errors['cedula'] = 'La cédula debe contener exactamente 10 dígitos numéricos.';
    } elseif (!validarCedulaEcuatoriana($cedula)) {
        $errors['cedula'] = 'Ingrese una cédula válida ecuatoriana.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'El campo contraseña es obligatorio.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    
    if (empty($password_confirm)) {
        $errors['password_confirm'] = 'Debe confirmar la contraseña.';
    } elseif ($password !== $password_confirm) {
        $errors['password_confirm'] = 'Las contraseñas no coinciden.';
    }
    
    // Si no hay errores, proceder a crear el usuario
    if (empty($errors)) {
        // Verificar si el usuario, email o cédula ya existen
        $stmt = $pdo->prepare('SELECT username, email, cedula FROM users WHERE username = ? OR email = ? OR cedula = ?');
        $stmt->execute([$username, $email, $cedula]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            if ($existing['username'] === $username) {
                $errors['username'] = 'Este nombre de usuario ya está registrado.';
            }
            if ($existing['email'] === $email) {
                $errors['email'] = 'Este correo electrónico ya está registrado.';
            }
            if ($existing['cedula'] === $cedula) {
                $errors['cedula'] = 'Esta cédula ya está registrada.';
            }
        } else {
            try {
                // Crear el administrador
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (nombres, apellidos, email, cedula, username, password, is_admin) VALUES (?, ?, ?, ?, ?, ?, 1)');
                $stmt->execute([
                    'Administrador',
                    'Principal',
                    $email,
                    $cedula,
                    $username,
                    $hashed_password
                ]);
                
                $success = true;
            } catch (Exception $e) {
                $errors['general'] = 'Error al crear el administrador: ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Wizard - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .setup-container {
            max-width: 500px;
            width: 100%;
            padding: 20px;
        }
        
        .setup-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .setup-header .icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .setup-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }
        
        .setup-header p {
            color: #666;
            font-size: 0.95rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-create {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: transform 0.2s ease;
        }
        
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .success-animation {
            text-align: center;
        }
        
        .success-animation .icon {
            font-size: 5rem;
            color: #28a745;
            animation: bounce 0.5s ease;
        }
        
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .valid-feedback, .invalid-feedback {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .valid-feedback {
            color: #28a745;
        }
        
        .form-control.is-valid {
            border-color: #28a745;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            padding-right: calc(1.5em + 0.75rem);
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
            padding-right: calc(1.5em + 0.75rem);
        }
        
        .form-text {
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        
        #password-strength {
            font-weight: 600;
            margin-right: 8px;
        }
    </style>
</head>
<body>

<div class="setup-container">
    <div class="setup-card">
        <?php if ($success): ?>
            <div class="success-animation">
                <i class="bi bi-check-circle-fill icon"></i>
                <h2 class="mb-3">¡Configuración Completada!</h2>
                <p class="text-muted mb-4">El administrador ha sido creado exitosamente.</p>
                <a href="iniciarSesion.php" class="btn btn-success btn-lg w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Ir a Iniciar Sesión
                </a>
            </div>
        <?php else: ?>
            <div class="setup-header">
                <i class="bi bi-gear-fill icon"></i>
                <h1>Configuración Inicial</h1>
                <p>Crea el primer administrador del sistema</p>
            </div>
            
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Error:</strong> <?php echo htmlspecialchars($errors['general']); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">
                        <i class="bi bi-person-fill"></i> Usuario <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" 
                        id="username" 
                        name="username" 
                        value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                        required
                        placeholder="Ingrese el nombre de usuario"
                    >
                    <?php if (isset($errors['username'])): ?>
                        <div class="invalid-feedback d-block">
                            <?php echo htmlspecialchars($errors['username']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope-fill"></i> Correo Electrónico <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="email" 
                        class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required
                        placeholder="admin@ejemplo.com"
                    >
                    <div class="invalid-feedback" id="email-feedback">
                        <?php echo isset($errors['email']) ? htmlspecialchars($errors['email']) : 'Por favor ingrese un correo electrónico válido.'; ?>
                    </div>
                    <small class="form-text text-muted" id="email-hint">Ingrese un correo electrónico válido.</small>
                </div>
                
                <div class="mb-3">
                    <label for="cedula" class="form-label">
                        <i class="bi bi-card-text"></i> Cédula <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control <?php echo isset($errors['cedula']) ? 'is-invalid' : ''; ?>" 
                        id="cedula" 
                        name="cedula" 
                        value="<?php echo htmlspecialchars($_POST['cedula'] ?? ''); ?>"
                        required
                        maxlength="10"
                        placeholder="Ingrese 10 dígitos"
                    >
                    <div class="invalid-feedback" id="cedula-feedback">
                        <?php echo isset($errors['cedula']) ? htmlspecialchars($errors['cedula']) : 'La cédula debe ser válida.'; ?>
                    </div>
                    <small class="form-text text-muted" id="cedula-hint">Ingrese una cédula ecuatoriana válida de 10 dígitos.</small>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock-fill"></i> Contraseña <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="password" 
                        class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                        id="password" 
                        name="password" 
                        required
                        minlength="6"
                        placeholder="Mínimo 6 caracteres"
                    >
                    <div class="invalid-feedback" id="password-feedback">
                        <?php echo isset($errors['password']) ? htmlspecialchars($errors['password']) : 'La contraseña debe tener al menos 6 caracteres.'; ?>
                    </div>
                    <small class="form-text text-muted" id="password-hint">
                        <span id="password-strength"></span>
                        <span id="password-length">Mínimo 6 caracteres</span>
                    </small>
                </div>
                
                <div class="mb-4">
                    <label for="password_confirm" class="form-label">
                        <i class="bi bi-lock-fill"></i> Repetir Contraseña <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="password" 
                        class="form-control <?php echo isset($errors['password_confirm']) ? 'is-invalid' : ''; ?>" 
                        id="password_confirm" 
                        name="password_confirm" 
                        required
                        minlength="6"
                        placeholder="Repita la contraseña"
                    >
                    <div class="invalid-feedback" id="password-confirm-feedback">
                        <?php echo isset($errors['password_confirm']) ? htmlspecialchars($errors['password_confirm']) : 'Las contraseñas deben coincidir.'; ?>
                    </div>
                    <small class="form-text text-muted" id="password-confirm-hint">Las contraseñas deben ser iguales.</small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-create w-100">
                    <i class="bi bi-person-plus-fill"></i> Crear Administrador
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para validar cédula ecuatoriana (JavaScript)
function validarCedulaEcuatoriana(cedula) {
    if (cedula.length !== 10) return false;
    if (!/^\d+$/.test(cedula)) return false;
    
    const provincia = parseInt(cedula.substring(0, 2));
    if (provincia < 1 || provincia > 24) return false;
    
    const tercerDigito = parseInt(cedula.charAt(2));
    if (tercerDigito > 5) return false;
    
    const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
    let suma = 0;
    
    for (let i = 0; i < 9; i++) {
        let valor = parseInt(cedula.charAt(i)) * coeficientes[i];
        if (valor > 9) valor -= 9;
        suma += valor;
    }
    
    const residuo = suma % 10;
    const resultado = residuo === 0 ? 0 : 10 - residuo;
    const digitoVerificador = parseInt(cedula.charAt(9));
    
    return resultado === digitoVerificador;
}

// Validación de email en tiempo real
document.getElementById('email')?.addEventListener('input', function() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const hint = document.getElementById('email-hint');
    const feedback = document.getElementById('email-feedback');
    
    if (this.value.length === 0) {
        this.classList.remove('is-invalid', 'is-valid');
        hint.style.display = 'block';
    } else if (emailRegex.test(this.value)) {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        hint.style.display = 'none';
        feedback.textContent = '✓ Correo válido';
        feedback.classList.remove('invalid-feedback');
        feedback.classList.add('valid-feedback', 'd-block');
    } else {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
        hint.style.display = 'none';
        feedback.textContent = 'Por favor ingrese un correo electrónico válido';
        feedback.classList.remove('valid-feedback');
        feedback.classList.add('invalid-feedback', 'd-block');
    }
});

// Validación de cédula en tiempo real (solo números)
document.getElementById('cedula')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    const hint = document.getElementById('cedula-hint');
    const feedback = document.getElementById('cedula-feedback');
    
    if (this.value.length === 0) {
        this.classList.remove('is-invalid', 'is-valid');
        hint.style.display = 'block';
    } else if (this.value.length !== 10) {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
        hint.style.display = 'none';
        feedback.textContent = 'La cédula debe tener exactamente 10 dígitos';
        feedback.classList.remove('valid-feedback');
        feedback.classList.add('invalid-feedback', 'd-block');
    } else if (!validarCedulaEcuatoriana(this.value)) {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
        hint.style.display = 'none';
        feedback.textContent = 'La cédula ingresada no es válida';
        feedback.classList.remove('valid-feedback');
        feedback.classList.add('invalid-feedback', 'd-block');
    } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        hint.style.display = 'none';
        feedback.textContent = '✓ Cédula válida';
        feedback.classList.remove('invalid-feedback');
        feedback.classList.add('valid-feedback', 'd-block');
    }
});

// Validación de contraseña en tiempo real
document.getElementById('password')?.addEventListener('input', function() {
    const password = this.value;
    const hint = document.getElementById('password-hint');
    const feedback = document.getElementById('password-feedback');
    const strengthIndicator = document.getElementById('password-strength');
    const lengthIndicator = document.getElementById('password-length');
    
    if (password.length === 0) {
        this.classList.remove('is-invalid', 'is-valid');
        strengthIndicator.textContent = '';
        lengthIndicator.textContent = 'Mínimo 6 caracteres';
    } else if (password.length < 6) {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
        feedback.textContent = 'La contraseña debe tener al menos 6 caracteres';
        feedback.classList.remove('valid-feedback');
        feedback.classList.add('invalid-feedback', 'd-block');
        strengthIndicator.textContent = '';
        lengthIndicator.textContent = `Faltan ${6 - password.length} caracteres`;
        lengthIndicator.style.color = '#dc3545';
    } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        feedback.textContent = '✓ Contraseña válida';
        feedback.classList.remove('invalid-feedback');
        feedback.classList.add('valid-feedback', 'd-block');
        
        // Indicador de fortaleza
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        lengthIndicator.textContent = '';
        if (strength <= 1) {
            strengthIndicator.textContent = '🔴 Débil';
            strengthIndicator.style.color = '#dc3545';
        } else if (strength === 2) {
            strengthIndicator.textContent = '🟡 Media';
            strengthIndicator.style.color = '#ffc107';
        } else {
            strengthIndicator.textContent = '🟢 Fuerte';
            strengthIndicator.style.color = '#28a745';
        }
    }
    
    // Re-validar confirmación de contraseña
    const confirmPassword = document.getElementById('password_confirm');
    if (confirmPassword.value.length > 0) {
        confirmPassword.dispatchEvent(new Event('input'));
    }
});

// Validación de contraseñas en tiempo real
document.getElementById('password_confirm')?.addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const passwordConfirm = this.value;
    const hint = document.getElementById('password-confirm-hint');
    const feedback = document.getElementById('password-confirm-feedback');
    
    if (passwordConfirm.length === 0) {
        this.classList.remove('is-invalid', 'is-valid');
        hint.style.display = 'block';
    } else if (password !== passwordConfirm) {
        this.classList.remove('is-valid');
        this.classList.add('is-invalid');
        hint.style.display = 'none';
        feedback.textContent = 'Las contraseñas no coinciden';
        feedback.classList.remove('valid-feedback');
        feedback.classList.add('invalid-feedback', 'd-block');
    } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        hint.style.display = 'none';
        feedback.textContent = '✓ Las contraseñas coinciden';
        feedback.classList.remove('invalid-feedback');
        feedback.classList.add('valid-feedback', 'd-block');
    }
});

// Validación del formulario Bootstrap
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

</body>
</html>
