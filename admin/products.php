<?php
require '../config.php';
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) { header('Location: login.php'); exit; }
require '../templates/header.php';

$stmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id');
$products = $stmt->fetchAll();
?>
<h1>Products</h1>
<p><a href="index.php">Back to dashboard</a></p>
<table class="table">
  <thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th></tr></thead>
  <tbody>
  <?php foreach ($products as $p): ?>
    <tr>
      <td><?php echo $p['id']; ?></td>
      <td><?php echo htmlspecialchars($p['name']); ?></td>
      <td>$<?php echo number_format($p['price'],2); ?></td>
      <td><?php echo htmlspecialchars($p['category_name']); ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<?php require '../templates/footer.php'; ?>
