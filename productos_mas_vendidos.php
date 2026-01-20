<?php
require 'config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: iniciarSesion.php');
    exit;
}

// Obtener productos más vendidos
$stmt = $pdo->query('
    SELECT 
        p.id,
        p.name,
        p.price,
        p.image,
        p.stock,
        c.name as category_name,
        SUM(oi.quantity) as total_vendido,
        COUNT(DISTINCT oi.order_id) as num_ordenes,
        SUM(oi.quantity * oi.price) as ingresos_totales
    FROM products p
    INNER JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN categories c ON p.category_id = c.id
    GROUP BY p.id
    ORDER BY total_vendido DESC
    LIMIT 20
');
$productos_vendidos = $stmt->fetchAll();

// Calcular el total de ventas
$total_ventas = 0;
$total_productos_vendidos = 0;
foreach ($productos_vendidos as $producto) {
    $total_ventas += $producto['ingresos_totales'];
    $total_productos_vendidos += $producto['total_vendido'];
}

require 'templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos Más Vendidos - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .product-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }
        .rank-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            z-index: 1;
        }
        .rank-badge.top-1 {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        }
        .rank-badge.top-2 {
            background: linear-gradient(135deg, #C0C0C0 0%, #808080 100%);
        }
        .rank-badge.top-3 {
            background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%);
        }
        .stats-card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .product-img-container {
            position: relative;
            height: 200px;
            overflow: hidden;
            border-radius: 8px 8px 0 0;
        }
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        .empty-state i {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="bi bi-graph-up-arrow"></i> Productos Más Vendidos</h1>
                <p class="mb-0">Descubre los productos favoritos de nuestra tienda</p>
            </div>
            <a href="dashboard.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card stats-card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Productos Vendidos</h6>
                            <h2 class="mb-0"><?php echo number_format($total_productos_vendidos); ?></h2>
                        </div>
                        <i class="bi bi-box-seam" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stats-card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Ingresos Totales</h6>
                            <h2 class="mb-0">$<?php echo number_format($total_ventas, 2); ?></h2>
                        </div>
                        <i class="bi bi-currency-dollar" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card stats-card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Productos en Ranking</h6>
                            <h2 class="mb-0"><?php echo count($productos_vendidos); ?></h2>
                        </div>
                        <i class="bi bi-star-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mb-4">
        <h4><i class="bi bi-trophy"></i> Ranking de Ventas</h4>
    </div>
    
    <?php if (empty($productos_vendidos)): ?>
        <div class="card">
            <div class="card-body empty-state">
                <i class="bi bi-graph-down"></i>
                <h3>No hay datos de ventas aún</h3>
                <p class="text-muted">Cuando se realicen compras, los productos aparecerán aquí</p>
                <a href="index.php" class="btn btn-primary mt-3">
                    <i class="bi bi-shop"></i> Ir a la Tienda
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($productos_vendidos as $index => $producto): 
                $rank = $index + 1;
                $rank_class = '';
                if ($rank == 1) $rank_class = 'top-1';
                elseif ($rank == 2) $rank_class = 'top-2';
                elseif ($rank == 3) $rank_class = 'top-3';
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card">
                        <div class="product-img-container">
                            <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                            <?php if ($producto['image']): ?>
                                <img src="<?php echo htmlspecialchars($producto['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($producto['name']); ?>"
                                     class="product-img"
                                     onerror="this.style.display='none'; var div = document.createElement('div'); div.className='bg-secondary text-white d-flex align-items-center justify-content-center h-100'; div.innerHTML='<i class=\"bi bi-image\" style=\"font-size: 3rem;\"></i>'; this.parentElement.appendChild(div); this.onerror=null;">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center h-100">
                                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($producto['name']); ?></h5>
                            <?php if ($producto['category_name']): ?>
                                <p class="text-muted mb-2">
                                    <small><i class="bi bi-tag"></i> <?php echo htmlspecialchars($producto['category_name']); ?></small>
                                </p>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Unidades vendidas:</span>
                                    <strong class="text-primary"><?php echo number_format($producto['total_vendido']); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Órdenes:</span>
                                    <strong><?php echo $producto['num_ordenes']; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Ingresos generados:</span>
                                    <strong class="text-success">$<?php echo number_format($producto['ingresos_totales'], 2); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Precio actual:</span>
                                    <strong>$<?php echo number_format($producto['price'], 2); ?></strong>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="product.php?id=<?php echo $producto['id']; ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver Producto
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
