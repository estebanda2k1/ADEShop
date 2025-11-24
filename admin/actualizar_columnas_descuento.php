<?php
/**
 * Script para verificar y agregar columnas de descuento a la tabla products
 */

require_once '../config.php';

$cambios = [];
$errores = [];

try {
    // Verificar si existen las columnas
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'is_on_sale'");
    if ($stmt->rowCount() == 0) {
        // Agregar columna is_on_sale
        $pdo->exec("ALTER TABLE products ADD COLUMN is_on_sale TINYINT(1) DEFAULT 0 COMMENT 'Indica si el producto está en oferta'");
        $cambios[] = "Columna 'is_on_sale' agregada correctamente";
    } else {
        $cambios[] = "Columna 'is_on_sale' ya existe";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'sale_price'");
    if ($stmt->rowCount() == 0) {
        // Agregar columna sale_price
        $pdo->exec("ALTER TABLE products ADD COLUMN sale_price DECIMAL(10,2) NULL COMMENT 'Precio con descuento cuando está en oferta'");
        $cambios[] = "Columna 'sale_price' agregada correctamente";
    } else {
        $cambios[] = "Columna 'sale_price' ya existe";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'sale_percentage'");
    if ($stmt->rowCount() == 0) {
        // Agregar columna sale_percentage
        $pdo->exec("ALTER TABLE products ADD COLUMN sale_percentage INT NULL COMMENT 'Porcentaje de descuento (opcional)'");
        $cambios[] = "Columna 'sale_percentage' agregada correctamente";
    } else {
        $cambios[] = "Columna 'sale_percentage' ya existe";
    }

    $success = true;

} catch (PDOException $e) {
    $success = false;
    $errores[] = "Error: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Tabla Products - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-<?php echo $success ? 'success' : 'danger'; ?> text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-<?php echo $success ? 'check-circle' : 'x-circle'; ?>"></i>
                            Actualización de Base de Datos
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> 
                                <strong>¡Actualización completada!</strong>
                            </div>
                            
                            <h5>Cambios realizados:</h5>
                            <ul class="list-group mb-3">
                                <?php foreach ($cambios as $cambio): ?>
                                    <li class="list-group-item">
                                        <i class="bi bi-check text-success"></i> <?php echo htmlspecialchars($cambio); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Estructura actualizada de la tabla 'products':</h6>
                                    <ul>
                                        <li><code>is_on_sale</code> - TINYINT(1) - Indica si está en oferta</li>
                                        <li><code>sale_price</code> - DECIMAL(10,2) - Precio con descuento</li>
                                        <li><code>sale_percentage</code> - INT - Porcentaje de descuento</li>
                                    </ul>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-x-circle"></i> 
                                <strong>Error al actualizar</strong>
                            </div>
                            
                            <h5>Errores encontrados:</h5>
                            <ul class="list-group mb-3">
                                <?php foreach ($errores as $error): ?>
                                    <li class="list-group-item list-group-item-danger">
                                        <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 mt-4">
                            <a href="productos.php" class="btn btn-primary">
                                <i class="bi bi-box-seam"></i> Ir a Gestión de Productos
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="bi bi-speedometer2"></i> Ir al Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-info-circle text-info"></i> Información
                        </h6>
                        <p class="card-text small text-muted mb-0">
                            Este script verifica y agrega automáticamente las columnas necesarias para el sistema de descuentos. 
                            Puedes ejecutarlo múltiples veces sin problema, no duplicará las columnas.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
