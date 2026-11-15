<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireSeller();
$sellerId = currentUserId();

$stmt = $pdo->prepare("SELECT DISTINCT o.* FROM orders o
                        JOIN order_items oi ON o.order_id = oi.order_id
                        WHERE oi.seller_id = ?
                        ORDER BY o.created_at DESC");
$stmt->execute([$sellerId]);
$orders = $stmt->fetchAll();

$pageTitle = 'Orders';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-box"></i> Orders Containing My Products</h4>

  <?php if (empty($orders)): ?>
    <div class="alert alert-info">No orders yet.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle bg-white">
        <thead><tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td>#<?php echo $o['order_id']; ?></td>
              <td><?php echo e($o['customer_name']); ?></td>
              <td><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
              <td><span class="badge badge-status-<?php echo $o['order_status']; ?>"><?php echo $o['order_status']; ?></span></td>
              <td><a href="<?php echo BASE_URL; ?>seller/order_details.php?id=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-outline-brand">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
