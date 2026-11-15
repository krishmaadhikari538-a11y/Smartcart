<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_GET['clear'])) {
    clearCompare();
    redirect('compare/compare.php');
}

$compareIds = getCompareList();
$products = [];
$allSpecNames = [];

foreach ($compareIds as $id) {
    $p = getProductById($pdo, $id);
    if ($p) {
        $p['specs'] = [];
        foreach (getProductSpecifications($pdo, $id) as $s) {
            $p['specs'][$s['specification_name']] = $s['specification_value'];
            $allSpecNames[$s['specification_name']] = true;
        }
        $p['rating'] = getAverageRating($pdo, $id);
        $products[] = $p;
    }
}
$allSpecNames = array_keys($allSpecNames);

$pageTitle = 'Compare Products';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa-solid fa-scale-balanced"></i> Compare Products</h4>
    <?php if (!empty($products)): ?>
      <a href="?clear=1" class="btn btn-outline-danger btn-sm confirm-delete">Clear All</a>
    <?php endif; ?>
  </div>

  <?php if (empty($products)): ?>
    <div class="alert alert-info">
      You haven't added any products to compare yet. You can add up to 3 products from the product listing or details page.
      <br><a href="<?php echo BASE_URL; ?>products/products.php" class="btn btn-brand btn-sm mt-2">Browse Products</a>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-compare bg-white">
        <tr>
          <th style="width:150px;">Product</th>
          <?php foreach ($products as $p): ?>
            <td class="text-center">
              <img src="<?php echo getProductImageUrl($p['image']); ?>" width="100" class="mb-2 rounded">
              <div><strong><?php echo e($p['product_name']); ?></strong></div>
              <a href="?product_id=<?php echo $p['product_id']; ?>" onclick="return false;"></a>
              <a href="<?php echo BASE_URL; ?>compare/remove.php?product_id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-danger mt-1">Remove</a>
            </td>
          <?php endforeach; ?>
        </tr>
        <tr><th>Category</th><?php foreach ($products as $p): ?><td class="text-center"><?php echo e($p['category_name']); ?></td><?php endforeach; ?></tr>
        <tr><th>Price</th><?php foreach ($products as $p): ?><td class="text-center product-price"><?php echo formatPrice($p['price']); ?></td><?php endforeach; ?></tr>
        <tr><th>Seller</th><?php foreach ($products as $p): ?><td class="text-center"><?php echo e($p['seller_name']); ?></td><?php endforeach; ?></tr>
        <tr><th>Rating</th><?php foreach ($products as $p): ?><td class="text-center"><?php echo $p['rating']['avg']; ?> / 5 (<?php echo $p['rating']['total']; ?>)</td><?php endforeach; ?></tr>
        <tr><th>Stock</th><?php foreach ($products as $p): ?><td class="text-center"><?php echo $p['stock'] > 0 ? $p['stock'] . ' available' : 'Out of stock'; ?></td><?php endforeach; ?></tr>

        <?php foreach ($allSpecNames as $specName): ?>
          <tr>
            <th><?php echo e($specName); ?></th>
            <?php foreach ($products as $p): ?>
              <td class="text-center"><?php echo isset($p['specs'][$specName]) ? e($p['specs'][$specName]) : '-'; ?></td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>

        <tr>
          <th>Action</th>
          <?php foreach ($products as $p): ?>
            <td class="text-center"><a href="<?php echo BASE_URL; ?>products/product_details.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-brand">View Details</a></td>
          <?php endforeach; ?>
        </tr>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
