<?php
require 'config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    header('Location: iniciarSesion.php');
    exit;
}

// Verificar que haya productos en el carrito
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

$error = '';
$success = false;

// Procesar el checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $cart = $_SESSION['cart'];
    
    try {
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Calcular total
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['qty'];
        }
        
        // Crear la orden
        $stmt = $pdo->prepare('INSERT INTO orders (user_id, total, status) VALUES (?, ?, ?)');
        $stmt->execute([$user_id, $total, 'pending']);
        $order_id = $pdo->lastInsertId();
        
        // Insertar items de la orden y actualizar stock
        $stmt_item = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)');
        $stmt_stock = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?');
        
        foreach ($cart as $product_id => $item) {
            // Verificar stock disponible
            $stmt_check = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
            $stmt_check->execute([$product_id]);
            $product = $stmt_check->fetch();
            
            if (!$product || $product['stock'] < $item['qty']) {
                throw new Exception('Stock insuficiente para: ' . $item['name']);
            }
            
            // Insertar item de orden
            $stmt_item->execute([$order_id, $product_id, $item['qty'], $item['price']]);
            
            // Reducir stock
            $stmt_stock->execute([$item['qty'], $product_id, $item['qty']]);
            
            if ($stmt_stock->rowCount() === 0) {
                throw new Exception('Error al actualizar stock para: ' . $item['name']);
            }
        }
        
        // Confirmar transacción
        $pdo->commit();
        
        // Vaciar carrito
        $_SESSION['cart'] = [];
        
        $success = true;
        $order_number = $order_id;
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

require 'templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .checkout-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .order-summary {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success-icon {
            font-size: 5rem;
            color: #28a745;
        }
    </style>
</head>
<body>

<div class="checkout-header">
    <div class="container">
        <h1><i class="bi bi-credit-card"></i> Finalizar Compra</h1>
        <p class="mb-0">Confirma tu pedido</p>
    </div>
</div>

<div class="container mb-5">
    <?php if ($success): ?>
        <!-- Mensaje de éxito -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="order-summary text-center">
                    <i class="bi bi-check-circle success-icon"></i>
                    <h2 class="mt-3">¡Pedido Realizado con Éxito!</h2>
                    <p class="text-muted">Tu orden ha sido procesada correctamente</p>
                    
                    <div class="alert alert-success mt-4">
                        <h5>Número de Orden: <strong>#<?php echo $order_number; ?></strong></h5>
                        <p class="mb-0">Guarda este número para seguimiento de tu pedido</p>
                    </div>
                    
                    <div class="mt-4">
                        <p><i class="bi bi-envelope"></i> Recibirás un correo de confirmación pronto</p>
                        <p><i class="bi bi-truck"></i> Tu pedido será procesado en las próximas 24-48 horas</p>
                    </div>
                    
                    <div class="d-grid gap-2 col-md-6 mx-auto mt-4">
                        <a href="index.php" class="btn btn-primary">
                            <i class="bi bi-shop"></i> Seguir Comprando
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Formulario de checkout -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="order-summary mb-4">
                    <h4 class="mb-3"><i class="bi bi-box-seam"></i> Productos en tu Pedido</h4>
                    
                    <?php 
                    $cart = $_SESSION['cart'];
                    $total = 0;
                    foreach ($cart as $item): 
                        $subtotal = $item['qty'] * $item['price'];
                        $total += $subtotal;
                    ?>
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <?php if ($item['image']): ?>
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px;">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; border-radius: 8px;">
                                    <i class="bi bi-image"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="ms-3 flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">Cantidad: <?php echo $item['qty']; ?> x $<?php echo number_format($item['price'], 2); ?></small>
                            </div>
                            
                            <div>
                                <strong>$<?php echo number_format($subtotal, 2); ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-summary">
                    <h4 class="mb-3"><i class="bi bi-person"></i> Información de Entrega</h4>
                    <p class="text-muted">Tu pedido será enviado a la dirección registrada en tu cuenta</p>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        En esta versión del sistema, la información de envío se gestiona directamente con el cliente.
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="order-summary">
                    <h4 class="mb-4">Resumen del Pedido</h4>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($total, 2); ?></span>
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
                    
                    <form method="post">
                        <button type="submit" class="btn btn-success w-100 mb-3">
                            <i class="bi bi-check-circle"></i> Confirmar Pedido
                        </button>
                    </form>
                    
                    <a href="cart.php" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-left"></i> Volver al Carrito
                    </a>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bi bi-shield-check"></i> Compra 100% segura
                        </small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
