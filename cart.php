<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // update quantities or remove
    if (isset($_POST['update']) && isset($_POST['qty']) && is_array($_POST['qty'])) {
        foreach ($_POST['qty'] as $id => $q) {
            $id = (int)$id; $q = max(0, (int)$q);
            if ($q === 0) { unset($_SESSION['cart'][$id]); } else { $_SESSION['cart'][$id]['qty'] = $q; }
        }
    }
    header('Location: cart.php'); exit;
}

require 'templates/header.php';
$cart = $_SESSION['cart'] ?? [];
?>
<h1>Your Cart</h1>
<?php if (empty($cart)): ?>
  <p>Your cart is empty. <a href="index.php">Continue shopping</a></p>
<?php else: ?>
  <form method="post">
  <table class="table">
    <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
    <tbody>
    <?php $total=0; foreach ($cart as $id => $item): $subtotal = $item['qty']*$item['price']; $total += $subtotal; ?>
      <tr>
        <td><?php echo htmlspecialchars($item['name']); ?></td>
        <td>$<?php echo number_format($item['price'],2); ?></td>
        <td><input type="number" name="qty[<?php echo $id; ?>]" value="<?php echo $item['qty']; ?>" min="0" style="width:80px;"></td>
        <td>$<?php echo number_format($subtotal,2); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <p class="h4">Total: $<?php echo number_format($total,2); ?></p>
  <button name="update" class="btn btn-primary">Update Cart</button>
  <a href="index.php" class="btn btn-link">Continue shopping</a>
  </form>
<?php endif; ?>

<?php require 'templates/footer.php'; ?>
