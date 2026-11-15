<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$orderId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->execute([$orderId, currentUserId()]);
$order = $stmt->fetch();

if (!$order) {
    $pageTitle = 'Order Not Found';
    include __DIR__ . '/../includes/header.php';
    echo '<div class="container my-5"><div class="alert alert-warning">Order not found.</div></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$itemsStmt = $pdo->prepare("SELECT oi.*, p.product_name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = ?");
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();

$pageTitle = 'Order #' . $orderId;
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3">Order #<?php echo $orderId; ?> <span class="badge badge-status-<?php echo $order['order_status']; ?>"><?php echo $order['order_status']; ?></span></h4>

  <div class="row g-4">
    <div class="col-md-8">
      <div class="card p-3">
        <h6>Items</h6>
        <table class="table table-sm">
          <?php foreach ($items as $item): ?>
            <tr>
              <td width="60"><img src="<?php echo getProductImageUrl($item['image']); ?>" width="50" class="rounded"></td>
              <td><?php echo e($item['product_name']); ?></td>
              <td>x<?php echo $item['quantity']; ?></td>
              <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h6>Delivery Details</h6>
        <p class="small mb-1"><strong>Name:</strong> <?php echo e($order['customer_name']); ?></p>
        <p class="small mb-1"><strong>Phone:</strong> <?php echo e($order['phone']); ?></p>
        <p class="small mb-1"><strong>Address:</strong> <?php echo e($order['delivery_address']); ?></p>
        <p class="small mb-1"><strong>Payment:</strong> <?php echo e($order['payment_method']); ?></p>
        <p class="small mb-1"><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
        <hr>
        <div class="d-flex justify-content-between"><strong>Total</strong><strong><?php echo formatPrice($order['total_amount']); ?></strong></div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
