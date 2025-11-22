<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

$error = '';
$success = '';

// Obtener categorías
$categorias = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

// Obtener imágenes disponibles en la carpeta imagenes
$imagenes_disponibles = [];
$imagenes_dir = '../imagenes/';
if (is_dir($imagenes_dir)) {
    $archivos = scandir($imagenes_dir);
    foreach ($archivos as $archivo) {
        if ($archivo != '.' && $archivo != '..' && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $archivo)) {
            $imagenes_disponibles[] = $archivo;
        }
    }
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    
    // Validaciones
    if (empty($name)) {
        $error = 'El nombre del producto es requerido';
    } elseif (empty($price) || !is_numeric($price) || $price < 0) {
        $error = 'El precio debe ser un número válido mayor o igual a 0';
    } elseif (empty($stock) || !is_numeric($stock) || $stock < 0) {
        $error = 'El stock debe ser un número válido mayor o igual a 0';
    } else {
        // Obtener imagen seleccionada
        $image_path = null;
        if (!empty($_POST['image_select'])) {
            $image_path = 'imagenes/' . $_POST['image_select'];
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare('INSERT INTO products (name, description, price, stock, image, category_id) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$name, $description, $price, $stock, $image_path, $category_id ?: null]);
                
                $_SESSION['message'] = 'Producto creado exitosamente';
                $_SESSION['message_type'] = 'success';
                header('Location: productos.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Error al crear el producto. Por favor intenta nuevamente.';
            }
        }
    }
}

require '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto - ADESHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .image-preview {
            max-width: 300px;
            max-height: 300px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="bi bi-plus-circle"></i> Crear Nuevo Producto</h1>
                <p class="mb-0">Agrega un nuevo producto al inventario</p>
            </div>
            <a href="productos.php" class="btn btn-light">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-body p-4">
                    <form method="post" id="formCrearProducto" novalidate>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">
                                    Nombre del Producto <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                       required
                                       maxlength="255">
                                <div class="invalid-feedback">
                                    Por favor ingresa el nombre del producto.
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="category_id" class="form-label">
                                    Categoría
                                </label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Sin categoría</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($category_id) && $category_id == $cat['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                Descripción
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      maxlength="1000"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                            <small class="form-text text-muted">Describe las características del producto</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">
                                    Precio (USD) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="price" 
                                           name="price" 
                                           value="<?php echo htmlspecialchars($price ?? ''); ?>"
                                           required
                                           min="0"
                                           step="0.01">
                                    <div class="invalid-feedback">
                                        Por favor ingresa un precio válido.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">
                                    Stock/Inventario <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="stock" 
                                       name="stock" 
                                       value="<?php echo htmlspecialchars($stock ?? '0'); ?>"
                                       required
                                       min="0"
                                       step="1">
                                <div class="invalid-feedback">
                                    Por favor ingresa la cantidad en stock.
                                </div>
                                <small class="form-text text-muted">Cantidad disponible para venta</small>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="image_select" class="form-label">
                                Imagen del Producto
                            </label>
                            <select class="form-select" id="image_select" name="image_select" onchange="previewSelectedImage(this)">
                                <option value="">Selecciona una imagen</option>
                                <?php foreach ($imagenes_disponibles as $imagen): ?>
                                    <option value="<?php echo htmlspecialchars($imagen); ?>">
                                        <?php echo htmlspecialchars($imagen); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Selecciona una imagen de la carpeta 'imagenes'</small>
                            <img id="imagePreview" class="image-preview img-fluid rounded mt-2" alt="Vista previa">
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="productos.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-check-circle"></i> Crear Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
    'use strict';
    
    const form = document.getElementById('formCrearProducto');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
})();

function previewSelectedImage(select) {
    const preview = document.getElementById('imagePreview');
    
    if (select.value) {
        preview.src = '../imagenes/' + select.value;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}
</script>
</body>
</html>
