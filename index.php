<?php
require 'config.php';
require 'templates/header.php';

$stmt = $pdo->query('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC');
$products = $stmt->fetchAll();

?>
<h1>Products</h1>
<div class="row">
<?php foreach ($products as $p): ?>
  <div class="col-md-4 mb-4">
    <div class="card">
      <?php if ($p['image']): ?>
      <img src="/<?php echo htmlspecialchars($p['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['name']); ?>">
      <?php endif; ?>
      <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($p['name']); ?></h5>
        <p class="card-text">$<?php echo number_format($p['price'],2); ?></p>
        <a href="product.php?id=<?php echo $p['id']; ?>" class="btn btn-primary">View</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<?php require 'templates/footer.php'; ?>
