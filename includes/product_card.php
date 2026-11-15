<?php
/**
 * Reusable product card. Requires $p (a product row) in scope.
 */
$rating = isset($p['avg_rating']) && $p['avg_rating'] ? round($p['avg_rating'], 1) : 0;
?>
<div class="product-card card">
  <a href="<?php echo BASE_URL; ?>products/product_details.php?id=<?php echo $p['product_id']; ?>">
    <img src="<?php echo getProductImageUrl($p['image']); ?>" alt="<?php echo e($p['product_name']); ?>">
  </a>
  <div class="card-body">
    <a href="<?php echo BASE_URL; ?>products/product_details.php?id=<?php echo $p['product_id']; ?>" class="text-decoration-none text-dark">
      <h6 class="mb-1"><?php echo e($p['product_name']); ?></h6>
    </a>
    <div class="rating-stars small mb-1">
      <?php for ($i = 1; $i <= 5; $i++): ?>
        <i class="fa-<?php echo $i <= round($rating) ? 'solid' : 'regular'; ?> fa-star"></i>
      <?php endfor; ?>
      <?php if ($rating > 0): ?><span class="text-muted">(<?php echo $rating; ?>)</span><?php endif; ?>
    </div>
    <div class="product-price mb-2"><?php echo formatPrice($p['price']); ?></div>
    <div class="mt-auto d-flex gap-1">
      <a href="<?php echo BASE_URL; ?>products/product_details.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-brand flex-fill">View</a>
      <?php if (isLoggedIn() && currentUserRole() === 'customer'): ?>
        <form action="<?php echo BASE_URL; ?>cart/add_to_cart.php" method="post" class="flex-fill">
          <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
          <input type="hidden" name="redirect" value="<?php echo e($_SERVER['REQUEST_URI']); ?>">
          <button type="submit" class="btn btn-sm btn-brand w-100"><i class="fa-solid fa-cart-plus"></i></button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
