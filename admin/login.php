<?php
require '../config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['is_admin'] = (bool)$user['is_admin'];
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php'); exit;
    } else {
        $error = 'Invalid credentials';
    }
}
require '../templates/header.php';
?>
<h1>Admin Login</h1>
<?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="post">
  <div class="mb-3">
    <label>Username</label>
    <input name="username" class="form-control">
  </div>
  <div class="mb-3">
    <label>Password</label>
    <input name="password" type="password" class="form-control">
  </div>
  <button class="btn btn-primary">Login</button>
</form>
<?php require '../templates/footer.php'; ?>
