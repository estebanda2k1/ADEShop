<?php
require_once 'config.php';

// Destruir la sesión
session_unset();
session_destroy();

// Redirigir a la página principal
header("Location: /ADESHOP/index.php");
exit;
?>