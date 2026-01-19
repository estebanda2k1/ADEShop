<?php
require 'config.php';
require 'cart_helper.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: index.php'); exit;
}

$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: index.php'); exit; }

// Verificar si el usuario es administrador
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

require 'templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .product-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .product-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .product-image-container {
            position: relative;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            padding: 2rem;
        }
        .product-image {
            max-width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 10px;
        }
        .product-info {
            padding: 2rem;
        }
        .price-tag {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            display: inline-block;
            font-size: 2rem;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .info-badge {
            background: #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            display: inline-block;
        }
        .stock-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
        }
        .stock-available {
            background: #d4edda;
            color: #155724;
        }
        .stock-low {
            background: #fff3cd;
            color: #856404;
        }
        .stock-out {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>

<div class="product-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="bi bi-box-seam"></i> Detalle del Producto</h1>
                <p class="mb-0">Información completa del producto</p>
            </div>
            <a href="javascript:history.back()" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="product-card">
        <div class="row g-0">
            <div class="col-lg-6">
                <div class="product-image-container">
                    <?php if ($product['stock'] > 10): ?>
                        <span class="stock-badge stock-available">
                            <i class="bi bi-check-circle"></i> En Stock
                        </span>
                    <?php elseif ($product['stock'] > 0): ?>
                        <span class="stock-badge stock-low">
                            <i class="bi bi-exclamation-circle"></i> Stock Bajo
                        </span>
                    <?php else: ?>
                        <span class="stock-badge stock-out">
                            <i class="bi bi-x-circle"></i> Agotado
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($product['image']): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                             class="product-image" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="bi bi-image" style="font-size: 5rem;"></i>
                            <p class="mt-2">Sin imagen disponible</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="product-info">
                    <h2 class="mb-3"><?php echo htmlspecialchars($product['name']); ?></h2>
                    
                    <?php if ($product['category_name']): ?>
                        <div class="mb-3">
                            <span class="info-badge">
                                <i class="bi bi-tag"></i> <?php echo htmlspecialchars($product['category_name']); ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-4">
                        <h5 class="text-muted mb-2">Descripción:</h5>
                        <?php if (!empty($product['description'])): ?>
                            <p class="lead"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        <?php else: ?>
                            <p class="text-muted">Sin descripción disponible</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="text-muted mb-2">Información del Producto:</h5>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <strong><i class="bi bi-box"></i> Stock disponible:</strong>
                                <p class="mb-0"><?php echo $product['stock']; ?> unidades</p>
                            </div>
                            <div class="col-6 mb-2">
                                <strong><i class="bi bi-upc"></i> ID del Producto:</strong>
                                <p class="mb-0">#<?php echo $product['id']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h5 class="text-muted mb-3">Precio:</h5>
                        <div class="price-tag">
                            $<?php echo number_format($product['price'], 2); ?>
                        </div>
                    </div>
                    
                    <?php if (!$is_admin): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Nota:</strong> Para comprar este producto, visita la tienda principal.
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-primary btn-lg">
                                <i class="bi bi-shop"></i> Ir a la Tienda
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-shield-check"></i> 
                            <strong>Modo Administrador:</strong> Estás viendo este producto en modo de solo lectura.
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="admin/producto_editar.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-lg">
                                <i class="bi bi-pencil"></i> Editar Producto
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php require 'templates/footer.php'; ?>
