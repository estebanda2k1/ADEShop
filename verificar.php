<?php
/**
 * Script de verificación para el módulo de registro
 * Ejecuta este archivo para verificar que todo está configurado correctamente
 */

echo "<h1>🔍 Verificación del Sistema ADESHOP</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// 1. Verificar versión de PHP
echo "<h2>1. Versión de PHP</h2>";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "<p class='success'>✅ PHP " . PHP_VERSION . " (Cumple requisito >= 7.4)</p>";
} else {
    echo "<p class='error'>❌ PHP " . PHP_VERSION . " (Se requiere >= 7.4)</p>";
}

// 2. Verificar extensiones necesarias
echo "<h2>2. Extensiones PHP</h2>";
$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p class='success'>✅ Extensión '$ext' cargada</p>";
    } else {
        echo "<p class='error'>❌ Extensión '$ext' NO cargada</p>";
    }
}

// 3. Verificar archivos del proyecto
echo "<h2>3. Archivos del Proyecto</h2>";
$files = [
    'config.php',
    'registro.php',
    'dashboard.php',
    'index.php',
    'templates/header.php',
    'templates/footer.php',
    'database/adeshop.sql',
    'admin/usuarios.php',
    'admin/usuario_crear.php',
    'admin/usuario_ver.php',
    'admin/usuario_editar.php',
    'admin/usuario_exportar.php',
    'docs/REGISTRO.md',
    'docs/CRUD_USUARIOS.md',
    'README.md'
];

foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p class='success'>✅ $file existe</p>";
    } else {
        echo "<p class='error'>❌ $file NO existe</p>";
    }
}

// 4. Verificar conexión a la base de datos
echo "<h2>4. Conexión a Base de Datos</h2>";
try {
    require 'config.php';
    echo "<p class='success'>✅ Conexión a la base de datos exitosa</p>";
    
    // Verificar tabla users
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✅ Tabla 'users' existe</p>";
        
        // Verificar estructura de la tabla
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $required_columns = ['id', 'nombres', 'apellidos', 'email', 'cedula', 'username', 'password', 'is_admin'];
        $missing_columns = array_diff($required_columns, $columns);
        
        if (empty($missing_columns)) {
            echo "<p class='success'>✅ Estructura de tabla 'users' correcta</p>";
            echo "<p class='info'>📋 Columnas: " . implode(', ', $columns) . "</p>";
        } else {
            echo "<p class='error'>❌ Faltan columnas en 'users': " . implode(', ', $missing_columns) . "</p>";
            echo "<p class='info'>💡 Reimporta el archivo database/adeshop.sql</p>";
        }
        
        // Contar usuarios
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
        $count = $stmt->fetch()['total'];
        echo "<p class='info'>👥 Total de usuarios registrados: $count</p>";
        
    } else {
        echo "<p class='error'>❌ Tabla 'users' NO existe</p>";
        echo "<p class='info'>💡 Importa el archivo database/adeshop.sql desde phpMyAdmin</p>";
    }
    
    // Verificar otras tablas
    $tables = ['categories', 'products', 'orders', 'order_items'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✅ Tabla '$table' existe</p>";
        } else {
            echo "<p class='error'>❌ Tabla '$table' NO existe</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error de conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='info'>💡 Verifica:</p>";
    echo "<ul>";
    echo "<li>MySQL está corriendo en XAMPP</li>";
    echo "<li>Las credenciales en config.php son correctas</li>";
    echo "<li>La base de datos 'adeshop' existe</li>";
    echo "</ul>";
}

// 5. Verificar funciones de seguridad
echo "<h2>5. Funciones de Seguridad</h2>";
if (function_exists('password_hash')) {
    echo "<p class='success'>✅ password_hash() disponible</p>";
    $test_hash = password_hash('test123', PASSWORD_DEFAULT);
    echo "<p class='info'>🔐 Ejemplo de hash: " . substr($test_hash, 0, 30) . "...</p>";
} else {
    echo "<p class='error'>❌ password_hash() NO disponible</p>";
}

if (function_exists('password_verify')) {
    echo "<p class='success'>✅ password_verify() disponible</p>";
} else {
    echo "<p class='error'>❌ password_verify() NO disponible</p>";
}

// 6. Verificar permisos de escritura (si es necesario)
echo "<h2>6. Permisos del Sistema</h2>";
if (is_writable(__DIR__)) {
    echo "<p class='success'>✅ Directorio tiene permisos de escritura</p>";
} else {
    echo "<p class='error'>⚠️ Directorio NO tiene permisos de escritura (puede ser necesario para uploads futuros)</p>";
}

// 7. Enlaces rápidos
echo "<h2>7. Enlaces Rápidos</h2>";
echo "<ul>";
echo "<li><a href='index.php'>🏠 Página Principal</a></li>";
echo "<li><a href='registro.php'>📝 Formulario de Registro</a></li>";
echo "<li><a href='cart.php'>🛒 Carrito</a></li>";
echo "<li><a href='dashboard.php'>📊 Dashboard Administrativo</a></li>";
echo "<li><a href='admin/login.php'>👨‍💼 Login Admin</a></li>";
echo "<li><a href='admin/usuarios.php'>👥 Gestión de Usuarios (CRUD)</a></li>";
echo "<li><a href='docs/preview-registro.html'>📄 Vista Previa Módulo Registro</a></li>";
echo "<li><a href='docs/preview-crud-usuarios.html'>📄 Vista Previa Módulo CRUD</a></li>";
echo "</ul>";

// Resumen final
echo "<hr>";
echo "<h2>📊 Resumen</h2>";
echo "<p><strong>Estado del Sistema:</strong> ";
if (isset($pdo) && !empty($columns) && empty($missing_columns)) {
    echo "<span class='success'>✅ SISTEMA LISTO PARA USAR</span></p>";
    echo "<p>🎉 Puedes empezar a registrar usuarios en: <a href='registro.php'>registro.php</a></p>";
} else {
    echo "<span class='error'>⚠️ REQUIERE CONFIGURACIÓN</span></p>";
    echo "<p>Por favor revisa los errores anteriores y sigue las instrucciones.</p>";
}

echo "<hr>";
echo "<p><small>Script de verificación v1.0 - ADESHOP</small></p>";
?>
