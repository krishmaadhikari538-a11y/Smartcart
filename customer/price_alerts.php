<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();
$userId = currentUserId();

if (isset($_GET['cancel'])) {
    $stmt = $pdo->prepare("UPDATE price_alerts SET status = 'cancelled' WHERE alert_id = ? AND user_id = ?");
    $stmt->execute([(int)$_GET['cancel'], $userId]);
    flash('success', 'Price alert cancelled.');
    redirect('customer/price_alerts.php');
}

$stmt = $pdo->prepare("SELECT pa.*, p.product_name, p.price, p.image FROM price_alerts pa
                        JOIN products p ON pa.product_id = p.product_id
                        WHERE pa.user_id = ? ORDER BY pa.created_at DESC");
$stmt->execute([$userId]);
$alerts = $stmt->fetchAll();

$successMsg = flash('success');

$pageTitle = 'My Price Alerts';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-bell"></i> My Price Alerts</h4>
  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>

  <?php if (empty($alerts)): ?>
    <div class="alert alert-info">You have no price alerts set. Visit a product page and click "Set Price Alert" to create one.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle bg-white">
        <thead><tr><th>Product</th><th>Current Price</th><th>Target Price</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($alerts as $a): ?>
            <tr>
              <td class="d-flex align-items-center gap-2">
                <img src="<?php echo getProductImageUrl($a['image']); ?>" width="40" class="rounded">
                <a href="<?php echo BASE_URL; ?>products/product_details.php?id=<?php echo $a['product_id']; ?>"><?php echo e($a['product_name']); ?></a>
              </td>
              <td><?php echo formatPrice($a['price']); ?></td>
              <td><?php echo formatPrice($a['target_price']); ?></td>
              <td>
                <?php
                  $badge = ['active' => 'primary', 'notified' => 'success', 'cancelled' => 'secondary'][$a['status']];
                ?>
                <span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($a['status']); ?></span>
              </td>
              <td>
                <?php if ($a['status'] === 'active'): ?>
                  <a href="?cancel=<?php echo $a['alert_id']; ?>" class="btn btn-sm btn-outline-danger confirm-delete">Cancel</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
