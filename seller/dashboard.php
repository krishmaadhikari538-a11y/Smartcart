<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireSeller();
$sellerId = currentUserId();

$totalProducts = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
$totalProducts->execute([$sellerId]);
$totalProducts = $totalProducts->fetchColumn();

$totalOrders = $pdo->prepare("SELECT COUNT(DISTINCT order_id) FROM order_items WHERE seller_id = ?");
$totalOrders->execute([$sellerId]);
$totalOrders = $totalOrders->fetchColumn();

$pendingOrders = $pdo->prepare("SELECT COUNT(DISTINCT oi.order_id) FROM order_items oi JOIN orders o ON oi.order_id = o.order_id WHERE oi.seller_id = ? AND o.order_status = 'Pending'");
$pendingOrders->execute([$sellerId]);
$pendingOrders = $pendingOrders->fetchColumn();

$completedOrders = $pdo->prepare("SELECT COUNT(DISTINCT oi.order_id) FROM order_items oi JOIN orders o ON oi.order_id = o.order_id WHERE oi.seller_id = ? AND o.order_status = 'Delivered'");
$completedOrders->execute([$sellerId]);
$completedOrders = $completedOrders->fetchColumn();

$lowStock = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? AND stock <= 5 ORDER BY stock ASC");
$lowStock->execute([$sellerId]);
$lowStock = $lowStock->fetchAll();

$recentProducts = $pdo->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC LIMIT 5");
$recentProducts->execute([$sellerId]);
$recentProducts = $recentProducts->fetchAll();

$pageTitle = 'Seller Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-4">Welcome, <?php echo e(currentUserName()); ?>!</h4>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="dashboard-stat stat-1"><span>Total Products</span><h3><?php echo $totalProducts; ?></h3></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="dashboard-stat stat-2"><span>Total Orders</span><h3><?php echo $totalOrders; ?></h3></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="dashboard-stat stat-3"><span>Pending Orders</span><h3><?php echo $pendingOrders; ?></h3></div>
    </div>
    <div class="col-6 col-md-3">
      <div class="dashboard-stat stat-4"><span>Completed Orders</span><h3><?php echo $completedOrders; ?></h3></div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card p-3">
        <h6>Low Stock Products (5 or fewer)</h6>
        <?php if (empty($lowStock)): ?>
          <p class="text-muted small mb-0">No low stock products.</p>
        <?php else: ?>
          <table class="table table-sm">
            <?php foreach ($lowStock as $p): ?>
              <tr><td><?php echo e($p['product_name']); ?></td><td class="text-danger"><?php echo $p['stock']; ?> left</td></tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <h6>Recently Added Products</h6>
        <?php if (empty($recentProducts)): ?>
          <p class="text-muted small mb-0">No products added yet. <a href="<?php echo BASE_URL; ?>seller/add_product.php">Add one now</a>.</p>
        <?php else: ?>
          <table class="table table-sm">
            <?php foreach ($recentProducts as $p): ?>
              <tr><td><?php echo e($p['product_name']); ?></td><td><?php echo formatPrice($p['price']); ?></td></tr>
            <?php endforeach; ?>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
