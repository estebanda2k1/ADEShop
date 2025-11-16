<?php
// Incluir config.php desde la raíz del proyecto
if (!isset($pdo)) {
    require_once __DIR__ . '/../config.php';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ADESHOP Mini Marketplace</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .navbar-brand img {
        height: 40px;
        margin-right: 8px;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
    <div class="container">
      <a class="navbar-brand" href="/ADESHOP">
        <img src="/ADESHOP/imagenes/logo.png" alt="Logo"> ADESHOP
      </a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item"><a class="nav-link" href="/ADESHOP/cart.php">Cart</a></li>
          
          <?php if (!isset($_SESSION['user_id'])): ?>
              <li class="nav-item"><a class="nav-link" href="/ADESHOP/iniciarSesion.php">Iniciar Sesión</a></li>
          <?php else: ?>
              <li class="nav-item">
                <span class="navbar-text me-2 text-white">
                  <?= htmlspecialchars($_SESSION['user_name']) ?>
                </span>
              </li>
              <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                  <li class="nav-item">
                    <a class="nav-link" href="/ADESHOP/dashboard.php">Ver Dashboard</a>
                  </li>
              <?php endif; ?>
              <li class="nav-item">
                <a class="nav-link" href="/ADESHOP/logout.php">Cerrar Sesión</a>
              </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
