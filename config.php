<?php
// Database configuration - edit as needed
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
    // Simple error page
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}

session_start();

// Función para verificar si existe un administrador
function checkAdminExists($pdo) {
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM users WHERE is_admin = 1');
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

// Verificar si necesitamos redirigir al setup
// Solo verificar si no estamos ya en setup.php y no hay administradores
$currentScript = basename($_SERVER['PHP_SELF']);
if (!checkAdminExists($pdo) && $currentScript !== 'setup.php') {
    // Obtener la ruta correcta al setup
    $scriptDir = dirname($_SERVER['PHP_SELF']);
    $setupPath = ($scriptDir === '/' || $scriptDir === '\\') ? '/ADEShop/setup.php' : str_replace('/admin', '', $scriptDir) . '/setup.php';
    header('Location: ' . $setupPath);
    exit;
}
