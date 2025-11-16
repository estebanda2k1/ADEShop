<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

// Manejo de acciones (eliminar, activar/desactivar)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    if ($action === 'delete' && $id > 0) {
        // No permitir eliminar al usuario actual
        if ($id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND is_admin = 0');
            $stmt->execute([$id]);
            $_SESSION['message'] = 'Usuario eliminado correctamente';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'No puedes eliminar tu propio usuario';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: usuarios.php');
        exit;
    }
}

// Obtener lista de usuarios (clientes, no administradores)
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

if ($search) {
    $search_term = '%' . $search . '%';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE is_admin = 0 AND (nombres LIKE ? OR apellidos LIKE ? OR email LIKE ? OR cedula LIKE ?) ORDER BY created_at DESC LIMIT ' . $per_page . ' OFFSET ' . $offset);
    $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
    $usuarios = $stmt->fetchAll();
    
    $stmt = $pdo->prepare('SELECT COUNT(*) as total FROM users WHERE is_admin = 0 AND (nombres LIKE ? OR apellidos LIKE ? OR email LIKE ? OR cedula LIKE ?)');
    $stmt->execute([$search_term, $search_term, $search_term, $search_term]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE is_admin = 0 ORDER BY created_at DESC LIMIT ' . $per_page . ' OFFSET ' . $offset);
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
    
    $stmt = $pdo->query('SELECT COUNT(*) as total FROM users WHERE is_admin = 0');
}

$total_usuarios = $stmt->fetch()['total'];
$total_pages = ceil($total_usuarios / $per_page);

require '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .table-actions button {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        .badge-status {
            font-size: 0.75rem;
        }
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="bi bi-people-fill"></i> Gestión de Usuarios</h1>
                <p class="mb-0">Control de clientes registrados en la plataforma</p>
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
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form method="get" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Buscar por nombre, email o cédula..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <?php if ($search): ?>
                            <a href="usuarios.php" class="btn btn-secondary ms-2">
                                <i class="bi bi-x"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <a href="usuario_crear.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Nuevo Usuario
                    </a>
                    <a href="usuario_exportar.php" class="btn btn-secondary">
                        <i class="bi bi-download"></i> Exportar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                Total de clientes registrados: <strong><?php echo $total_usuarios; ?></strong>
                <?php if ($search): ?>
                    | Resultados de búsqueda: <strong><?php echo count($usuarios); ?></strong>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tabla de usuarios -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($usuarios)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="mt-3 text-muted">
                        <?php echo $search ? 'No se encontraron usuarios con ese criterio' : 'No hay usuarios registrados todavía'; ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Información Personal</th>
                                <th>Email</th>
                                <th>Cédula</th>
                                <th>Fecha Registro</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): 
                                $iniciales = strtoupper(substr($usuario['nombres'], 0, 1) . substr($usuario['apellidos'], 0, 1));
                                $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                            ?>
                                <tr>
                                    <td><?php echo $usuario['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="background-color: <?php echo $color; ?>">
                                                <?php echo $iniciales; ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($usuario['username']); ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['cedula']); ?></td>
                                    <td>
                                        <small><?php echo date('d/m/Y H:i', strtotime($usuario['created_at'])); ?></small>
                                    </td>
                                    <td class="text-center table-actions">
                                        <a href="usuario_ver.php?id=<?php echo $usuario['id']; ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="usuario_editar.php?id=<?php echo $usuario['id']; ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button onclick="confirmarEliminar(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']); ?>')" 
                                                class="btn btn-sm btn-danger" 
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Navegación de usuarios" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        Anterior
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        Siguiente
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
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
                <p>¿Estás seguro de que deseas eliminar al usuario <strong id="usuarioNombre"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="btnConfirmarEliminar" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Eliminar Usuario
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmarEliminar(id, nombre) {
    document.getElementById('usuarioNombre').textContent = nombre;
    document.getElementById('btnConfirmarEliminar').href = 'usuarios.php?action=delete&id=' + id;
    new bootstrap.Modal(document.getElementById('modalEliminar')).show();
}
</script>
</body>
</html>
