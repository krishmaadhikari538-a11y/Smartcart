<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

if (isset($_GET['toggle'])) {
    $productId = (int)$_GET['toggle'];
    $stmt = $pdo->prepare("UPDATE products SET status = IF(status='active','inactive','active') WHERE product_id = ?");
    $stmt->execute([$productId]);
    redirect('admin/products.php');
}

if (isset($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    $check = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
    $check->execute([$productId]);
    if ($check->fetchColumn() > 0) {
        flash('error', 'Cannot delete this product because it has existing orders. Deactivate it instead.');
    } else {
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$productId]);
        flash('success', 'Product deleted.');
    }
    redirect('admin/products.php');
}

$products = $pdo->query("SELECT p.*, c.category_name, u.full_name AS seller_name
                          FROM products p JOIN categories c ON p.category_id = c.category_id
                          JOIN users u ON p.seller_id = u.user_id
                          ORDER BY p.created_at DESC")->fetchAll();

$successMsg = flash('success');
$errorMsg = flash('error');

$pageTitle = 'Manage Products';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-box-open"></i> Manage Products</h4>

  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>
  <?php if ($errorMsg): ?><div class="alert alert-danger auto-dismiss"><?php echo e($errorMsg); ?></div><?php endif; ?>

  <div class="table-responsive">
    <table class="table align-middle bg-white">
      <thead><tr><th>Image</th><th>Name</th><th>Seller</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><img src="<?php echo getProductImageUrl($p['image']); ?>" width="45" class="rounded"></td>
            <td><?php echo e($p['product_name']); ?></td>
            <td><?php echo e($p['seller_name']); ?></td>
            <td><?php echo e($p['category_name']); ?></td>
            <td><?php echo formatPrice($p['price']); ?></td>
            <td><?php echo $p['stock']; ?></td>
            <td><span class="badge bg-<?php echo $p['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($p['status']); ?></span></td>
            <td class="text-nowrap">
              <a href="?toggle=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-power-off"></i></a>
              <a href="?delete=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-outline-danger confirm-delete"><i class="fa-solid fa-trash"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
