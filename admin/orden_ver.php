<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: ordenes.php');
    exit;
}

// Obtener orden con información del usuario
$stmt = $pdo->prepare('
    SELECT o.*, u.nombres, u.apellidos, u.email, u.cedula
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
');
$stmt->execute([$id]);
$orden = $stmt->fetch();

if (!$orden) {
    $_SESSION['message'] = 'Orden no encontrada';
    $_SESSION['message_type'] = 'danger';
    header('Location: ordenes.php');
    exit;
}

// Obtener items de la orden
$stmt = $pdo->prepare('
    SELECT oi.*, p.name as product_name, p.image
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
');
$stmt->execute([$id]);
$items = $stmt->fetchAll();

// Procesar actualización de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    $stmt->execute([$new_status, $id]);
    
    $_SESSION['message'] = 'Estado actualizado correctamente';
    $_SESSION['message_type'] = 'success';
    header('Location: orden_ver.php?id=' . $id);
    exit;
}

require '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden #<?php echo $orden['id']; ?> - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .admin-header {
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
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="bi bi-receipt-cutoff"></i> Orden #<?php echo $orden['id']; ?></h1>
                <p class="mb-0">Detalles completos de la orden</p>
            </div>
            <a href="ordenes.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="container mb-5">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
            <?php 
            echo htmlspecialchars($_SESSION['message']); 
            unset($_SESSION['message'], $_SESSION['message_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Información de la orden -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Productos Ordenados</h5>
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
                                                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"
                                                         class="me-3">
                                                <?php else: ?>
                                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center me-3"
                                                         style="width: 50px; height: 50px; border-radius: 5px;">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                </div>
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
        
        <!-- Información del cliente y estado -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="info-label">Nombre:</span>
                        <p class="mb-0"><?php echo htmlspecialchars($orden['nombres'] . ' ' . $orden['apellidos']); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <span class="info-label">Email:</span>
                        <p class="mb-0"><?php echo htmlspecialchars($orden['email']); ?></p>
                    </div>
                    
                    <div class="mb-0">
                        <span class="info-label">Cédula:</span>
                        <p class="mb-0"><?php echo htmlspecialchars($orden['cedula']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información de Orden</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="info-label">ID de Orden:</span>
                        <p class="mb-0"><strong>#<?php echo $orden['id']; ?></strong></p>
                    </div>
                    
                    <div class="mb-3">
                        <span class="info-label">Fecha:</span>
                        <p class="mb-0"><?php echo date('d/m/Y H:i:s', strtotime($orden['created_at'])); ?></p>
                    </div>
                    
                    <div class="mb-0">
                        <span class="info-label">Estado Actual:</span>
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
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Acciones</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Cambiar Estado:</label>
                            <select name="status" class="form-select">
                                <option value="pending" <?php echo $orden['status'] === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="completed" <?php echo $orden['status'] === 'completed' ? 'selected' : ''; ?>>Completado</option>
                                <option value="cancelled" <?php echo $orden['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="update_status" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> Actualizar Estado
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
