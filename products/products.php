<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_name")->fetchAll();

$q = trim($_GET['q'] ?? '');
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = $_GET['sort'] ?? 'newest';

$sql = "SELECT p.*, c.category_name, (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.product_id) AS avg_rating
        FROM products p JOIN categories c ON p.category_id = c.category_id
        WHERE p.status = 'active'";
$params = [];

if ($q !== '') {
    $sql .= " AND (p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($categoryId > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryId;
}

switch ($sort) {
    case 'price_low': $sql .= " ORDER BY p.price ASC"; break;
    case 'price_high': $sql .= " ORDER BY p.price DESC"; break;
    case 'rating': $sql .= " ORDER BY avg_rating DESC"; break;
    default: $sql .= " ORDER BY p.created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Products';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <div class="row">
    <div class="col-md-3 mb-4">
      <div class="card p-3">
        <h6>Categories</h6>
        <ul class="list-unstyled small">
          <li class="mb-1"><a href="<?php echo BASE_URL; ?>products/products.php" class="<?php echo $categoryId === 0 ? 'fw-bold' : ''; ?>">All Categories</a></li>
          <?php foreach ($categories as $cat): ?>
            <li class="mb-1">
              <a href="<?php echo BASE_URL; ?>products/products.php?category=<?php echo $cat['category_id']; ?>"
                 class="<?php echo $categoryId === (int)$cat['category_id'] ? 'fw-bold' : ''; ?>">
                <?php echo e($cat['category_name']); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="col-md-9">
      <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h5 class="mb-0">
          <?php echo $q !== '' ? 'Search results for "' . e($q) . '"' : 'All Products'; ?>
          <span class="text-muted small">(<?php echo count($products); ?> found)</span>
        </h5>
        <form method="get" class="d-flex gap-2">
          <?php if ($categoryId): ?><input type="hidden" name="category" value="<?php echo $categoryId; ?>"><?php endif; ?>
          <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?php echo e($q); ?>"><?php endif; ?>
          <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
            <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Top Rated</option>
          </select>
        </form>
      </div>

      <?php if (empty($products)): ?>
        <div class="alert alert-info">No products found. Try a different search or category.</div>
      <?php else: ?>
        <div class="row g-4">
          <?php foreach ($products as $p): ?>
            <div class="col-6 col-md-4"><?php include __DIR__ . '/../includes/product_card.php'; ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
