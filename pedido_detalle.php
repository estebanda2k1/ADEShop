<?php
require 'config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: iniciarSesion.php');
    exit;
}

$order_id = $_GET['id'] ?? null;
$message = '';
$message_type = '';

if (!$order_id) {
    header('Location: mis_pedidos.php');
    exit;
}

// Procesar cancelación de orden
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    try {
        $pdo->beginTransaction();
        
        // Verificar que la orden pertenezca al usuario y esté en estado cancelable
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception('Orden no encontrada');
        }
        
        if ($order['status'] !== 'pending') {
            throw new Exception('Solo se pueden cancelar órdenes pendientes');
        }
        
        // Obtener items de la orden para devolver el stock
        $stmt = $pdo->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = ?');
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll();
        
        // Devolver stock a cada producto
        foreach ($order_items as $item) {
            $stmt = $pdo->prepare('UPDATE products SET stock = stock + ? WHERE id = ?');
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Actualizar estado de la orden
        $stmt = $pdo->prepare('UPDATE orders SET status = ?, payment_status = ? WHERE id = ?');
        $stmt->execute(['cancelled', 'refunded', $order_id]);
        
        $pdo->commit();
        
        $message = '✓ Pedido cancelado exitosamente. Los productos han sido devueltos al inventario.';
        $message_type = 'success';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = 'Error al cancelar el pedido: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Obtener la orden verificando que pertenezca al usuario
$stmt = $pdo->prepare('
    SELECT o.*, u.nombres, u.apellidos, u.email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ? AND o.user_id = ?
');
$stmt->execute([$order_id, $_SESSION['user_id']]);
$orden = $stmt->fetch();

if (!$orden) {
    $_SESSION['message'] = 'Orden no encontrada o no tienes permiso para verla';
    $_SESSION['message_type'] = 'danger';
    header('Location: mis_pedidos.php');
    exit;
}

// Obtener items de la orden
$stmt = $pdo->prepare('
    SELECT oi.*, p.name as product_name, p.image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
');
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

require 'templates/header.php';
?>

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
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 1rem;
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
    .info-label {
        font-weight: 600;
        color: #6c757d;
    }
    .order-summary-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>

<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="bi bi-receipt-cutoff"></i> Pedido #<?php echo $orden['id']; ?></h1>
                <p class="mb-0">Detalle completo de tu pedido</p>
            </div>
            <a href="mis_pedidos.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Mensajes de alerta -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
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
    
    <div class="row">
        <!-- Productos -->
        <div class="col-lg-8">
            <div class="card order-summary-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Productos del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio Unit.</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($item['image']): ?>
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;"
                                                         class="me-3"
                                                         onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="bg-secondary text-white align-items-center justify-content-center me-3" style="width: 60px; height: 60px; border-radius: 5px; display: none;">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center me-3"
                                                         style="width: 60px; height: 60px; border-radius: 5px;">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                            </div>
                                        </td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td class="fw-bold">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="fw-bold text-success fs-5">$<?php echo number_format($orden['total'], 2); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información del pedido -->
        <div class="col-lg-4">
            <div class="card order-summary-card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="info-label">Fecha:</span>
                        <p class="mb-0"><?php echo date('d/m/Y H:i:s', strtotime($orden['created_at'])); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <span class="info-label">Estado:</span>
                        <p class="mt-2">
                            <span class="status-badge status-<?php echo $orden['status']; ?>">
                                <?php 
                                $status_labels = [
                                    'pending' => 'Pendiente',
                                    'completed' => 'Completado',
                                    'cancelled' => 'Cancelado'
                                ];
                                echo $status_labels[$orden['status']] ?? $orden['status'];
                                ?>
                            </span>
                        </p>
                    </div>
                    
                    <?php if (!empty($orden['payment_method'])): ?>
                        <div class="mb-3">
                            <span class="info-label">Método de Pago:</span>
                            <p class="mb-0 mt-1">
                                <?php
                                $payment_methods = [
                                    'credit_card' => '<i class="bi bi-credit-card text-primary"></i> Tarjeta de Crédito',
                                    'debit_card' => '<i class="bi bi-credit-card text-success"></i> Tarjeta de Débito',
                                    'paypal' => '<i class="bi bi-paypal" style="color: #003087;"></i> PayPal',
                                    'bank_transfer' => '<i class="bi bi-bank text-info"></i> Transferencia Bancaria',
                                    'cash' => '<i class="bi bi-cash-coin text-warning"></i> Efectivo'
                                ];
                                echo $payment_methods[$orden['payment_method']] ?? htmlspecialchars($orden['payment_method']);
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mb-0">
                        <span class="info-label">Cliente:</span>
                        <p class="mb-0"><?php echo htmlspecialchars($orden['nombres'] . ' ' . $orden['apellidos']); ?></p>
                        <p class="mb-0 text-muted"><small><?php echo htmlspecialchars($orden['email']); ?></small></p>
                    </div>
                </div>
            </div>
            
            <div class="card order-summary-card">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-info-circle-fill text-primary"></i> Estado del Pedido</h6>
                    
                    <?php if ($orden['status'] === 'pending'): ?>
                        <div class="alert alert-warning mb-3">
                            <strong>Pendiente:</strong> Tu pedido está siendo procesado. Te notificaremos cuando sea enviado.
                        </div>
                        
                        <!-- Botón para cancelar pedido -->
                        <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas cancelar este pedido? El stock de los productos será devuelto.');">
                            <input type="hidden" name="cancel_order" value="1">
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-x-circle"></i> Cancelar Pedido
                            </button>
                        </form>
                    <?php elseif ($orden['status'] === 'completed'): ?>
                        <div class="alert alert-success mb-0">
                            <strong>Completado:</strong> Tu pedido ha sido entregado satisfactoriamente.
                        </div>
                    <?php elseif ($orden['status'] === 'cancelled'): ?>
                        <div class="alert alert-danger mb-0">
                            <strong>Cancelado:</strong> Este pedido ha sido cancelado.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php require 'templates/footer.php'; ?>
