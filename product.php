<?php
require 'config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: index.php'); exit;
}

$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    if (!isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] = ['qty'=>$qty, 'name'=>$product['name'], 'price'=>$product['price']];
    } else {
        $_SESSION['cart'][$id]['qty'] += $qty;
    }
    header('Location: cart.php'); exit;
}

require 'templates/header.php';
?>
<div class="row">
  <div class="col-md-6">
    <?php if ($product['image']): ?>
      <img src="/<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>">
    <?php endif; ?>
  </div>
  <div class="col-md-6">
    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
    <p class="h4">$<?php echo number_format($product['price'],2); ?></p>
    <form method="post">
      <div class="mb-3">
        <label>Quantity</label>
        <input type="number" name="qty" value="1" min="1" class="form-control" style="width:100px;">
      </div>
      <button class="btn btn-success">Add to cart</button>
    </form>
  </div>
</div>

<?php require 'templates/footer.php'; ?>
