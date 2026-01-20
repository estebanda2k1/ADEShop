<?php
require 'config.php';
require 'templates/header.php';

// Obtener productos de la base de datos
$stmt = $pdo->query('SELECT * FROM products ORDER BY created_at DESC LIMIT 12');
$productos = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADESHOP | Moda Minimalista</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- Estilos personalizados -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Poppins", sans-serif;
            color: #212529;
        }

        header {
            background: #000;
            color: #fff;
            padding: 1rem 0;
            text-align: center;
            letter-spacing: 2px;
        }

        .product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
            background-color: #fff;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .product-card img {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }
        
        .product-card .no-image {
            height: 250px;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stock-low {
            background-color: #dc3545;
            color: white;
        }

        .stock-out {
            background-color: #6c757d;
            color: white;
        }

        .stock-good {
            background-color: #28a745;
            color: white;
        }
        
        .offer-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 700;
            background-color: #dc3545;
            color: white;
            z-index: 10;
        }
        
        .price-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        
        .original-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: normal;
        }
        
        .sale-price {
            color: #dc3545;
            font-size: 1.3rem;
            font-weight: bold;
        }

        footer {
            background-color: #000;
            color: #fff;
            text-align: center;
            padding: 1rem 0;
            margin-top: 2rem;
        }

        .social-icons a {
            color: #fff;
            margin: 0 10px;
            font-size: 1.2rem;
            transition: color 0.3s;
        }

        .social-icons a:hover {
            color: #0d6efd;
        }

        .policy-links a {
            text-decoration: none;
            color: #adb5bd;
            font-size: 0.9rem;
            margin: 0 10px;
        }

        .policy-links a:hover {
            color: #fff;
        }
    </style>
</head>
<body>

<header>
    <h1>ADESHOP</h1>
    <p>Tu estilo, tu identidad.</p>
</header>

<main class="container my-5">
    <h2 class="text-center mb-4 fw-bold">Colección Destacada</h2>

    <div class="row g-4">
        <?php if (empty($productos)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> No hay productos disponibles en este momento.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($productos as $producto): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card position-relative">
                        <?php if ($producto['stock'] == 0): ?>
                            <span class="stock-badge stock-out">
                                <i class="bi bi-x-circle"></i> Agotado
                            </span>
                        <?php elseif ($producto['stock'] <= 5): ?>
                            <span class="stock-badge stock-low">
                                <i class="bi bi-exclamation-triangle"></i> ¡Últimas unidades!
                            </span>
                        <?php elseif ($producto['stock'] <= 10): ?>
                            <span class="stock-badge stock-good">
                                <i class="bi bi-check-circle"></i> Disponible
                            </span>
                        <?php endif; ?>
                        
                        <?php if (isset($producto['is_on_sale']) && $producto['is_on_sale'] && isset($producto['sale_percentage']) && $producto['sale_percentage']): ?>
                            <span class="offer-badge">
                                -<?php echo $producto['sale_percentage']; ?>% OFF
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($producto['image']): ?>
                            <img src="<?php echo htmlspecialchars($producto['image']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($producto['name']); ?>"
                                 onerror="this.style.display='none'; this.insertAdjacentHTML('afterend', '<div class=\'no-image\'><i class=\'bi bi-image\' style=\'font-size: 3rem;\'></i></div>'); this.onerror=null;">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="bi bi-image" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($producto['name']); ?></h5>
                            
                            <?php if (isset($producto['is_on_sale']) && $producto['is_on_sale'] && isset($producto['sale_price']) && $producto['sale_price']): ?>
                                <div class="price-container">
                                    <span class="original-price">$<?php echo number_format($producto['price'], 2); ?></span>
                                    <span class="sale-price">$<?php echo number_format($producto['sale_price'], 2); ?></span>
                                    <small class="text-muted">¡Ahorra $<?php echo number_format($producto['price'] - $producto['sale_price'], 2); ?>!</small>
                                </div>
                            <?php else: ?>
                                <p class="card-text text-success fw-bold">$<?php echo number_format($producto['price'], 2); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($producto['stock'] > 0): ?>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form method="post" action="cart.php" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?php echo $producto['id']; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <button type="submit" class="btn btn-dark w-100">
                                            <i class="bi bi-cart-plus"></i> Agregar al Carrito
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="iniciarSesion.php" class="btn btn-dark w-100">
                                        <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión para Comprar
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="bi bi-x-circle"></i> No Disponible
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<footer>
    <div class="social-icons mb-3">
        <a href="#"><i class="bi bi-facebook"></i></a>
        <a href="#"><i class="bi bi-instagram"></i></a>
        <a href="#"><i class="bi bi-tiktok"></i></a>
    </div>

    <div class="policy-links">
        <a href="#">Política de privacidad</a> |
        <a href="#">Términos de uso</a> |
        <a href="#">Devoluciones</a>
    </div>

    <p class="mt-3 mb-0">&copy; <?php echo date('Y'); ?> ADESHOP. Todos los derechos reservados.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Iconos de Bootstrap -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">


</body>
</html>
