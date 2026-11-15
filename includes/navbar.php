<?php
if (!isset($pdo)) { require_once __DIR__ . '/../config/database.php'; }
$navCartCount = 0;
$navWishlistCount = 0;
$navUnread = 0;
if (isLoggedIn() && currentUserRole() === 'customer') {
    $navCartCount = getCartCount($pdo, currentUserId());
    $navWishlistCount = getWishlistCount($pdo, currentUserId());
    $navUnread = getUnreadNotificationCount($pdo, currentUserId());
}
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>index.php">
      <i class="fa-solid fa-cart-shopping"></i> SmartCart
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>products/products.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>compare/compare.php">Compare</a></li>
      </ul>
      <form class="d-flex me-3" role="search" action="<?php echo BASE_URL; ?>products/products.php" method="get">
        <input class="form-control form-control-sm" type="search" name="q" placeholder="Search products..." value="<?php echo isset($_GET['q']) ? e($_GET['q']) : ''; ?>">
      </form>
      <ul class="navbar-nav align-items-lg-center">
        <?php if (isLoggedIn() && currentUserRole() === 'customer'): ?>
          <li class="nav-item">
            <a class="nav-link position-relative" href="<?php echo BASE_URL; ?>cart/cart.php" title="Cart">
              <i class="fa-solid fa-bag-shopping"></i>
              <?php if ($navCartCount > 0): ?><span class="badge rounded-pill bg-danger badge-nav"><?php echo $navCartCount; ?></span><?php endif; ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link position-relative" href="<?php echo BASE_URL; ?>customer/wishlist.php" title="Wishlist">
              <i class="fa-solid fa-heart"></i>
              <?php if ($navWishlistCount > 0): ?><span class="badge rounded-pill bg-danger badge-nav"><?php echo $navWishlistCount; ?></span><?php endif; ?>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link position-relative" href="<?php echo BASE_URL; ?>customer/notifications.php" title="Notifications">
              <i class="fa-solid fa-bell"></i>
              <?php if ($navUnread > 0): ?><span class="badge rounded-pill bg-danger badge-nav"><?php echo $navUnread; ?></span><?php endif; ?>
            </a>
          </li>
        <?php endif; ?>

        <?php if (isLoggedIn()): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
              <i class="fa-solid fa-user-circle"></i> <?php echo e(currentUserName()); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if (currentUserRole() === 'customer'): ?>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>customer/dashboard.php">Dashboard</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>customer/profile.php">Profile</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>customer/orders.php">My Orders</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>customer/price_alerts.php">Price Alerts</a></li>
              <?php elseif (currentUserRole() === 'seller'): ?>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>seller/dashboard.php">Seller Dashboard</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>seller/products.php">My Products</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>seller/orders.php">Orders</a></li>
              <?php elseif (currentUserRole() === 'admin'): ?>
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>admin/dashboard.php">Admin Dashboard</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>auth/login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link btn btn-sm btn-light text-primary ms-2 px-3" href="<?php echo BASE_URL; ?>auth/register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
