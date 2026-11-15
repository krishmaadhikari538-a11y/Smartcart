<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$stmt = $pdo->prepare("SELECT c.*, p.product_name, p.price, p.image, p.stock
                        FROM cart c JOIN products p ON c.product_id = p.product_id
                        WHERE c.user_id = ? ORDER BY c.created_at DESC");
$stmt->execute([currentUserId()]);
$cartItems = $stmt->fetchAll();

$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

$successMsg = flash('success');
$errorMsg = flash('error');

$pageTitle = 'My Cart';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-bag-shopping"></i> My Shopping Cart</h4>

  <?php if ($successMsg): ?><div class="alert alert-success auto-dismiss"><?php echo e($successMsg); ?></div><?php endif; ?>
  <?php if ($errorMsg): ?><div class="alert alert-danger auto-dismiss"><?php echo e($errorMsg); ?></div><?php endif; ?>

  <?php if (empty($cartItems)): ?>
    <div class="alert alert-info">Your cart is empty. <a href="<?php echo BASE_URL; ?>products/products.php">Browse products</a> to add items.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($cartItems as $item): ?>
            <tr>
              <td class="d-flex align-items-center gap-2">
                <img src="<?php echo getProductImageUrl($item['image']); ?>" width="50" height="50" style="object-fit:cover;" class="rounded">
                <a href="<?php echo BASE_URL; ?>products/product_details.php?id=<?php echo $item['product_id']; ?>" class="text-decoration-none"><?php echo e($item['product_name']); ?></a>
              </td>
              <td><?php echo formatPrice($item['price']); ?></td>
              <td>
                <form action="<?php echo BASE_URL; ?>cart/update_cart.php" method="post" class="d-flex align-items-center gap-1">
                  <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                  <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="form-control form-control-sm" style="width:70px;">
                  <button class="btn btn-sm btn-outline-brand" type="submit">Update</button>
                </form>
              </td>
              <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
              <td><a href="<?php echo BASE_URL; ?>cart/remove_from_cart.php?cart_id=<?php echo $item['cart_id']; ?>" class="btn btn-sm btn-outline-danger confirm-delete"><i class="fa-solid fa-trash"></i></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-end">
      <div class="card p-3" style="max-width:350px; width:100%;">
        <div class="d-flex justify-content-between mb-2"><span>Total</span><strong><?php echo formatPrice($total); ?></strong></div>
        <a href="<?php echo BASE_URL; ?>checkout/checkout.php" class="btn btn-brand w-100">Proceed to Checkout</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
