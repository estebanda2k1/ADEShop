<?php
require 'config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Debes iniciar sesión para acceder al carrito';
    $_SESSION['message_type'] = 'warning';
    header('Location: iniciarSesion.php');
    exit;
}

// Inicializar carrito si no existe
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
$message_type = '';

// Procesar acciones del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Agregar producto al carrito
        $product_id = (int)($_POST['product_id'] ?? 0);
        
        if ($product_id > 0) {
            // Obtener producto de la base de datos
            $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product && $product['stock'] > 0) {
                // Verificar si ya está en el carrito
                if (isset($_SESSION['cart'][$product_id])) {
                    // Verificar que no exceda el stock
                    if ($_SESSION['cart'][$product_id]['qty'] < $product['stock']) {
                        $_SESSION['cart'][$product_id]['qty']++;
                        $message = 'Cantidad actualizada en el carrito';
                        $message_type = 'success';
                    } else {
                        $message = 'No hay suficiente stock disponible';
                        $message_type = 'warning';
                    }
                } else {
                    // Agregar nuevo producto
                    $_SESSION['cart'][$product_id] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'image' => $product['image'],
                        'qty' => 1,
                        'max_stock' => $product['stock']
                    ];
                    $message = 'Producto agregado al carrito';
                    $message_type = 'success';
                }
            } else {
                $message = 'Producto no disponible';
                $message_type = 'danger';
            }
        }
    } elseif ($action === 'update') {
        // Actualizar cantidades
        if (isset($_POST['qty']) && is_array($_POST['qty'])) {
            foreach ($_POST['qty'] as $id => $q) {
                $id = (int)$id;
                $q = max(0, (int)$q);
                
                if ($q === 0) {
                    unset($_SESSION['cart'][$id]);
                } else {
                    // Verificar stock disponible
                    $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
                    $stmt->execute([$id]);
                    $product = $stmt->fetch();
                    
                    if ($product && $q <= $product['stock']) {
                        $_SESSION['cart'][$id]['qty'] = $q;
                        $_SESSION['cart'][$id]['max_stock'] = $product['stock'];
                    } else {
                        $message = 'Cantidad ajustada al stock disponible';
                        $message_type = 'warning';
                        $_SESSION['cart'][$id]['qty'] = $product['stock'];
                    }
                }
            }
        }
        $message = 'Carrito actualizado';
        $message_type = 'success';
    } elseif ($action === 'remove') {
        // Remover producto
        $product_id = (int)($_POST['product_id'] ?? 0);
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            $message = 'Producto eliminado del carrito';
            $message_type = 'success';
        }
    } elseif ($action === 'clear') {
        // Vaciar carrito
        $_SESSION['cart'] = [];
        $message = 'Carrito vaciado';
        $message_type = 'success';
    }
    
    header('Location: cart.php');
    exit;
}

require 'templates/header.php';
$cart = $_SESSION['cart'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .cart-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .cart-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .cart-summary {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
    </style>
</head>
<body>

<div class="cart-header">
    <div class="container">
        <h1><i class="bi bi-cart3"></i> Carrito de Compras</h1>
        <p class="mb-0">Revisa tus productos antes de continuar</p>
    </div>
</div>

<div class="container mb-5">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (empty($cart)): ?>
        <div class="text-center py-5">
            <i class="bi bi-cart-x" style="font-size: 5rem; color: #6c757d;"></i>
            <h3 class="mt-3">Tu carrito está vacío</h3>
            <p class="text-muted">Agrega productos para comenzar a comprar</p>
            <a href="index.php" class="btn btn-primary mt-3">
                <i class="bi bi-shop"></i> Continuar Comprando
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <form method="post" id="cartForm">
                    <input type="hidden" name="action" value="update">
                    
                    <?php 
                    $total = 0;
                    foreach ($cart as $id => $item): 
                        $subtotal = $item['qty'] * $item['price'];
                        $total += $subtotal;
                    ?>
                        <div class="cart-item">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <?php if ($item['image']): ?>
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php else: ?>
                                        <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                             style="width: 100px; height: 100px; border-radius: 8px;">
                                            <i class="bi bi-image" style="font-size: 2rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-4">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="text-muted mb-0">
                                        <small>Stock disponible: <?php echo $item['max_stock']; ?></small>
                                    </p>
                                </div>
                                
                                <div class="col-md-2">
                                    <p class="mb-0 fw-bold text-success">
                                        $<?php echo number_format($item['price'], 2); ?>
                                    </p>
                                </div>
                                
                                <div class="col-md-2">
                                    <input type="number" 
                                           name="qty[<?php echo $id; ?>]" 
                                           value="<?php echo $item['qty']; ?>" 
                                           min="1" 
                                           max="<?php echo $item['max_stock']; ?>"
                                           class="form-control"
                                           onchange="document.getElementById('cartForm').submit();">
                                </div>
                                
                                <div class="col-md-2">
                                    <p class="mb-0 fw-bold">
                                        $<?php echo number_format($subtotal, 2); ?>
                                    </p>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                            <i class="bi bi-trash"></i> Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </form>
                
                <div class="d-flex justify-content-between mt-3">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Continuar Comprando
                    </a>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline-danger" 
                                onclick="return confirm('¿Seguro que deseas vaciar el carrito?');">
                            <i class="bi bi-trash"></i> Vaciar Carrito
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="cart-summary">
                    <h4 class="mb-4">Resumen del Pedido</h4>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span class="fw-bold">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Envío:</span>
                        <span class="text-success">GRATIS</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5">Total:</span>
                        <span class="h5 text-success">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-success w-100 mb-2">
                        <i class="bi bi-credit-card"></i> Proceder al Pago
                    </a>
                    
                    <p class="text-muted text-center mb-0" style="font-size: 0.85rem;">
                        <i class="bi bi-shield-check"></i> Compra 100% segura
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
