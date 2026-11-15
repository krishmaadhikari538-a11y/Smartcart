<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$productId = (int)($_POST['product_id'] ?? 0);
$redirectTo = $_POST['redirect'] ?? 'cart/cart.php';
$qty = max(1, (int)($_POST['quantity'] ?? 1));

$product = getProductById($pdo, $productId);

if (!$product) {
    flash('error', 'Product not found.');
} elseif ($product['stock'] <= 0) {
    flash('error', 'This product is currently out of stock.');
} else {
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([currentUserId(), $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = min($existing['quantity'] + $qty, $product['stock']);
        $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $update->execute([$newQty, $existing['cart_id']]);
    } else {
        $insert = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert->execute([currentUserId(), $productId, min($qty, $product['stock'])]);
    }
    flash('success', 'Product added to cart.');
}

redirect($redirectTo);
