<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([currentUserId()]);
$orders = $stmt->fetchAll();

$successMsg = flash('success');

$pageTitle = 'My Orders';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-box"></i> My Orders</h4>
  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>

  <?php if (empty($orders)): ?>
    <div class="alert alert-info">You haven't placed any orders yet.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle bg-white">
        <thead><tr><th>Order ID</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td>#<?php echo $o['order_id']; ?></td>
              <td><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
              <td><?php echo formatPrice($o['total_amount']); ?></td>
              <td><?php echo e($o['payment_method']); ?></td>
              <td><span class="badge badge-status-<?php echo $o['order_status']; ?>"><?php echo $o['order_status']; ?></span></td>
              <td><a href="<?php echo BASE_URL; ?>customer/order_details.php?id=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-outline-brand">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
