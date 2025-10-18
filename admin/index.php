<?php
require '../config.php';
// Simple auth check
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: login.php'); exit;
}
require '../templates/header.php';
?>
<h1>Admin Dashboard</h1>
<p><a href="products.php">Manage Products</a></p>
<p><a href="logout.php">Logout</a></p>
<?php require '../templates/footer.php'; ?>
