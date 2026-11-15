<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$stmt = $pdo->prepare("SELECT c.*, p.product_name, p.price, p.stock, p.image
                        FROM cart c JOIN products p ON c.product_id = p.product_id
                        WHERE c.user_id = ?");
$stmt->execute([currentUserId()]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    flash('error', 'Your cart is empty.');
    redirect('cart/cart.php');
}

$total = 0;
foreach ($cartItems as $item) $total += $item['price'] * $item['quantity'];

$userStmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$userStmt->execute([currentUserId()]);
$user = $userStmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['delivery_address'] ?? '');
    $paymentMethod = in_array($_POST['payment_method'] ?? '', ['COD', 'Manual Payment']) ? $_POST['payment_method'] : 'COD';

    if ($name === '') $errors[] = 'Name is required.';
    if ($phone === '') $errors[] = 'Phone number is required.';
    if ($address === '') $errors[] = 'Delivery address is required.';

    // Re-validate stock right before placing the order
    foreach ($cartItems as $item) {
        $stockCheck = $pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
        $stockCheck->execute([$item['product_id']]);
        $currentStock = $stockCheck->fetchColumn();
        if ($currentStock < $item['quantity']) {
            $errors[] = 'Insufficient stock for "' . $item['product_name'] . '". Only ' . $currentStock . ' left.';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $orderStmt = $pdo->prepare("INSERT INTO orders (user_id, customer_name, phone, delivery_address, total_amount, payment_method, order_status)
                                         VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
            $orderStmt->execute([currentUserId(), $name, $phone, $address, $total, $paymentMethod]);
            $orderId = $pdo->lastInsertId();

            foreach ($cartItems as $item) {
                $productStmt = $pdo->prepare("SELECT seller_id FROM products WHERE product_id = ?");
                $productStmt->execute([$item['product_id']]);
                $sellerId = $productStmt->fetchColumn();

                $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
                $itemStmt->execute([$orderId, $item['product_id'], $sellerId, $item['quantity'], $item['price']]);

                $updateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
                $updateStock->execute([$item['quantity'], $item['product_id']]);
            }

            $clearCart = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $clearCart->execute([currentUserId()]);

            $pdo->commit();

            flash('success', 'Order placed successfully! Your order ID is #' . $orderId . '.');
            redirect('customer/orders.php');
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Something went wrong while placing your order. Please try again.';
        }
    }
}

$pageTitle = 'Checkout';
include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
  <h4 class="mb-3"><i class="fa-solid fa-truck"></i> Checkout</h4>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?php echo e($err); ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>

  <div class="row g-4">
    <div class="col-md-7">
      <form method="post" class="card p-3 needs-validation" novalidate>
        <h6 class="mb-3">Delivery Details</h6>
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="customer_name" class="form-control" required value="<?php echo e($user['full_name']); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Phone Number</label>
          <input type="text" name="phone" class="form-control" required value="<?php echo e($user['phone']); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Delivery Address</label>
          <textarea name="delivery_address" class="form-control" rows="3" required><?php echo e($user['address']); ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Payment Method</label>
          <select name="payment_method" class="form-select">
            <option value="COD">Cash on Delivery</option>
            <option value="Manual Payment">Manual/Demo Payment</option>
          </select>
        </div>
        <button type="submit" class="btn btn-brand w-100">Place Order</button>
      </form>
    </div>
    <div class="col-md-5">
      <div class="card p-3">
        <h6>Order Summary</h6>
        <?php foreach ($cartItems as $item): ?>
          <div class="d-flex justify-content-between small mb-1">
            <span><?php echo e($item['product_name']); ?> x<?php echo $item['quantity']; ?></span>
            <span><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
          </div>
        <?php endforeach; ?>
        <hr>
        <div class="d-flex justify-content-between"><strong>Total</strong><strong><?php echo formatPrice($total); ?></strong></div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
