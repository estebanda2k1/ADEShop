<?php
require 'config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: iniciarSesion.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Paginación
$page = max(1, $_GET['page'] ?? 1);
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Contar total de órdenes del usuario
$stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
$stmt->execute([$user_id]);
$total_orders = $stmt->fetchColumn();
$total_pages = ceil($total_orders / $per_page);

// Obtener órdenes del usuario
$stmt = $pdo->prepare("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
    LIMIT $per_page OFFSET $offset
");
$stmt->execute([$user_id]);
$ordenes = $stmt->fetchAll();

require 'templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Pedidos - ADESHOP</title>
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
        .order-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .status-badge {
            padding: 6px 14px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        .status-completed {
            background-color: #28a745;
            color: white;
        }
        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }
        .payment-icon {
            font-size: 1.5rem;
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
        <h1><i class="bi bi-clock-history"></i> Mis Pedidos</h1>
        <p class="mb-0">Historial completo de tus compras</p>
    </div>
</div>

<div class="container mb-5">
    <?php if (empty($ordenes)): ?>
        <div class="card">
            <div class="card-body empty-state">
                <i class="bi bi-bag-x"></i>
                <h3>No tienes pedidos aún</h3>
                <p class="text-muted">Cuando realices tu primera compra, aparecerá aquí</p>
                <a href="index.php" class="btn btn-primary mt-3">
                    <i class="bi bi-shop"></i> Ir a la Tienda
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row mb-3">
            <div class="col">
                <h5>Total de pedidos: <span class="badge bg-primary"><?php echo $total_orders; ?></span></h5>
            </div>
            <div class="col text-end">
                <a href="productos_mas_vendidos.php" class="btn btn-outline-primary">
                    <i class="bi bi-graph-up"></i> Ver Productos Más Vendidos
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php foreach ($ordenes as $orden): ?>
                <?php
                // Obtener items de la orden
                $stmt = $pdo->prepare('
                    SELECT oi.*, p.name, p.image
                    FROM order_items oi
                    LEFT JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = ?
                    LIMIT 3
                ');
                $stmt->execute([$orden['id']]);
                $items = $stmt->fetchAll();
                
                $status_labels = [
                    'pending' => 'Pendiente',
                    'completed' => 'Completado',
                    'cancelled' => 'Cancelado'
                ];
                
                $payment_methods = [
                    'credit_card' => '<i class="bi bi-credit-card text-primary payment-icon" title="Tarjeta de Crédito"></i>',
                    'debit_card' => '<i class="bi bi-credit-card text-success payment-icon" title="Tarjeta de Débito"></i>',
                    'paypal' => '<i class="bi bi-paypal payment-icon" style="color: #003087;" title="PayPal"></i>',
                    'bank_transfer' => '<i class="bi bi-bank text-info payment-icon" title="Transferencia"></i>',
                    'cash' => '<i class="bi bi-cash-coin text-warning payment-icon" title="Efectivo"></i>'
                ];
                ?>
                <div class="col-md-6">
                    <div class="card order-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">Pedido #<?php echo $orden['id']; ?></h5>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($orden['created_at'])); ?>
                                    </small>
                                </div>
                                <span class="status-badge status-<?php echo $orden['status']; ?>">
                                    <?php echo $status_labels[$orden['status']] ?? $orden['status']; ?>
                                </span>
                            </div>
                            
                            <!-- Productos -->
                            <div class="mb-3">
                                <h6 class="text-muted mb-2">Productos (<?php echo $orden['items_count']; ?>):</h6>
                                <div class="row g-2">
                                    <?php foreach ($items as $item): ?>
                                        <div class="col-12">
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['image']): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"
                                                         class="me-2">
                                                <?php else: ?>
                                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center me-2"
                                                         style="width: 50px; height: 50px; border-radius: 5px;">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex-grow-1">
                                                    <small class="fw-bold d-block"><?php echo htmlspecialchars($item['name']); ?></small>
                                                    <small class="text-muted">Cant: <?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if ($orden['items_count'] > 3): ?>
                                        <div class="col-12">
                                            <small class="text-muted">+ <?php echo ($orden['items_count'] - 3); ?> producto(s) más</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Total y Método de Pago -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <div>
                                    <small class="text-muted d-block">Método de pago:</small>
                                    <?php 
                                    if (!empty($orden['payment_method'])) {
                                        echo $payment_methods[$orden['payment_method']] ?? htmlspecialchars($orden['payment_method']);
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted d-block">Total:</small>
                                    <h4 class="mb-0 text-success">$<?php echo number_format($orden['total'], 2); ?></h4>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pt-0">
                            <a href="pedido_detalle.php?id=<?php echo $orden['id']; ?>" class="btn btn-sm btn-outline-primary w-100">
                                <i class="bi bi-eye"></i> Ver Detalles
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Anterior</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Siguiente</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
