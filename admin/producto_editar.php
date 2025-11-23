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

// Procesar creación de nueva categoría vía AJAX
if (isset($_POST['ajax_create_category']) && !empty($_POST['new_category_name'])) {
    header('Content-Type: application/json');
    $new_category_name = trim($_POST['new_category_name']);
    
    try {
        // Verificar si la categoría ya existe
        $stmt = $pdo->prepare('SELECT id, name FROM categories WHERE name = ?');
        $stmt->execute([$new_category_name]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Si existe, devolver la categoría existente
            echo json_encode([
                'success' => true, 
                'id' => $existing['id'], 
                'name' => $existing['name'],
                'already_exists' => true
            ]);
        } else {
            // Si no existe, crear nueva
            $stmt = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
            $stmt->execute([$new_category_name]);
            $new_id = $pdo->lastInsertId();
            echo json_encode([
                'success' => true, 
                'id' => $new_id, 
                'name' => $new_category_name,
                'already_exists' => false
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al crear la categoría: ' . $e->getMessage()
        ]);
    }
    exit;
}

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
    $is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
    $sale_price = trim($_POST['sale_price'] ?? '');
    $sale_percentage = trim($_POST['sale_percentage'] ?? '');
    
    // Validaciones
    if (empty($name)) {
        $error = 'El nombre del producto es requerido';
    } elseif (empty($price) || !is_numeric($price) || $price < 0) {
        $error = 'El precio debe ser un número válido mayor o igual a 0';
    } elseif (empty($stock) || !is_numeric($stock) || $stock < 0) {
        $error = 'El stock debe ser un número válido mayor o igual a 0';
    } elseif ($is_on_sale && (empty($sale_price) || !is_numeric($sale_price) || $sale_price < 0)) {
        $error = 'Si el producto está en oferta, debes ingresar un precio de oferta válido';
    } elseif ($is_on_sale && $sale_price >= $price) {
        $error = 'El precio de oferta debe ser menor que el precio normal';
    } else {
        // Obtener imagen seleccionada
        $image_path = $producto['image']; // Mantener la imagen actual por defecto
        if (!empty($_POST['image_select'])) {
            $image_path = 'imagenes/' . $_POST['image_select'];
        }
        
        if (empty($error)) {
            try {
                $stmt = $pdo->prepare('UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ?, category_id = ?, is_on_sale = ?, sale_price = ?, sale_percentage = ? WHERE id = ?');
                $stmt->execute([$name, $description, $price, $stock, $image_path, $category_id ?: null, $is_on_sale, $is_on_sale ? $sale_price : null, $is_on_sale && !empty($sale_percentage) ? $sale_percentage : null, $id]);
                
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
    $is_on_sale = $producto['is_on_sale'] ?? 0;
    $sale_price = $producto['sale_price'] ?? '';
    $sale_percentage = $producto['sale_percentage'] ?? '';
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
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-bs-toggle="modal" data-bs-target="#modalCrearCategoria">
                                    <i class="bi bi-plus-circle"></i> Crear nueva categoría
                                </button>
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
                            <label for="image_select" class="form-label">
                                Imagen del Producto
                            </label>
                            
                            <?php if ($producto['image']): ?>
                                <div class="mb-2">
                                    <label class="form-label">Imagen Actual:</label>
                                    <img src="../<?php echo htmlspecialchars($producto['image']); ?>" 
                                         alt="Imagen actual"
                                         class="current-image img-fluid rounded d-block"
                                         style="max-width: 200px;">
                                </div>
                            <?php endif; ?>
                            
                            <select class="form-select" id="image_select" name="image_select" onchange="previewSelectedImage(this)">
                                <option value="">Selecciona una imagen</option>
                                <?php 
                                $current_image = basename($producto['image'] ?? '');
                                foreach ($imagenes_disponibles as $imagen): 
                                ?>
                                    <option value="<?php echo htmlspecialchars($imagen); ?>" <?php echo ($current_image === $imagen) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($imagen); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">Selecciona una imagen de la carpeta 'imagenes'</small>
                            <img id="imagePreview" class="image-preview img-fluid rounded mt-2" alt="Vista previa" style="display: none;">
                        </div>
                        
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-tag-fill"></i> Configuración de Oferta
                                </h5>
                                
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_on_sale" name="is_on_sale" <?php echo $is_on_sale ? 'checked' : ''; ?> onchange="toggleOfferFields()">
                                    <label class="form-check-label" for="is_on_sale">
                                        <strong>Producto en Oferta</strong>
                                    </label>
                                </div>
                                
                                <div id="offerFields" style="display: <?php echo $is_on_sale ? 'block' : 'none'; ?>;">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="sale_price" class="form-label">
                                                Precio de Oferta (USD) <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="sale_price" 
                                                       name="sale_price" 
                                                       value="<?php echo htmlspecialchars($sale_price); ?>"
                                                       min="0"
                                                       step="0.01"
                                                       placeholder="0.00">
                                            </div>
                                            <small class="form-text text-muted">Debe ser menor que el precio normal</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="sale_percentage" class="form-label">
                                                Descuento (%) <span class="text-muted">(Opcional)</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="sale_percentage" 
                                                       name="sale_percentage" 
                                                       value="<?php echo htmlspecialchars($sale_percentage); ?>"
                                                       min="1"
                                                       max="99"
                                                       placeholder="Ej: 20">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <small class="form-text text-muted">Se mostrará como badge de descuento</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

function previewSelectedImage(select) {
    const preview = document.getElementById('imagePreview');
    
    if (select.value) {
        preview.src = '../imagenes/' + select.value;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

function toggleOfferFields() {
    const isOnSale = document.getElementById('is_on_sale').checked;
    const offerFields = document.getElementById('offerFields');
    const salePrice = document.getElementById('sale_price');
    
    if (isOnSale) {
        offerFields.style.display = 'block';
        salePrice.required = true;
    } else {
        offerFields.style.display = 'none';
        salePrice.required = false;
    }
}

// Auto-calcular precio de oferta basado en porcentaje
document.getElementById('sale_percentage')?.addEventListener('input', function() {
    const priceInput = document.getElementById('price');
    const salePriceInput = document.getElementById('sale_price');
    const price = parseFloat(priceInput.value) || 0;
    const percentage = parseFloat(this.value) || 0;
    
    if (price > 0 && percentage > 0 && percentage <= 99) {
        const discount = price * (percentage / 100);
        const salePrice = price - discount;
        salePriceInput.value = salePrice.toFixed(2);
    } else if (percentage === 0 || this.value === '') {
        salePriceInput.value = '';
    }
});

// Auto-calcular porcentaje basado en precio de oferta
document.getElementById('sale_price')?.addEventListener('input', function() {
    const priceInput = document.getElementById('price');
    const percentageInput = document.getElementById('sale_percentage');
    const price = parseFloat(priceInput.value) || 0;
    const salePrice = parseFloat(this.value) || 0;
    
    if (price > 0 && salePrice > 0 && salePrice < price) {
        const discount = price - salePrice;
        const percentage = (discount / price * 100).toFixed(0);
        percentageInput.value = percentage;
    } else if (salePrice === 0 || this.value === '') {
        percentageInput.value = '';
    }
});

// También recalcular cuando cambie el precio normal
document.getElementById('price')?.addEventListener('input', function() {
    const percentageInput = document.getElementById('sale_percentage');
    const percentage = parseFloat(percentageInput.value) || 0;
    
    if (percentage > 0) {
        // Trigger el evento para recalcular
        percentageInput.dispatchEvent(new Event('input'));
    }
});

// Función para crear categoría vía AJAX
function crearCategoria(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const categoryNameInput = document.getElementById('new_category_name');
    const messageDiv = document.getElementById('categoryMessage');
    
    if (!categoryNameInput || !messageDiv) {
        console.error('Elementos del formulario no encontrados');
        return false;
    }
    
    const categoryName = categoryNameInput.value.trim();
    
    if (!categoryName) {
        messageDiv.innerHTML = '<div class="alert alert-danger">El nombre de la categoría es requerido</div>';
        return false;
    }
    
    // Crear FormData
    const formData = new FormData();
    formData.append('ajax_create_category', '1');
    formData.append('new_category_name', categoryName);
    
    // Mostrar indicador de carga
    messageDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Creando categoría...</div>';
    
    // Enviar petición AJAX
    fetch(window.location.href.split('?')[0] + '?id=<?php echo $id; ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor (HTTP ' + response.status + ')');
        }
        return response.text();
    })
    .then(text => {
        console.log('Response text:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                const categorySelect = document.getElementById('category_id');
                
                // Verificar si la categoría ya existe en el select
                let optionExists = false;
                for (let i = 0; i < categorySelect.options.length; i++) {
                    if (categorySelect.options[i].value == data.id) {
                        optionExists = true;
                        categorySelect.options[i].selected = true;
                        break;
                    }
                }
                
                // Si no existe, agregarla
                if (!optionExists) {
                    const newOption = document.createElement('option');
                    newOption.value = data.id;
                    newOption.textContent = data.name;
                    newOption.selected = true;
                    categorySelect.appendChild(newOption);
                }
                
                // Mostrar mensaje apropiado
                if (data.already_exists) {
                    messageDiv.innerHTML = '<div class="alert alert-info">La categoría ya existía. Se ha seleccionado.</div>';
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-success">Categoría creada exitosamente</div>';
                }
                
                // Limpiar el input
                categoryNameInput.value = '';
                
                // Cerrar modal después de 1.5 segundos
                setTimeout(() => {
                    const modalElement = document.getElementById('modalCrearCategoria');
                    if (modalElement) {
                        const modal = bootstrap.Modal.getInstance(modalElement);
                        if (modal) {
                            modal.hide();
                        } else {
                            // Si no existe instancia, crear una y cerrar
                            const newModal = new bootstrap.Modal(modalElement);
                            newModal.hide();
                        }
                    }
                    messageDiv.innerHTML = '';
                }, 1500);
            } else {
                messageDiv.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Error al crear la categoría') + '</div>';
            }
        } catch (e) {
            console.error('Error parsing JSON:', e);
            messageDiv.innerHTML = '<div class="alert alert-danger">Error: Respuesta inválida del servidor. Revisa la consola.</div>';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        messageDiv.innerHTML = '<div class="alert alert-danger">Error de conexión: ' + error.message + '</div>';
    });
    
    return false;
}

// Agregar event listener al botón cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCategoryButton);
} else {
    initCategoryButton();
}

function initCategoryButton() {
    const btn = document.getElementById('btnCrearCategoria');
    if (btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            crearCategoria(e);
        });
    }
}
</script>

<!-- Modal para crear categoría -->
<div class="modal fade" id="modalCrearCategoria" tabindex="-1" data-bs-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formCrearCategoria">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-folder-plus"></i> Crear Nueva Categoría
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="categoryMessage"></div>
                    <div class="mb-3">
                        <label for="new_category_name" class="form-label">Nombre de la Categoría</label>
                        <input type="text" 
                               class="form-control" 
                               id="new_category_name" 
                               name="new_category_name" 
                               required
                               placeholder="Ej: Camisetas, Zapatos, etc.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnCrearCategoria" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Crear Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
