<?php
require_once '../config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: index.php');
    exit;
}

// Obtener estadísticas
$stmt = $pdo->query('SELECT COUNT(*) as total FROM users WHERE is_admin = 0');
$total_usuarios = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM products');
$total_productos = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM orders');
$total_pedidos = $stmt->fetch()['total'];

require '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .dashboard-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 15px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .stat-icon {
            font-size: 3rem;
            opacity: 0.3;
        }
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
        }
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <h1 class="mb-0"><i class="bi bi-speedometer2"></i> Panel de Administración</h1>
        <p class="mb-0">Bienvenido, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Administrador'); ?></p>
    </div>
</div>

<div class="container">
    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card dashboard-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-white-50">Clientes Registrados</h6>
                            <h2 class="card-title mb-0"><?php echo $total_usuarios; ?></h2>
                        </div>
                        <i class="bi bi-people-fill stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card dashboard-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-white-50">Productos</h6>
                            <h2 class="card-title mb-0"><?php echo $total_productos; ?></h2>
                        </div>
                        <i class="bi bi-box-seam stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card dashboard-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2 text-white-50">Pedidos</h6>
                            <h2 class="card-title mb-0"><?php echo $total_pedidos; ?></h2>
                        </div>
                        <i class="bi bi-cart-fill stat-icon"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Módulos de administración -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people text-primary" style="font-size: 4rem;"></i>
                    <h3 class="card-title mt-3">Gestión de Usuarios</h3>
                    <p class="card-text">Administra los clientes registrados en la plataforma</p>
                    <a href="admin/usuarios.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-right-circle"></i> Acceder
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam text-success" style="font-size: 4rem;"></i>
                    <h3 class="card-title mt-3">Gestión de Productos</h3>
                    <p class="card-text">Administra el inventario y catálogo de productos</p>
                    <a href="admin/productos.php" class="btn btn-success btn-lg">
                        <i class="bi bi-arrow-right-circle"></i> Acceder
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card dashboard-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cart text-warning" style="font-size: 4rem;"></i>
                    <h3 class="card-title mt-3">Gestión de Órdenes</h3>
                    <p class="card-text">Visualiza y administra las órdenes de compra</p>
                    <a href="admin/ordenes.php" class="btn btn-warning btn-lg">
                        <i class="bi bi-arrow-right-circle"></i> Acceder
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>