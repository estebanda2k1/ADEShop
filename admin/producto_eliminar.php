<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    
    if ($id) {
        try {
            // Obtener información del producto antes de eliminarlo
            $stmt = $pdo->prepare('SELECT image FROM products WHERE id = ?');
            $stmt->execute([$id]);
            $producto = $stmt->fetch();
            
            if ($producto) {
                // Eliminar el producto
                $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
                $stmt->execute([$id]);
                
                // Eliminar la imagen si existe
                if ($producto['image'] && file_exists('../' . $producto['image'])) {
                    unlink('../' . $producto['image']);
                }
                
                $_SESSION['message'] = 'Producto eliminado exitosamente';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Producto no encontrado';
                $_SESSION['message_type'] = 'danger';
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Error al eliminar el producto. Puede tener órdenes asociadas.';
            $_SESSION['message_type'] = 'danger';
        }
    }
}

header('Location: productos.php');
exit;
