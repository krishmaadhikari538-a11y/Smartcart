<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = $_POST['order_status'] ?? '';
    $allowed = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
    if (in_array($newStatus, $allowed) && $orderId > 0) {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $stmt->execute([$newStatus, $orderId]);
        flash('success', 'Order #' . $orderId . ' status updated.');
    }
    redirect('admin/orders.php');
}

$orders = $pdo->query("SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.created_at DESC")->fetchAll();

$successMsg = flash('success');

$pageTitle = 'Manage Orders';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-truck"></i> Manage Orders</h4>
  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>

  <div class="table-responsive">
    <table class="table align-middle bg-white">
      <thead><tr><th>Order ID</th><th>Customer</th><th>Email</th><th>Total</th><th>Date</th><th>Status</th><th>Update</th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td>#<?php echo $o['order_id']; ?></td>
            <td><?php echo e($o['customer_name']); ?></td>
            <td><?php echo e($o['email']); ?></td>
            <td><?php echo formatPrice($o['total_amount']); ?></td>
            <td><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
            <td><span class="badge badge-status-<?php echo $o['order_status']; ?>"><?php echo $o['order_status']; ?></span></td>
            <td>
              <form method="post" class="d-flex gap-1">
                <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                <select name="order_status" class="form-select form-select-sm">
                  <?php foreach (['Pending','Processing','Shipped','Delivered','Cancelled'] as $status): ?>
                    <option value="<?php echo $status; ?>" <?php echo $o['order_status'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-outline-brand">Save</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
