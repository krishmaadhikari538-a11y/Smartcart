<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$productId = (int)($_GET['id'] ?? 0);
$product = getProductById($pdo, $productId);

if (!$product) {
    $pageTitle = 'Product Not Found';
    include __DIR__ . '/../includes/header.php';
    echo '<div class="container my-5"><div class="alert alert-warning">Product not found. It may have been removed.</div>
          <a href="' . BASE_URL . 'products/products.php" class="btn btn-brand">Browse Products</a></div>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

// Log the view for logged-in customers (used by the recommendation engine)
if (isLoggedIn() && currentUserRole() === 'customer') {
    logProductView($pdo, currentUserId(), $productId);
}

$specs = getProductSpecifications($pdo, $productId);
$ratingInfo = getAverageRating($pdo, $productId);

$reviewsStmt = $pdo->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.user_id
                               WHERE r.product_id = ? AND r.status = 'visible' ORDER BY r.created_at DESC");
$reviewsStmt->execute([$productId]);
$reviews = $reviewsStmt->fetchAll();

$related = getCategoryRecommendations($pdo, $product['category_id'], $productId, 4);

$recommended = [];
if (isLoggedIn() && currentUserRole() === 'customer') {
    $recommended = getRecommendedProducts($pdo, currentUserId(), 4);
}

$inWishlist = false;
if (isLoggedIn() && currentUserRole() === 'customer') {
    $stmt = $pdo->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([currentUserId(), $productId]);
    $inWishlist = (bool)$stmt->fetch();
}

$successMsg = flash('success');
$errorMsg = flash('error');

$pageTitle = $product['product_name'];
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">

  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>
  <?php if ($errorMsg): ?><div class="alert alert-danger auto-dismiss"><?php echo e($errorMsg); ?></div><?php endif; ?>

  <div class="row g-4">
    <div class="col-md-5">
      <img src="<?php echo getProductImageUrl($product['image']); ?>" class="img-fluid rounded shadow-sm w-100" alt="<?php echo e($product['product_name']); ?>">
    </div>
    <div class="col-md-7">
      <h3><?php echo e($product['product_name']); ?></h3>
      <p class="text-muted mb-1">Category: <a href="<?php echo BASE_URL; ?>products/products.php?category=<?php echo $product['category_id']; ?>"><?php echo e($product['category_name']); ?></a></p>
      <div class="rating-stars mb-2">
        <?php for ($i = 1; $i <= 5; $i++): ?>
          <i class="fa-<?php echo $i <= round($ratingInfo['avg']) ? 'solid' : 'regular'; ?> fa-star"></i>
        <?php endfor; ?>
        <span class="text-muted small"><?php echo $ratingInfo['avg']; ?> (<?php echo $ratingInfo['total']; ?> reviews)</span>
      </div>
      <h3 class="product-price"><?php echo formatPrice($product['price']); ?></h3>
      <p class="mb-1"><strong>Stock:</strong>
        <?php if ($product['stock'] > 0): ?>
          <span class="text-success"><?php echo $product['stock']; ?> available</span>
        <?php else: ?>
          <span class="text-danger">Out of stock</span>
        <?php endif; ?>
      </p>
      <p class="mb-3"><strong>Sold by:</strong> <?php echo e($product['seller_name']); ?></p>
      <p><?php echo nl2br(e($product['description'])); ?></p>

      <?php if (!empty($specs)): ?>
        <h6 class="mt-3">Specifications</h6>
        <table class="table table-sm">
          <?php foreach ($specs as $s): ?>
            <tr><th class="w-25"><?php echo e($s['specification_name']); ?></th><td><?php echo e($s['specification_value']); ?></td></tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>

      <div class="d-flex flex-wrap gap-2 mt-3">
        <?php if (isLoggedIn() && currentUserRole() === 'customer'): ?>
          <form action="<?php echo BASE_URL; ?>cart/add_to_cart.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <input type="hidden" name="redirect" value="products/product_details.php?id=<?php echo $productId; ?>">
            <button type="submit" class="btn btn-brand" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
              <i class="fa-solid fa-cart-plus"></i> Add to Cart
            </button>
          </form>
          <form action="<?php echo BASE_URL; ?>wishlist/add.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
            <input type="hidden" name="redirect" value="products/product_details.php?id=<?php echo $productId; ?>">
            <button type="submit" class="btn btn-outline-brand">
              <i class="fa-<?php echo $inWishlist ? 'solid' : 'regular'; ?> fa-heart"></i> <?php echo $inWishlist ? 'In Wishlist' : 'Add to Wishlist'; ?>
            </button>
          </form>
          <a href="<?php echo BASE_URL; ?>alerts/set_alert.php?product_id=<?php echo $productId; ?>" class="btn btn-outline-brand">
            <i class="fa-solid fa-bell"></i> Set Price Alert
          </a>
        <?php elseif (!isLoggedIn()): ?>
          <a href="<?php echo BASE_URL; ?>auth/login.php" class="btn btn-brand">Login to buy this product</a>
        <?php endif; ?>
        <form action="<?php echo BASE_URL; ?>compare/add.php" method="post">
          <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
          <input type="hidden" name="redirect" value="products/product_details.php?id=<?php echo $productId; ?>">
          <button type="submit" class="btn btn-outline-secondary"><i class="fa-solid fa-scale-balanced"></i> Add to Compare</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Reviews -->
  <div class="mt-5">
    <h5>Customer Reviews</h5>
    <?php if (empty($reviews)): ?>
      <p class="text-muted">No reviews yet. Be the first to review this product!</p>
    <?php else: ?>
      <?php foreach ($reviews as $r): ?>
        <div class="border-bottom py-2">
          <strong><?php echo e($r['full_name']); ?></strong>
          <span class="rating-stars ms-2">
            <?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-<?php echo $i <= $r['rating'] ? 'solid' : 'regular'; ?> fa-star"></i><?php endfor; ?>
          </span>
          <p class="mb-0 small"><?php echo e($r['comment']); ?></p>
          <span class="text-muted small"><?php echo date('M d, Y', strtotime($r['created_at'])); ?></span>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isLoggedIn() && currentUserRole() === 'customer'): ?>
      <form action="<?php echo BASE_URL; ?>products/submit_review.php" method="post" class="mt-3 needs-validation" novalidate>
        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
        <div class="mb-2">
          <label class="form-label">Your Rating</label>
          <div class="star-rating-input fs-4">
            <?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-regular fa-star" data-value="<?php echo $i; ?>" style="cursor:pointer;"></i><?php endfor; ?>
          </div>
          <input type="hidden" name="rating" value="5" required>
        </div>
        <div class="mb-2">
          <textarea name="comment" class="form-control" rows="2" placeholder="Write your review..." required></textarea>
        </div>
        <button type="submit" class="btn btn-brand btn-sm">Submit Review</button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (!empty($related)): ?>
    <div class="mt-5">
      <h5>Related Products</h5>
      <div class="row g-4">
        <?php foreach ($related as $p): ?>
          <div class="col-6 col-md-3"><?php include __DIR__ . '/../includes/product_card.php'; ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($recommended)): ?>
    <div class="mt-5">
      <h5><i class="fa-solid fa-wand-magic-sparkles text-warning"></i> Recommended for You</h5>
      <div class="row g-4">
        <?php foreach ($recommended as $p): ?>
          <div class="col-6 col-md-3"><?php include __DIR__ . '/../includes/product_card.php'; ?></div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
