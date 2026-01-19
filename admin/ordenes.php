<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

// Paginación
$page = max(1, $_GET['page'] ?? 1);
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Búsqueda y filtros
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Construir query
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = '(o.id LIKE ? OR u.nombres LIKE ? OR u.apellidos LIKE ? OR u.email LIKE ?)';
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if (!empty($status_filter)) {
    $where[] = 'o.status = ?';
    $params[] = $status_filter;
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Contar total de órdenes
$count_query = "SELECT COUNT(*) FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                $where_clause";
$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_orders = $stmt->fetchColumn();
$total_pages = ceil($total_orders / $per_page);

// Obtener órdenes
$query = "SELECT o.*, u.nombres, u.apellidos, u.email,
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id
          $where_clause
          ORDER BY o.created_at DESC
          LIMIT $per_page OFFSET $offset";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$ordenes = $stmt->fetchAll();

require '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Órdenes - ADESHOP</title>
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
            padding: 5px 12px;
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
        .order-card {
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="bi bi-receipt"></i> Gestión de Órdenes</h1>
                <p class="mb-0">Administra todas las órdenes de compra</p>
            </div>
            <a href="../dashboard.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>
</div>

<div class="container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
            <?php 
            echo htmlspecialchars($_SESSION['message']); 
            unset($_SESSION['message'], $_SESSION['message_type']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Filtros y búsqueda -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Buscar:</label>
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           placeholder="ID, cliente, email..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Estado:</label>
                    <select name="status" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pendiente</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completado</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
                
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                    <a href="ordenes.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <?php
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(total) as revenue
            FROM orders");
        $stats = $stmt->fetch();
        ?>
        
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3 class="mb-0"><?php echo $stats['total']; ?></h3>
                    <p class="mb-0">Total Órdenes</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h3 class="mb-0"><?php echo $stats['pending']; ?></h3>
                    <p class="mb-0">Pendientes</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3 class="mb-0"><?php echo $stats['completed']; ?></h3>
                    <p class="mb-0">Completadas</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3 class="mb-0">$<?php echo number_format($stats['revenue'], 2); ?></h3>
                    <p class="mb-0">Ingresos Totales</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lista de órdenes -->
    <div class="card shadow">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-list-ul"></i> 
                Órdenes (<?php echo $total_orders; ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($ordenes)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-2">No se encontraron órdenes</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Email</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Método de Pago</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordenes as $orden): ?>
                                <tr>
                                    <td><strong>#<?php echo $orden['id']; ?></strong></td>
                                    <td><?php echo htmlspecialchars($orden['nombres'] . ' ' . $orden['apellidos']); ?></td>
                                    <td><?php echo htmlspecialchars($orden['email']); ?></td>
                                    <td><?php echo $orden['items_count']; ?></td>
                                    <td class="fw-bold text-success">$<?php echo number_format($orden['total'], 2); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($orden['payment_method'])) {
                                            $payment_icons = [
                                                'credit_card' => '<i class="bi bi-credit-card text-primary" title="Tarjeta de Crédito"></i>',
                                                'debit_card' => '<i class="bi bi-credit-card text-success" title="Tarjeta de Débito"></i>',
                                                'paypal' => '<i class="bi bi-paypal" style="color: #003087;" title="PayPal"></i>',
                                                'bank_transfer' => '<i class="bi bi-bank text-info" title="Transferencia"></i>',
                                                'cash' => '<i class="bi bi-cash-coin text-warning" title="Efectivo"></i>'
                                            ];
                                            echo $payment_icons[$orden['payment_method']] ?? htmlspecialchars($orden['payment_method']);
                                        } else {
                                            echo '<span class="text-muted">-</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
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
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($orden['created_at'])); ?></td>
                                    <td>
                                        <a href="orden_ver.php?id=<?php echo $orden['id']; ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Paginación -->
    <?php if ($total_pages > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                            Anterior
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">
                            Siguiente
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
