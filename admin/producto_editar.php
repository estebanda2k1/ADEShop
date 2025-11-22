<?php
require_once '../config.php';

// Verificar que sea administrador
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: productos.php');
    exit;
}

$error = '';
$success = '';

// Obtener producto
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    $_SESSION['message'] = 'Producto no encontrado';
    $_SESSION['message_type'] = 'danger';
    header('Location: productos.php');
    exit;
}

// Obtener categorías
$categorias = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

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
        // Manejo de imagen
        $image_path = $producto['image']; // Mantener la imagen actual por defecto
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Solo se permiten imágenes (JPG, PNG, GIF, WEBP)';
            } elseif ($_FILES['image']['size'] > 5000000) { // 5MB
                $error = 'La imagen no debe superar 5MB';
            } else {
                $upload_dir = '../assets/images/products/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Eliminar imagen anterior si existe
                    if ($producto['image'] && file_exists('../' . $producto['image'])) {
                        unlink('../' . $producto['image']);
                    }
                    $image_path = 'assets/images/products/' . $file_name;
                } else {
                    $error = 'Error al subir la imagen';
                }
            }
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ?, category_id = ? WHERE id = ?');
                $stmt->execute([$name, $description, $price, $stock, $image_path, $category_id ?: null, $id]);
                
                $_SESSION['message'] = 'Producto actualizado exitosamente';
                $_SESSION['message_type'] = 'success';
                header('Location: productos.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Error al actualizar el producto. Por favor intenta nuevamente.';
            }
        }
    }
} else {
    // Cargar datos actuales del producto
    $name = $producto['name'];
    $description = $producto['description'];
    $price = $producto['price'];
    $stock = $producto['stock'];
    $category_id = $producto['category_id'];
}

require '../templates/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - ADESHOP</title>
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
        }
        .current-image {
            max-width: 200px;
            max-height: 200px;
        }
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><i class="bi bi-pencil-square"></i> Editar Producto</h1>
                <p class="mb-0">Modifica la información del producto</p>
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
                    <form method="post" enctype="multipart/form-data" id="formEditarProducto" novalidate>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">
                                    Nombre del Producto <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo htmlspecialchars($name); ?>"
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
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id == $cat['id']) ? 'selected' : ''; ?>>
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
                                      maxlength="1000"><?php echo htmlspecialchars($description); ?></textarea>
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
                                           value="<?php echo htmlspecialchars($price); ?>"
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
                                       value="<?php echo htmlspecialchars($stock); ?>"
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
                            <label class="form-label">Imagen Actual</label>
                            <?php if ($producto['image']): ?>
                                <div class="mb-2">
                                    <img src="../<?php echo htmlspecialchars($producto['image']); ?>" 
                                         alt="Imagen actual"
                                         class="current-image img-fluid rounded">
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No hay imagen cargada</p>
                            <?php endif; ?>
                            
                            <label for="image" class="form-label mt-2">
                                Cambiar Imagen (opcional)
                            </label>
                            <input type="file" 
                                   class="form-control" 
                                   id="image" 
                                   name="image"
                                   accept="image/*"
                                   onchange="previewImage(this)">
                            <small class="form-text text-muted">Deja vacío para mantener la imagen actual. Formatos: JPG, PNG, GIF, WEBP. Máximo 5MB.</small>
                            <img id="imagePreview" class="image-preview img-fluid rounded mt-2" style="display: none;" alt="Vista previa">
                        </div>
                        
                        <hr>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="productos.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
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
    
    const form = document.getElementById('formEditarProducto');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    }, false);
})();

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>
</body>
</html>
