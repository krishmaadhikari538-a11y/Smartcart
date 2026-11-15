<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$productId = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
$product = getProductById($pdo, $productId);

if (!$product) {
    flash('error', 'Invalid product.');
    redirect('products/products.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetPrice = (float)($_POST['target_price'] ?? 0);

    if ($targetPrice <= 0) {
        $errors[] = 'Please enter a valid target price.';
    } elseif ($targetPrice >= $product['price']) {
        $errors[] = 'Target price must be lower than the current price (' . formatPrice($product['price']) . ').';
    }

    if (empty($errors)) {
        // Prevent duplicate active alerts for the same product
        $stmt = $pdo->prepare("SELECT alert_id FROM price_alerts WHERE user_id = ? AND product_id = ? AND status = 'active'");
        $stmt->execute([currentUserId(), $productId]);
        if ($stmt->fetch()) {
            $update = $pdo->prepare("UPDATE price_alerts SET target_price = ? WHERE user_id = ? AND product_id = ? AND status = 'active'");
            $update->execute([$targetPrice, currentUserId(), $productId]);
        } else {
            $insert = $pdo->prepare("INSERT INTO price_alerts (user_id, product_id, target_price, status) VALUES (?, ?, ?, 'active')");
            $insert->execute([currentUserId(), $productId, $targetPrice]);
        }
        flash('success', 'Price alert set! We will notify you when the price drops to or below your target.');
        redirect('customer/price_alerts.php');
    }
}

$pageTitle = 'Set Price Alert';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <div class="card mx-auto" style="max-width:500px;">
    <div class="card-body">
      <h5><i class="fa-solid fa-bell"></i> Set Price Alert</h5>
      <p class="mb-1"><strong><?php echo e($product['product_name']); ?></strong></p>
      <p class="text-muted">Current Price: <?php echo formatPrice($product['price']); ?></p>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
        <div class="mb-3">
          <label class="form-label">Your Target Price (Rs.)</label>
          <input type="number" step="0.01" name="target_price" class="form-control" required min="1" max="<?php echo $product['price'] - 1; ?>">
        </div>
        <button type="submit" class="btn btn-brand w-100">Set Alert</button>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
