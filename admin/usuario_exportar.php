<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

// Obtener todos los usuarios (clientes)
$stmt = $pdo->query('SELECT id, nombres, apellidos, email, cedula, username, is_admin, created_at FROM users WHERE is_admin = 0 ORDER BY created_at DESC');
$usuarios = $stmt->fetchAll();

// Configurar headers para descarga CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=usuarios_' . date('Y-m-d_His') . '.csv');

// Crear output
$output = fopen('php://output', 'w');

// BOM para UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Encabezados
fputcsv($output, ['ID', 'Nombres', 'Apellidos', 'Email', 'Cédula', 'Username', 'Tipo', 'Fecha Registro']);

// Datos
foreach ($usuarios as $usuario) {
    fputcsv($output, [
        $usuario['id'],
        $usuario['nombres'],
        $usuario['apellidos'],
        $usuario['email'],
        $usuario['cedula'],
        $usuario['username'],
        $usuario['is_admin'] ? 'Administrador' : 'Cliente',
        date('Y-m-d H:i:s', strtotime($usuario['created_at']))
    ]);
}

fclose($output);
exit;
?>
