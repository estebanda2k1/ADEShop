<?php
/**
 * Helper functions para gestionar el carrito persistente
 */

/**
 * Guardar el carrito de sesión a la base de datos
 */
function saveCartToDatabase($pdo, $user_id, $cart) {
    if (empty($cart)) {
        return;
    }
    
    try {
        // Primero eliminar items antiguos del usuario
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmt->execute([$user_id]);
        
        // Insertar items actuales
        $stmt = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)');
        
        foreach ($cart as $product_id => $item) {
            $stmt->execute([$user_id, $product_id, $item['qty']]);
        }
    } catch (PDOException $e) {
        error_log("Error guardando carrito: " . $e->getMessage());
    }
}

/**
 * Cargar el carrito desde la base de datos
 */
function loadCartFromDatabase($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare('
            SELECT ci.product_id, ci.quantity, 
                   p.name, p.price, p.stock, p.image, 
                   p.is_on_sale, p.sale_price, p.sale_percentage
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.id
            WHERE ci.user_id = ?
        ');
        $stmt->execute([$user_id]);
        $items = $stmt->fetchAll();
        
        $cart = [];
        foreach ($items as $item) {
            // Determinar el precio a usar (con descuento o normal)
            $price = $item['is_on_sale'] && $item['sale_price'] ? $item['sale_price'] : $item['price'];
            
            $cart[$item['product_id']] = [
                'name' => $item['name'],
                'price' => $price,
                'original_price' => $item['price'],
                'qty' => $item['quantity'],
                'image' => $item['image'],
                'stock' => $item['stock'],
                'max_stock' => $item['stock'],
                'is_on_sale' => $item['is_on_sale'],
                'sale_percentage' => $item['sale_percentage']
            ];
        }
        
        return $cart;
    } catch (PDOException $e) {
        error_log("Error cargando carrito: " . $e->getMessage());
        return [];
    }
}

/**
 * Sincronizar carrito: guardar en cada cambio
 */
function syncCart($pdo, $user_id, $cart) {
    saveCartToDatabase($pdo, $user_id, $cart);
}

/**
 * Limpiar carrito de la base de datos
 */
function clearCartFromDatabase($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
        $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        error_log("Error limpiando carrito: " . $e->getMessage());
    }
}
