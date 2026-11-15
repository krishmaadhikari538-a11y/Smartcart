<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireSeller();
$sellerId = currentUserId();

$orderId = (int)($_GET['id'] ?? 0);

// Confirm this seller has at least one item in this order
$check = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ? AND seller_id = ?");
$check->execute([$orderId, $sellerId]);
if ($check->fetchColumn() == 0) {
    flash('error', 'Order not found.');
    redirect('seller/orders.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['order_status'] ?? '';
    $allowed = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    if (in_array($newStatus, $allowed)) {
        $update = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $update->execute([$newStatus, $orderId]);
        flash('success', 'Order status updated.');
    }
    redirect('seller/order_details.php?id=' . $orderId);
}

$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE order_id = ?");
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch();

$itemsStmt = $pdo->prepare("SELECT oi.*, p.product_name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.product_id
                             WHERE oi.order_id = ? AND oi.seller_id = ?");
$itemsStmt->execute([$orderId, $sellerId]);
$items = $itemsStmt->fetchAll();

$successMsg = flash('success');

$pageTitle = 'Order #' . $orderId;
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3">Order #<?php echo $orderId; ?></h4>
  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>

  <div class="row g-4">
    <div class="col-md-8">
      <div class="card p-3">
        <h6>My Items in This Order</h6>
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
        <h6>Customer Details</h6>
        <p class="small mb-1"><strong>Name:</strong> <?php echo e($order['customer_name']); ?></p>
        <p class="small mb-1"><strong>Phone:</strong> <?php echo e($order['phone']); ?></p>
        <p class="small mb-3"><strong>Address:</strong> <?php echo e($order['delivery_address']); ?></p>

        <form method="post">
          <label class="form-label">Order Status</label>
          <select name="order_status" class="form-select mb-2">
            <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $status): ?>
              <option value="<?php echo $status; ?>" <?php echo $order['order_status'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-brand w-100">Update Status</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
