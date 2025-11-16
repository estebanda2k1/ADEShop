<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: usuarios.php');
    exit;
}

// Obtener usuario
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    $_SESSION['message'] = 'Usuario no encontrado';
    $_SESSION['message_type'] = 'danger';
    header('Location: usuarios.php');
    exit;
}

// Obtener estadísticas del usuario (pedidos si existen)
$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM orders WHERE user_email = ?');
$stmt->execute([$usuario['email']]);
$total_pedidos = $stmt->fetch()['total'];

require '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Usuario - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }
        .info-value {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        .user-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            color: white;
            margin: 0 auto 1rem;
        }
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="bi bi-person-circle"></i> Detalles del Usuario</h1>
                <p class="mb-0">Información completa del cliente</p>
            </div>
            <a href="usuarios.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Información Principal -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <?php 
                    $iniciales = strtoupper(substr($usuario['nombres'], 0, 1) . substr($usuario['apellidos'], 0, 1));
                    $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                    ?>
                    <div class="user-avatar-large" style="background-color: <?php echo $color; ?>">
                        <?php echo $iniciales; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']); ?></h3>
                    <p class="text-muted">@<?php echo htmlspecialchars($usuario['username']); ?></p>
                    
                    <?php if ($usuario['is_admin']): ?>
                        <span class="badge bg-danger mb-3">
                            <i class="bi bi-shield-check"></i> Administrador
                        </span>
                    <?php else: ?>
                        <span class="badge bg-primary mb-3">
                            <i class="bi bi-person"></i> Cliente
                        </span>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <a href="usuario_editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-warning">
                            <i class="bi bi-pencil"></i> Editar Usuario
                        </a>
                        <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                            <button onclick="confirmarEliminar()" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Eliminar Usuario
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas -->
            <div class="card shadow mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-graph-up"></i> Estadísticas
                    </h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Pedidos Realizados:</span>
                        <strong><?php echo $total_pedidos; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Fecha de Registro:</span>
                        <strong><?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Información Detallada -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle"></i> Información Personal
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">
                                <i class="bi bi-person"></i> Nombres
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($usuario['nombres']); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-label">
                                <i class="bi bi-person"></i> Apellidos
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($usuario['apellidos']); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-label">
                                <i class="bi bi-envelope"></i> Correo Electrónico
                            </div>
                            <div class="info-value">
                                <a href="mailto:<?php echo htmlspecialchars($usuario['email']); ?>">
                                    <?php echo htmlspecialchars($usuario['email']); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-label">
                                <i class="bi bi-credit-card"></i> Cédula
                            </div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($usuario['cedula']); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-label">
                                <i class="bi bi-at"></i> Username
                            </div>
                            <div class="info-value">
                                @<?php echo htmlspecialchars($usuario['username']); ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-label">
                                <i class="bi bi-shield"></i> Tipo de Usuario
                            </div>
                            <div class="info-value">
                                <?php echo $usuario['is_admin'] ? 'Administrador' : 'Cliente'; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <div class="info-label">
                                <i class="bi bi-calendar"></i> Fecha de Registro
                            </div>
                            <div class="info-value">
                                <?php echo date('d/m/Y H:i:s', strtotime($usuario['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actividad Reciente (placeholder para futuro) -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Actividad Reciente
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">No hay actividad reciente para mostrar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
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
                <p>¿Estás seguro de que deseas eliminar a este usuario?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="usuarios.php?action=delete&id=<?php echo $usuario['id']; ?>" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Eliminar Usuario
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmarEliminar() {
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
}
</script>
</body>
</html>
