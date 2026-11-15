<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireSeller();
$sellerId = currentUserId();

if (isset($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    // Only allow deleting the seller's own product, and only if it has no orders yet
    $check = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ? AND seller_id = ?");
    $check->execute([$productId, $sellerId]);
    if ($check->fetchColumn() > 0) {
        flash('error', 'This product cannot be deleted because it has existing orders. You can mark it inactive instead.');
    } else {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ? AND seller_id = ?");
        $stmt->execute([$productId, $sellerId]);
        flash('success', 'Product deleted successfully.');
    }
    redirect('seller/products.php');
}

if (isset($_GET['toggle'])) {
    $productId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE products SET status = IF(status='active','inactive','active') WHERE product_id = ? AND seller_id = ?");
    $stmt->execute([$productId, $sellerId]);
    redirect('seller/products.php');
}

$stmt = $pdo->prepare("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id
                        WHERE p.seller_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$sellerId]);
$products = $stmt->fetchAll();

$successMsg = flash('success');
$errorMsg = flash('error');

$pageTitle = 'My Products';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fa-solid fa-boxes-stacked"></i> My Products</h4>
    <a href="<?php echo BASE_URL; ?>seller/add_product.php" class="btn btn-brand"><i class="fa-solid fa-plus"></i> Add Product</a>
  </div>

  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>
  <?php if ($errorMsg): ?><div class="alert alert-danger auto-dismiss"><?php echo e($errorMsg); ?></div><?php endif; ?>

  <?php if (empty($products)): ?>
    <div class="alert alert-info">You haven't added any products yet.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle bg-white">
        <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><img src="<?php echo getProductImageUrl($p['image']); ?>" width="50" class="rounded"></td>
              <td><?php echo e($p['product_name']); ?></td>
              <td><?php echo e($p['category_name']); ?></td>
              <td><?php echo formatPrice($p['price']); ?></td>
              <td><?php echo $p['stock']; ?><?php if ($p['stock'] <= 5): ?> <span class="badge bg-warning text-dark">Low</span><?php endif; ?></td>
              <td><span class="badge bg-<?php echo $p['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($p['status']); ?></span></td>
              <td class="text-nowrap">
                <a href="<?php echo BASE_URL; ?>seller/edit_product.php?id=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-brand"><i class="fa-solid fa-pen"></i></a>
                <a href="?toggle=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-power-off"></i></a>
                <a href="?delete=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-danger confirm-delete"><i class="fa-solid fa-trash"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
