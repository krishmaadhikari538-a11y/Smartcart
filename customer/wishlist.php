<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$stmt = $pdo->prepare("SELECT w.wishlist_id, p.* FROM wishlist w JOIN products p ON w.product_id = p.product_id WHERE w.user_id = ? ORDER BY w.created_at DESC");
$stmt->execute([currentUserId()]);
$items = $stmt->fetchAll();

$successMsg = flash('success');

$pageTitle = 'My Wishlist';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-heart"></i> My Wishlist</h4>
  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>

  <?php if (empty($items)): ?>
    <div class="alert alert-info">Your wishlist is empty. <a href="<?php echo BASE_URL; ?>products/products.php">Browse products</a> to add items.</div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($items as $p): ?>
        <div class="col-6 col-md-3">
          <div class="product-card card">
            <a href="<?php echo BASE_URL; ?>products/product_details.php?id=<?php echo $p['product_id']; ?>">
              <img src="<?php echo getProductImageUrl($p['image']); ?>" alt="<?php echo e($p['product_name']); ?>">
            </a>
            <div class="card-body">
              <h6><?php echo e($p['product_name']); ?></h6>
              <div class="product-price mb-2"><?php echo formatPrice($p['price']); ?></div>
              <div class="d-flex gap-1">
                <form action="<?php echo BASE_URL; ?>cart/add_to_cart.php" method="post" class="flex-fill">
                  <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                  <input type="hidden" name="redirect" value="customer/wishlist.php">
                  <button class="btn btn-sm btn-brand w-100">Move to Cart</button>
                </form>
                <a href="<?php echo BASE_URL; ?>wishlist/remove.php?wishlist_id=<?php echo $p['wishlist_id']; ?>" class="btn btn-sm btn-outline-danger confirm-delete"><i class="fa-solid fa-trash"></i></a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
