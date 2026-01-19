<?php
require 'config.php';
require 'cart_helper.php';

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
    $payment_method = $_POST['payment_method'] ?? '';
    
    // Validar método de pago
    $valid_payment_methods = ['credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash'];
    if (!in_array($payment_method, $valid_payment_methods)) {
        $error = 'Por favor selecciona un método de pago válido';
    } else {
        try {
            // Iniciar transacción
            $pdo->beginTransaction();
            
            // Calcular total
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['price'] * $item['qty'];
            }
            
            // Preparar detalles del pago según el método
            $payment_details = [];
            if ($payment_method === 'credit_card' || $payment_method === 'debit_card') {
                $payment_details = [
                    'card_last_four' => substr($_POST['card_number'] ?? '', -4),
                    'card_holder' => $_POST['card_holder'] ?? '',
                ];
            } elseif ($payment_method === 'paypal') {
                $payment_details = [
                    'paypal_email' => $_POST['paypal_email'] ?? '',
                ];
            } elseif ($payment_method === 'bank_transfer') {
                $payment_details = [
                    'bank_name' => $_POST['bank_name'] ?? '',
                    'account_number' => substr($_POST['account_number'] ?? '', -4),
                ];
            }
            
            // Crear la orden con información de pago
            $stmt = $pdo->prepare('INSERT INTO orders (user_id, total, status, payment_method, payment_status, payment_details) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $user_id, 
                $total, 
                'pending', 
                $payment_method, 
                'pending',
                json_encode($payment_details)
            ]);
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
            
            // Vaciar carrito de sesión y base de datos
            $_SESSION['cart'] = [];
            clearCartFromDatabase($pdo, $_SESSION['user_id']);
            
            $success = true;
            $order_number = $order_id;
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $pdo->rollBack();
            $error = $e->getMessage();
        }
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
        .payment-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-option:hover {
            border-color: #667eea;
            background-color: #f8f9ff;
        }
        .payment-option.active {
            border-color: #667eea;
            background-color: #f0f2ff;
        }
        .payment-option input[type="radio"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        .payment-icon {
            font-size: 2rem;
            margin-right: 1rem;
        }
        .payment-details {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .payment-details.active {
            display: block;
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
                    <h4 class="mb-3"><i class="bi bi-credit-card-2-front"></i> Método de Pago</h4>
                    <p class="text-muted mb-4">Selecciona tu forma de pago preferida</p>
                    
                    <form method="post" id="checkoutForm">
                        <!-- Tarjeta de Crédito -->
                        <div class="payment-option" data-payment="credit_card">
                            <div class="d-flex align-items-center">
                                <input type="radio" name="payment_method" value="credit_card" id="credit_card" required>
                                <i class="bi bi-credit-card payment-icon text-primary"></i>
                                <div>
                                    <h6 class="mb-0">Tarjeta de Crédito</h6>
                                    <small class="text-muted">Visa, Mastercard, American Express</small>
                                </div>
                            </div>
                            <div class="payment-details" id="credit_card_details">
                                <div class="mb-3">
                                    <label class="form-label">Número de Tarjeta</label>
                                    <input type="text" class="form-control" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Nombre en la Tarjeta</label>
                                        <input type="text" class="form-control" name="card_holder" placeholder="JUAN PÉREZ">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" name="card_cvv" placeholder="123" maxlength="4">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label">Mes</label>
                                        <select class="form-select" name="card_month">
                                            <option value="">Mes</option>
                                            <?php for($i = 1; $i <= 12; $i++): ?>
                                                <option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">Año</label>
                                        <select class="form-select" name="card_year">
                                            <option value="">Año</option>
                                            <?php for($i = 2026; $i <= 2036; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta de Débito -->
                        <div class="payment-option" data-payment="debit_card">
                            <div class="d-flex align-items-center">
                                <input type="radio" name="payment_method" value="debit_card" id="debit_card" required>
                                <i class="bi bi-credit-card payment-icon text-success"></i>
                                <div>
                                    <h6 class="mb-0">Tarjeta de Débito</h6>
                                    <small class="text-muted">Pago directo desde tu cuenta</small>
                                </div>
                            </div>
                            <div class="payment-details" id="debit_card_details">
                                <div class="mb-3">
                                    <label class="form-label">Número de Tarjeta</label>
                                    <input type="text" class="form-control" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                                </div>
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">Nombre en la Tarjeta</label>
                                        <input type="text" class="form-control" name="card_holder" placeholder="JUAN PÉREZ">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" name="card_cvv" placeholder="123" maxlength="4">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PayPal -->
                        <div class="payment-option" data-payment="paypal">
                            <div class="d-flex align-items-center">
                                <input type="radio" name="payment_method" value="paypal" id="paypal" required>
                                <i class="bi bi-paypal payment-icon" style="color: #003087;"></i>
                                <div>
                                    <h6 class="mb-0">PayPal</h6>
                                    <small class="text-muted">Paga de forma rápida y segura</small>
                                </div>
                            </div>
                            <div class="payment-details" id="paypal_details">
                                <div class="mb-3">
                                    <label class="form-label">Correo de PayPal</label>
                                    <input type="email" class="form-control" name="paypal_email" placeholder="tu@email.com">
                                </div>
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle"></i> Serás redirigido a PayPal para completar el pago
                                </div>
                            </div>
                        </div>

                        <!-- Transferencia Bancaria -->
                        <div class="payment-option" data-payment="bank_transfer">
                            <div class="d-flex align-items-center">
                                <input type="radio" name="payment_method" value="bank_transfer" id="bank_transfer" required>
                                <i class="bi bi-bank payment-icon text-info"></i>
                                <div>
                                    <h6 class="mb-0">Transferencia Bancaria</h6>
                                    <small class="text-muted">Transferencia directa a nuestra cuenta</small>
                                </div>
                            </div>
                            <div class="payment-details" id="bank_transfer_details">
                                <div class="mb-3">
                                    <label class="form-label">Banco</label>
                                    <select class="form-select" name="bank_name">
                                        <option value="">Selecciona tu banco</option>
                                        <option value="Banco Pichincha">Banco Pichincha</option>
                                        <option value="Banco Guayaquil">Banco Guayaquil</option>
                                        <option value="Banco del Pacífico">Banco del Pacífico</option>
                                        <option value="Produbanco">Produbanco</option>
                                        <option value="Banco Internacional">Banco Internacional</option>
                                        <option value="Banco Bolivariano">Banco Bolivariano</option>
                                        <option value="Banco del Austro">Banco del Austro</option>
                                        <option value="Banco Solidario">Banco Solidario</option>
                                        <option value="Banco Machala">Banco Machala</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Número de Cuenta (opcional)</label>
                                    <input type="text" class="form-control" name="account_number" placeholder="Ej: 0123456789">
                                </div>
                                <div class="alert alert-warning mb-0">
                                    <i class="bi bi-exclamation-triangle"></i> El pedido será procesado una vez confirmemos el pago (24-48 horas)
                                </div>
                            </div>
                        </div>

                        <!-- Efectivo -->
                        <div class="payment-option" data-payment="cash">
                            <div class="d-flex align-items-center">
                                <input type="radio" name="payment_method" value="cash" id="cash" required>
                                <i class="bi bi-cash-coin payment-icon text-warning"></i>
                                <div>
                                    <h6 class="mb-0">Pago en Efectivo</h6>
                                    <small class="text-muted">Paga al recibir tu pedido</small>
                                </div>
                            </div>
                            <div class="payment-details" id="cash_details">
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle"></i> Pagarás en efectivo cuando recibas tu pedido
                                </div>
                            </div>
                        </div>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentOptions = document.querySelectorAll('.payment-option');
    const form = document.getElementById('checkoutForm');
    
    // Manejar selección de método de pago
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remover clase active de todas las opciones
            paymentOptions.forEach(opt => {
                opt.classList.remove('active');
                const details = opt.querySelector('.payment-details');
                if (details) details.classList.remove('active');
            });
            
            // Agregar clase active a la opción seleccionada
            this.classList.add('active');
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            
            // Mostrar detalles de pago
            const details = this.querySelector('.payment-details');
            if (details) details.classList.add('active');
        });
    });
    
    // Validación del formulario
    form.addEventListener('submit', function(e) {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        
        if (!selectedPayment) {
            e.preventDefault();
            alert('Por favor selecciona un método de pago');
            return false;
        }
        
        const paymentMethod = selectedPayment.value;
        
        // Buscar el contenedor activo del método de pago seleccionado
        const activePaymentOption = document.querySelector('.payment-option.active');
        
        // Validar campos según método de pago
        if (paymentMethod === 'credit_card' || paymentMethod === 'debit_card') {
            // Buscar campos solo dentro del contenedor activo
            const cardNumber = activePaymentOption.querySelector('input[name="card_number"]');
            const cardHolder = activePaymentOption.querySelector('input[name="card_holder"]');
            
            if (!cardNumber || !cardNumber.value.trim() || !cardHolder || !cardHolder.value.trim()) {
                e.preventDefault();
                alert('Por favor completa todos los campos de la tarjeta');
                return false;
            }
        } else if (paymentMethod === 'paypal') {
            const paypalEmail = activePaymentOption.querySelector('input[name="paypal_email"]');
            
            if (!paypalEmail || !paypalEmail.value.trim()) {
                e.preventDefault();
                alert('Por favor ingresa tu correo de PayPal');
                return false;
            }
        } else if (paymentMethod === 'bank_transfer') {
            const bankName = activePaymentOption.querySelector('select[name="bank_name"]');
            
            if (!bankName || !bankName.value) {
                e.preventDefault();
                alert('Por favor selecciona tu banco');
                return false;
            }
        }
        
        return true;
    });
    
    // Formatear número de tarjeta
    const cardInputs = document.querySelectorAll('input[name="card_number"]');
    cardInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });
    });
});
</script>
</body>
</html>
