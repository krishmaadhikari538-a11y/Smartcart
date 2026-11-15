<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();

$featured = $pdo->query("SELECT p.*, (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.product_id) AS avg_rating
                          FROM products p WHERE p.status = 'active'
                          ORDER BY RAND() LIMIT 8")->fetchAll();

$popular = $pdo->query("SELECT p.*, COUNT(oi.order_item_id) AS orders_count
                         FROM products p LEFT JOIN order_items oi ON p.product_id = oi.product_id
                         WHERE p.status = 'active'
                         GROUP BY p.product_id
                         ORDER BY orders_count DESC, p.created_at DESC LIMIT 4")->fetchAll();

$latest = $pdo->query("SELECT * FROM products WHERE status = 'active' ORDER BY created_at DESC LIMIT 4")->fetchAll();

$recommended = [];
if (isLoggedIn() && currentUserRole() === 'customer') {
    $recommended = getRecommendedProducts($pdo, currentUserId(), 4);
}

$pageTitle = 'Home';
include __DIR__ . '/includes/header.php';
?>

<section class="hero-section text-center">
  <div class="container">
    <h1 class="display-5 mb-3">Shop Smarter with SmartCart</h1>
    <p class="lead mb-4">Personalized recommendations, price drop alerts, and easy product comparison - all in one place.</p>
    <form action="<?php echo BASE_URL; ?>products/products.php" method="get" class="d-flex justify-content-center">
      <div class="input-group" style="max-width: 500px;">
        <input type="search" name="q" class="form-control" placeholder="Search for products...">
        <button class="btn btn-brand" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
      </div>
    </form>
  </div>
</section>

<div class="container my-5">

  <!-- Categories -->
  <h4 class="mb-3">Shop by Category</h4>
  <div class="mb-5">
    <?php foreach ($categories as $cat): ?>
      <a class="category-pill" href="<?php echo BASE_URL; ?>products/products.php?category=<?php echo $cat['category_id']; ?>">
        <?php echo e($cat['category_name']); ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($recommended)): ?>
    <h4 class="mb-3"><i class="fa-solid fa-wand-magic-sparkles text-warning"></i> Recommended for You</h4>
    <div class="row g-4 mb-5">
      <?php foreach ($recommended as $p): ?>
        <div class="col-6 col-md-3"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h4 class="mb-3">Featured Products</h4>
  <div class="row g-4 mb-5">
    <?php foreach ($featured as $p): ?>
      <div class="col-6 col-md-3"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
    <?php endforeach; ?>
  </div>

  <h4 class="mb-3">Popular Products</h4>
  <div class="row g-4 mb-5">
    <?php foreach ($popular as $p): ?>
      <div class="col-6 col-md-3"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
    <?php endforeach; ?>
  </div>

  <h4 class="mb-3">Latest Products</h4>
  <div class="row g-4 mb-5">
    <?php foreach ($latest as $p): ?>
      <div class="col-6 col-md-3"><?php include __DIR__ . '/includes/product_card.php'; ?></div>
    <?php endforeach; ?>
  </div>

  <!-- Features -->
  <div class="row text-center g-4 my-4">
    <div class="col-md-4">
      <i class="fa-solid fa-wand-magic-sparkles fa-2x text-primary mb-2"></i>
      <h5>Smart Recommendations</h5>
      <p class="text-muted small">Products suggested based on your browsing and purchase history using simple rule-based logic.</p>
    </div>
    <div class="col-md-4">
      <i class="fa-solid fa-bell fa-2x text-primary mb-2"></i>
      <h5>Price Drop Alerts</h5>
      <p class="text-muted small">Set your target price and get notified the moment a product's price drops to it.</p>
    </div>
    <div class="col-md-4">
      <i class="fa-solid fa-scale-balanced fa-2x text-primary mb-2"></i>
      <h5>Product Comparison</h5>
      <p class="text-muted small">Compare up to 3 products side by side, including their full specifications.</p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
