<?php
require 'config.php';
require 'cart_helper.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Debes iniciar sesión para acceder al carrito';
    $_SESSION['message_type'] = 'warning';
    header('Location: iniciarSesion.php');
    exit;
}

// Cargar carrito desde la base de datos si no está en sesión
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['cart'] = loadCartFromDatabase($pdo, $_SESSION['user_id']);
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
            $stmt = $pdo->prepare('SELECT id, name, price, stock, image, is_on_sale, sale_price, sale_percentage FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();
            
            if ($product && $product['stock'] > 0) {
                // Determinar el precio a usar (con descuento o normal)
                $price = $product['is_on_sale'] && $product['sale_price'] ? $product['sale_price'] : $product['price'];
                
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
                        'price' => $price,
                        'original_price' => $product['price'],
                        'is_on_sale' => $product['is_on_sale'],
                        'sale_percentage' => $product['sale_percentage'],
                        'image' => $product['image'],
                        'qty' => 1,
                        'max_stock' => $product['stock']
                    ];
                    $message = 'Producto agregado al carrito';
                    $message_type = 'success';
                }
                
                // Sincronizar con la base de datos
                syncCart($pdo, $_SESSION['user_id'], $_SESSION['cart']);
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
                $q = (int)$q;
                
                // Solo eliminar si explícitamente es 0 o negativo
                if ($q <= 0) {
                    unset($_SESSION['cart'][$id]);
                } else {
                    // Verificar stock disponible
                    $stmt = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
                    $stmt->execute([$id]);
                    $product = $stmt->fetch();
                    
                    if ($product) {
                        // Ajustar al stock disponible si es necesario
                        if ($q > $product['stock']) {
                            $_SESSION['cart'][$id]['qty'] = $product['stock'];
                            $_SESSION['cart'][$id]['max_stock'] = $product['stock'];
                            $message = 'Cantidad ajustada al stock disponible';
                            $message_type = 'warning';
                        } else {
                            $_SESSION['cart'][$id]['qty'] = $q;
                            $_SESSION['cart'][$id]['max_stock'] = $product['stock'];
                        }
                    }
                }
            }
        }
        
        // Sincronizar con la base de datos
        syncCart($pdo, $_SESSION['user_id'], $_SESSION['cart']);
        
        $message = 'Carrito actualizado';
        $message_type = 'success';
    } elseif ($action === 'remove') {
        // Remover producto
        $product_id = (int)($_POST['product_id'] ?? 0);
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            
            // Sincronizar con la base de datos
            syncCart($pdo, $_SESSION['user_id'], $_SESSION['cart']);
            
            $message = 'Producto eliminado del carrito';
            $message_type = 'success';
        }
    } elseif ($action === 'clear') {
        // Vaciar carrito
        $_SESSION['cart'] = [];
        
        // Limpiar de la base de datos
        clearCartFromDatabase($pdo, $_SESSION['user_id']);
        
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
        .offer-badge {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: bold;
            margin-left: 5px;
        }
        .price-original {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9rem;
            margin-right: 5px;
        }
        .price-sale {
            color: #dc3545;
            font-weight: bold;
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
                                             alt="<?php echo htmlspecialchars($item['name']); ?>"
                                             onerror="this.style.display='none'; this.insertAdjacentHTML('afterend', '<div class=\"bg-secondary text-white d-flex align-items-center justify-content-center\" style=\"width: 100px; height: 100px; border-radius: 8px;\"><i class=\"bi bi-image\" style=\"font-size: 2rem;\"></i></div>'); this.onerror=null;">
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
                                        <small>Stock disponible: <?php echo $item['max_stock'] ?? $item['stock'] ?? 0; ?></small>
                                    </p>
                                </div>
                                
                                <div class="col-md-2">
                                    <?php if (!empty($item['is_on_sale'])): ?>
                                        <div>
                                            <span class="price-original">$<?php echo number_format($item['original_price'], 2); ?></span>
                                            <span class="offer-badge">-<?php echo $item['sale_percentage']; ?>%</span>
                                        </div>
                                        <p class="mb-0 price-sale">
                                            $<?php echo number_format($item['price'], 2); ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="mb-0 fw-bold text-success">
                                            $<?php echo number_format($item['price'], 2); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-2">
                                    <input type="number" 
                                           name="qty[<?php echo $id; ?>]" 
                                           value="<?php echo $item['qty']; ?>" 
                                           min="1" 
                                           max="<?php echo $item['max_stock'] ?? $item['stock'] ?? 999; ?>"
                                           class="form-control qty-input"
                                           data-price="<?php echo $item['price']; ?>"
                                           data-original-price="<?php echo $item['original_price'] ?? $item['price']; ?>"
                                           data-is-on-sale="<?php echo !empty($item['is_on_sale']) ? '1' : '0'; ?>"
                                           data-item-id="<?php echo $id; ?>">
                                </div>
                                
                                <div class="col-md-2">
                                    <p class="mb-0 fw-bold item-subtotal" data-item-id="<?php echo $id; ?>">
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
                    
                    <?php 
                    $total_savings = 0;
                    foreach ($cart as $item) {
                        if (!empty($item['is_on_sale']) && !empty($item['original_price'])) {
                            $savings = ($item['original_price'] - $item['price']) * $item['qty'];
                            $total_savings += $savings;
                        }
                    }
                    ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span class="fw-bold" id="cart-subtotal">$<?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <?php if ($total_savings > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success" id="savings-row">
                        <span><i class="bi bi-tag-fill"></i> Ahorros:</span>
                        <span class="fw-bold" id="cart-savings">-$<?php echo number_format($total_savings, 2); ?></span>
                    </div>
                    <?php else: ?>
                    <div class="d-flex justify-content-between mb-2 text-success" id="savings-row" style="display: none !important;">
                        <span><i class="bi bi-tag-fill"></i> Ahorros:</span>
                        <span class="fw-bold" id="cart-savings">$0.00</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Envío:</span>
                        <span class="text-success">GRATIS</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5">Total:</span>
                        <span class="h5 text-success" id="cart-total">$<?php echo number_format($total, 2); ?></span>
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
<script>
// Actualización automática del carrito
document.addEventListener('DOMContentLoaded', function() {
    const qtyInputs = document.querySelectorAll('.qty-input');
    let updateTimeout;
    
    // Crear elemento para mostrar mensajes
    const alertContainer = document.createElement('div');
    alertContainer.id = 'alert-container';
    alertContainer.style.position = 'fixed';
    alertContainer.style.top = '20px';
    alertContainer.style.right = '20px';
    alertContainer.style.zIndex = '9999';
    document.body.appendChild(alertContainer);
    
    function showAlert(message, type = 'warning') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 3000);
    }
    
    function updateCartOnServer() {
        const formData = new FormData();
        formData.append('action', 'update');
        
        // Recopilar todas las cantidades válidas
        qtyInputs.forEach(input => {
            const qty = parseInt(input.value);
            if (!isNaN(qty) && qty >= 1) {
                formData.append(`qty[${input.dataset.itemId}]`, qty);
            }
        });
        
        // Enviar vía fetch sin recargar la página
        fetch('cart.php', {
            method: 'POST',
            body: formData
        }).then(response => {
            if (response.ok) {
                console.log('Carrito actualizado en el servidor');
            }
        }).catch(error => {
            console.error('Error al actualizar carrito:', error);
        });
    }
    
    qtyInputs.forEach(input => {
        // Guardar el valor anterior
        let previousValue = input.value;
        
        input.addEventListener('input', function() {
            const itemId = this.dataset.itemId;
            const price = parseFloat(this.dataset.price);
            let qty = parseInt(this.value);
            const maxStock = parseInt(this.max);
            const itemName = this.closest('.cart-item').querySelector('h5').textContent;
            
            // Si el campo está vacío o el valor no es válido, no hacer nada aún
            if (this.value === '' || isNaN(qty)) {
                return;
            }
            
            // Validar cantidad máxima
            if (qty > maxStock) {
                this.value = maxStock;
                showAlert(`⚠️ Solo hay ${maxStock} unidades disponibles de "${itemName}"`, 'warning');
                qty = maxStock;
            }
            
            // Validar cantidad mínima
            if (qty < 1) {
                this.value = 1;
                qty = 1;
            }
            
            // Guardar el valor válido
            previousValue = qty;
            
            // Actualizar subtotal del item
            const subtotal = price * qty;
            const subtotalElement = document.querySelector(`.item-subtotal[data-item-id="${itemId}"]`);
            if (subtotalElement) {
                subtotalElement.textContent = '$' + subtotal.toFixed(2);
            }
            
            // Recalcular totales
            updateCartTotals();
            
            // Guardar cambios en el servidor después de 1.5 segundos sin cambios
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(() => {
                updateCartOnServer();
            }, 1500);
        });
        
        // Validar al perder el foco
        input.addEventListener('blur', function() {
            if (this.value === '' || isNaN(parseInt(this.value)) || parseInt(this.value) < 1) {
                this.value = previousValue || 1;
                // Disparar evento input para actualizar los cálculos
                this.dispatchEvent(new Event('input'));
            }
        });
        
        // Guardar valor original
        let originalValue = input.value;
        
        // Prevenir que las flechas del teclado cambien el valor más allá del límite
        input.addEventListener('keydown', function(e) {
            const currentValue = parseInt(this.value) || 0;
            const maxStock = parseInt(this.max);
            
            if (e.key === 'ArrowUp' && currentValue >= maxStock) {
                e.preventDefault();
                const itemName = this.closest('.cart-item').querySelector('h5').textContent;
                showAlert(`⚠️ Stock insuficiente. Solo hay ${maxStock} unidades disponibles de "${itemName}"`, 'warning');
            }
            
            if (e.key === 'ArrowDown' && currentValue <= 1) {
                e.preventDefault();
            }
        });
        
        // Interceptar clics en el área del input para detectar botones spinner
        input.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const width = rect.width;
            
            // Los botones spinner están típicamente en los últimos 20px del input
            if (clickX > width - 20) {
                setTimeout(() => {
                    const currentValue = parseInt(this.value);
                    const maxStock = parseInt(this.max);
                    const previousValue = parseInt(originalValue);
                    
                    // Si se intentó incrementar y está en el máximo
                    if (currentValue >= maxStock && currentValue > previousValue) {
                        this.value = maxStock;
                        const itemName = this.closest('.cart-item').querySelector('h5').textContent;
                        showAlert(`⚠️ Stock insuficiente. Solo hay ${maxStock} unidades disponibles de "${itemName}"`, 'warning');
                    }
                    
                    originalValue = this.value;
                }, 10);
            }
        });
        
        // Monitorear cambios del valor para detectar clicks en spinner buttons
        let checkInterval;
        input.addEventListener('mousedown', function(e) {
            const rect = this.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const width = rect.width;
            
            // Si el click es en la zona de los botones spinner (últimos 20px)
            if (clickX > width - 20) {
                const startValue = parseInt(this.value);
                const maxStock = parseInt(this.max);
                const inputElement = this;
                
                checkInterval = setInterval(() => {
                    const newValue = parseInt(inputElement.value);
                    
                    // Detectar intento de incremento más allá del stock
                    if (newValue > maxStock) {
                        inputElement.value = maxStock;
                        const itemName = inputElement.closest('.cart-item').querySelector('h5').textContent;
                        showAlert(`⚠️ Stock insuficiente. Solo hay ${maxStock} unidades disponibles de "${itemName}"`, 'warning');
                        clearInterval(checkInterval);
                        // Disparar evento input para actualizar cálculos
                        inputElement.dispatchEvent(new Event('input'));
                    }
                    
                    if (newValue >= maxStock) {
                        clearInterval(checkInterval);
                    }
                }, 50);
            }
        });
        
        input.addEventListener('mouseup', function() {
            if (checkInterval) {
                clearInterval(checkInterval);
            }
        });
        
        input.addEventListener('mouseleave', function() {
            if (checkInterval) {
                clearInterval(checkInterval);
            }
        });
    });
    
    function updateCartTotals() {
        let total = 0;
        let totalSavings = 0;
        
        // Recorrer cada input para calcular totales
        qtyInputs.forEach(input => {
            const qty = parseInt(input.value) || 0;
            const price = parseFloat(input.dataset.price);
            const originalPrice = parseFloat(input.dataset.originalPrice);
            const isOnSale = input.dataset.isOnSale === '1';
            
            // Calcular subtotal
            total += price * qty;
            
            // Calcular ahorros si está en oferta
            if (isOnSale && originalPrice > price) {
                totalSavings += (originalPrice - price) * qty;
            }
        });
        
        // Actualizar subtotal y total
        const subtotalElement = document.getElementById('cart-subtotal');
        const totalElement = document.getElementById('cart-total');
        const savingsElement = document.getElementById('cart-savings');
        const savingsRow = document.getElementById('savings-row');
        
        if (subtotalElement) {
            subtotalElement.textContent = '$' + total.toFixed(2);
        }
        
        if (totalElement) {
            totalElement.textContent = '$' + total.toFixed(2);
        }
        
        // Mostrar u ocultar la fila de ahorros
        if (savingsElement && savingsRow) {
            if (totalSavings > 0) {
                savingsElement.textContent = '-$' + totalSavings.toFixed(2);
                savingsRow.style.display = 'flex';
            } else {
                savingsRow.style.display = 'none';
            }
        }
    }
});
</script>
</body>
</html>
