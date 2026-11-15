<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$totalCustomers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalSellers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn();
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
$lowStock = $pdo->query("SELECT * FROM products WHERE stock <= 5 ORDER BY stock ASC LIMIT 8")->fetchAll();

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-4">Admin Dashboard</h4>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-2">
      <div class="dashboard-stat stat-1"><span>Customers</span><h3><?php echo $totalCustomers; ?></h3></div>
    </div>
    <div class="col-6 col-md-2">
      <div class="dashboard-stat stat-2"><span>Sellers</span><h3><?php echo $totalSellers; ?></h3></div>
    </div>
    <div class="col-6 col-md-2">
      <div class="dashboard-stat stat-3"><span>Products</span><h3><?php echo $totalProducts; ?></h3></div>
    </div>
    <div class="col-6 col-md-2">
      <div class="dashboard-stat stat-4"><span>Categories</span><h3><?php echo $totalCategories; ?></h3></div>
    </div>
    <div class="col-6 col-md-2">
      <div class="dashboard-stat stat-1"><span>Orders</span><h3><?php echo $totalOrders; ?></h3></div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3"><a href="<?php echo BASE_URL; ?>admin/users.php" class="btn btn-outline-brand w-100">Manage Customers</a></div>
    <div class="col-md-3"><a href="<?php echo BASE_URL; ?>admin/sellers.php" class="btn btn-outline-brand w-100">Manage Sellers</a></div>
    <div class="col-md-3"><a href="<?php echo BASE_URL; ?>admin/categories.php" class="btn btn-outline-brand w-100">Manage Categories</a></div>
    <div class="col-md-3"><a href="<?php echo BASE_URL; ?>admin/products.php" class="btn btn-outline-brand w-100">Manage Products</a></div>
    <div class="col-md-3"><a href="<?php echo BASE_URL; ?>admin/orders.php" class="btn btn-outline-brand w-100">Manage Orders</a></div>
    <div class="col-md-3"><a href="<?php echo BASE_URL; ?>admin/reviews.php" class="btn btn-outline-brand w-100">Manage Reviews</a></div>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="card p-3">
        <h6>Recent Orders</h6>
        <?php foreach ($recentOrders as $o): ?>
          <div class="d-flex justify-content-between small mb-1">
            <span>#<?php echo $o['order_id']; ?> - <?php echo e($o['customer_name']); ?></span>
            <span class="badge badge-status-<?php echo $o['order_status']; ?>"><?php echo $o['order_status']; ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h6>Recent Users</h6>
        <?php foreach ($recentUsers as $u): ?>
          <div class="d-flex justify-content-between small mb-1">
            <span><?php echo e($u['full_name']); ?></span>
            <span class="badge bg-secondary"><?php echo ucfirst($u['role']); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-3">
        <h6>Low Stock Products</h6>
        <?php if (empty($lowStock)): ?>
          <p class="text-muted small mb-0">None right now.</p>
        <?php else: ?>
          <?php foreach ($lowStock as $p): ?>
            <div class="d-flex justify-content-between small mb-1">
              <span><?php echo e($p['product_name']); ?></span>
              <span class="text-danger"><?php echo $p['stock']; ?> left</span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
