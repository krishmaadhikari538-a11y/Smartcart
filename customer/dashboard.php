<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();
$userId = currentUserId();

$totalOrders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$totalOrders->execute([$userId]);
$totalOrders = $totalOrders->fetchColumn();

$wishlistCount = getWishlistCount($pdo, $userId);

$activeAlerts = $pdo->prepare("SELECT COUNT(*) FROM price_alerts WHERE user_id = ? AND status = 'active'");
$activeAlerts->execute([$userId]);
$activeAlerts = $activeAlerts->fetchColumn();

$unreadNotifications = getUnreadNotificationCount($pdo, $userId);

$recentOrders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$recentOrders->execute([$userId]);
$recentOrders = $recentOrders->fetchAll();

$recommended = getRecommendedProducts($pdo, $userId, 4);

$pageTitle = 'Customer Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-4">Welcome back, <?php echo e(currentUserName()); ?>!</h4>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="dashboard-stat stat-1">
        <span>Total Orders</span>
        <h3><?php echo $totalOrders; ?></h3>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="dashboard-stat stat-2">
        <span>Wishlist Items</span>
        <h3><?php echo $wishlistCount; ?></h3>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="dashboard-stat stat-3">
        <span>Active Price Alerts</span>
        <h3><?php echo $activeAlerts; ?></h3>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="dashboard-stat stat-4">
        <span>Unread Notifications</span>
        <h3><?php echo $unreadNotifications; ?></h3>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card p-3">
        <h6>Recent Orders</h6>
        <?php if (empty($recentOrders)): ?>
          <p class="text-muted small mb-0">No orders yet.</p>
        <?php else: ?>
          <table class="table table-sm">
            <?php foreach ($recentOrders as $o): ?>
              <tr>
                <td>#<?php echo $o['order_id']; ?></td>
                <td><?php echo formatPrice($o['total_amount']); ?></td>
                <td><span class="badge badge-status-<?php echo $o['order_status']; ?>"><?php echo $o['order_status']; ?></span></td>
                <td><a href="<?php echo BASE_URL; ?>customer/order_details.php?id=<?php echo $o['order_id']; ?>" class="small">View</a></td>
              </tr>
            <?php endforeach; ?>
          </table>
          <a href="<?php echo BASE_URL; ?>customer/orders.php" class="small">View all orders &rarr;</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card p-3">
        <h6><i class="fa-solid fa-wand-magic-sparkles text-warning"></i> Recommended for You</h6>
        <?php if (empty($recommended)): ?>
          <p class="text-muted small mb-0">No recommendations yet - start browsing products!</p>
        <?php else: ?>
          <div class="row g-2">
            <?php foreach (array_slice($recommended, 0, 4) as $p): ?>
              <div class="col-6">
                <a href="<?php echo BASE_URL; ?>products/product_details.php?id=<?php echo $p['product_id']; ?>" class="text-decoration-none text-dark small">
                  <img src="<?php echo getProductImageUrl($p['image']); ?>" class="img-fluid rounded mb-1" style="height:80px;object-fit:cover;width:100%;">
                  <?php echo e($p['product_name']); ?>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
