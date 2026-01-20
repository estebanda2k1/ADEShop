<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: productos.php');
    exit;
}

// Obtener producto
$stmt = $pdo->prepare('
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    $_SESSION['message'] = 'Producto no encontrado';
    $_SESSION['message_type'] = 'danger';
    header('Location: productos.php');
    exit;
}

require '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Producto - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .product-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
        }
        .stock-badge-low {
            background: #dc3545;
            color: white;
        }
        .stock-badge-good {
            background: #28a745;
            color: white;
        }
        .stock-badge-out {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="bi bi-box-seam"></i> Detalles del Producto</h1>
                <p class="mb-0">Información completa del producto</p>
            </div>
            <div>
                <a href="producto_editar.php?id=<?php echo $producto['id']; ?>" class="btn btn-light me-2">
                    <i class="bi bi-pencil"></i> Editar
                </a>
                <a href="productos.php" class="btn btn-outline-light">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <?php if ($producto['image']): ?>
                        <img src="<?php echo htmlspecialchars($producto['image']); ?>" 
                             alt="<?php echo htmlspecialchars($producto['name']); ?>"
                             class="product-image"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\"bg-secondary text-white p-5 rounded\" style=\"height: 300px; display: flex; align-items: center; justify-content: center;\"><div><i class=\"bi bi-image\" style=\"font-size: 4rem;\"></i><p class=\"mt-2\">Imagen no disponible</p></div></div>';">
                    <?php else: ?>
                        <div class="bg-secondary text-white p-5 rounded" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                            <div>
                                <i class="bi bi-image" style="font-size: 4rem;"></i>
                                <p class="mt-2">Sin imagen</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información General</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="info-label">Nombre:</span>
                        <h4 class="mb-0"><?php echo htmlspecialchars($producto['name']); ?></h4>
                    </div>
                    
                    <div class="mb-3">
                        <span class="info-label">ID del Producto:</span>
                        <p class="mb-0">#<?php echo $producto['id']; ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <span class="info-label">Categoría:</span>
                        <p class="mb-0">
                            <?php if ($producto['category_name']): ?>
                                <span class="badge bg-info"><?php echo htmlspecialchars($producto['category_name']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">Sin categoría</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <span class="info-label">Descripción:</span>
                        <p class="mb-0">
                            <?php if ($producto['description']): ?>
                                <?php echo nl2br(htmlspecialchars($producto['description'])); ?>
                            <?php else: ?>
                                <span class="text-muted">Sin descripción</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <span class="info-label">Precio:</span>
                            <h3 class="text-success mb-0">
                                $<?php echo number_format($producto['price'], 2); ?>
                            </h3>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <span class="info-label">Stock Disponible:</span>
                            <h3 class="mb-0">
                                <?php if ($producto['stock'] == 0): ?>
                                    <span class="badge stock-badge-out fs-5">
                                        <i class="bi bi-x-circle"></i> Agotado
                                    </span>
                                <?php elseif ($producto['stock'] <= 5): ?>
                                    <span class="badge stock-badge-low fs-5">
                                        <i class="bi bi-exclamation-triangle"></i> <?php echo $producto['stock']; ?> unidades
                                    </span>
                                <?php else: ?>
                                    <span class="badge stock-badge-good fs-5">
                                        <i class="bi bi-check-circle"></i> <?php echo $producto['stock']; ?> unidades
                                    </span>
                                <?php endif; ?>
                            </h3>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-2">
                        <span class="info-label">Fecha de Creación:</span>
                        <p class="mb-0">
                            <i class="bi bi-calendar-event"></i> 
                            <?php echo date('d/m/Y H:i', strtotime($producto['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Acciones</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="producto_editar.php?id=<?php echo $producto['id']; ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Editar Producto
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash"></i> Eliminar Producto
                        </button>
                        <a href="productos.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al Listado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro que deseas eliminar este producto?</p>
                <p class="mb-0"><strong><?php echo htmlspecialchars($producto['name']); ?></strong></p>
                <p class="text-muted mt-2">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form method="post" action="producto_eliminar.php" style="display: inline;">
                    <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
