<?php
require 'templates/header.php';

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADESHOP | Moda Minimalista</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .product-card img {
            height: 250px;
            object-fit: cover;
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

        <!-- Caja de producto 1 -->
        <div class="col-md-3 col-sm-6">
            <div class="card product-card">
                <img src="imagenes/Producto1.png" class="card-img-top" alt="Producto 1">
                <div class="card-body text-center">
                    <h5 class="card-title">Camiseta Básica</h5>
                    <p class="card-text text-muted">$19.99</p>
                    <button class="btn btn-dark w-100">Ver más</button>
                </div>
            </div>
        </div>

        <!-- Caja de producto 2 -->
        <div class="col-md-3 col-sm-6">
            <div class="card product-card">
                <img src="imagenes/Producto2.png" class="card-img-top" alt="Producto 2">
                <div class="card-body text-center">
                    <h5 class="card-title">Jeans Clásicos</h5>
                    <p class="card-text text-muted">$29.99</p>
                    <button class="btn btn-dark w-100">Ver más</button>
                </div>
            </div>
        </div>

        <!-- Caja de producto 3 -->
        <div class="col-md-3 col-sm-6">
            <div class="card product-card">
                <img src="imagenes/Producto3.png" class="card-img-top" alt="Producto 3">
                <div class="card-body text-center">
                    <h5 class="card-title">Chaqueta Minimal</h5>
                    <p class="card-text text-muted">$49.99</p>
                    <button class="btn btn-dark w-100">Ver más</button>
                </div>
            </div>
        </div>

        <!-- Caja de producto 4 -->
        <div class="col-md-3 col-sm-6">
            <div class="card product-card">
                <img src="imagenes/Producto4.png" class="card-img-top" alt="Producto 4">
                <div class="card-body text-center">
                    <h5 class="card-title">Zapatillas Urbanas</h5>
                    <p class="card-text text-muted">$59.99</p>
                    <button class="btn btn-dark w-100">Ver más</button>
                </div>
            </div>
        </div>
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
