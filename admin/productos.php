<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

// Manejo de acciones (eliminar)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['message'] = 'Producto eliminado correctamente';
        $_SESSION['message_type'] = 'success';
        header('Location: productos.php');
        exit;
    }
}

// Obtener lista de productos
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if ($search) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
}

if ($category_filter) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = (int)$category_filter;
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

$sql = "SELECT p.*, c.name AS category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where_sql 
        ORDER BY p.created_at DESC 
        LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// Contar total
$count_sql = "SELECT COUNT(*) as total FROM products p $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_productos = $stmt->fetch()['total'];
$total_pages = ceil($total_productos / $per_page);

// Obtener categorías para filtro
$categorias = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

require '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .product-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .low-stock {
            background-color: #dc3545;
        }
        .in-stock {
            background-color: #28a745;
        }
        .offer-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
            z-index: 10;
        }
        .price-original {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .price-sale {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="bi bi-box-seam-fill"></i> Gestión de Productos</h1>
                <p class="mb-0">Control de inventario y catálogo de productos</p>
            </div>
            <a href="../dashboard.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Mensajes -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?php 
            echo htmlspecialchars($_SESSION['message']); 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Barra de herramientas -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center mb-3">
                <div class="col-md-4">
                    <form method="get" class="d-flex">
                        <?php if ($category_filter): ?>
                            <input type="hidden" name="category" value="<?php echo $category_filter; ?>">
                        <?php endif; ?>
                        <input type="text" name="search" class="form-control me-2" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                        <?php if ($search): ?>
                            <a href="productos.php<?php echo $category_filter ? '?category=' . $category_filter : ''; ?>" class="btn btn-secondary ms-2">
                                <i class="bi bi-x"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="col-md-4">
                    <form method="get" id="categoryFilterForm">
                        <?php if ($search): ?>
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <?php endif; ?>
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">Todas las categorías</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                
                <div class="col-md-4 text-end">
                    <a href="producto_crear.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Nuevo Producto
                    </a>
                </div>
            </div>
            
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle"></i> 
                Total de productos: <strong><?php echo $total_productos; ?></strong>
                <?php if ($search || $category_filter): ?>
                    | Mostrando resultados filtrados
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Grid de productos -->
    <?php if (empty($productos)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
            <p class="mt-3 text-muted">
                <?php echo ($search || $category_filter) ? 'No se encontraron productos con ese criterio' : 'No hay productos registrados todavía'; ?>
            </p>
            <a href="producto_crear.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Crear primer producto
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($productos as $producto): ?>
                <div class="col-md-3 mb-4">
                    <div class="card product-card">
                        <div class="position-relative">
                            <?php if ($producto['image'] && file_exists('../' . $producto['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($producto['image']); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($producto['name']); ?>">
                            <?php else: ?>
                                <div class="product-image bg-secondary d-flex align-items-center justify-content-center">
                                    <i class="bi bi-image" style="font-size: 4rem; color: white;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($producto['is_on_sale']): ?>
                                <span class="offer-badge">
                                    -<?php echo $producto['sale_percentage']; ?>% OFF
                                </span>
                            <?php endif; ?>
                            
                            <span class="badge stock-badge <?php echo $producto['stock'] <= 5 ? 'low-stock' : 'in-stock'; ?>">
                                Stock: <?php echo $producto['stock']; ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($producto['name']); ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-tag"></i> <?php echo htmlspecialchars($producto['category_name'] ?? 'Sin categoría'); ?>
                            </p>
                            
                            <?php if ($producto['is_on_sale'] && $producto['sale_price']): ?>
                                <div class="mb-2">
                                    <span class="price-original">$<?php echo number_format($producto['price'], 2); ?></span>
                                </div>
                                <h4 class="price-sale mb-1">$<?php echo number_format($producto['sale_price'], 2); ?></h4>
                                <p class="text-success small mb-3">
                                    Ahorras: $<?php echo number_format($producto['price'] - $producto['sale_price'], 2); ?>
                                </p>
                            <?php else: ?>
                                <h4 class="text-primary mb-3">$<?php echo number_format($producto['price'], 2); ?></h4>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2">
                                <a href="producto_ver.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-info text-white">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <div class="btn-group" role="group">
                                    <a href="producto_editar.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <button onclick="confirmarEliminar(<?php echo $producto['id']; ?>, '<?php echo htmlspecialchars($producto['name']); ?>')" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Navegación de productos" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?>">
                                Anterior
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category_filter ? '&category=' . $category_filter : ''; ?>">
                                Siguiente
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el producto <strong id="productoNombre"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btnConfirmarEliminar" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Eliminar Producto
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmarEliminar(id, nombre) {
    document.getElementById('productoNombre').textContent = nombre;
    document.getElementById('btnConfirmarEliminar').href = 'productos.php?action=delete&id=' + id;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
}
</script>
</body>
</html>
