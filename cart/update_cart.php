<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$cartId = (int)($_POST['cart_id'] ?? 0);
$qty = (int)($_POST['quantity'] ?? 1);

$stmt = $pdo->prepare("SELECT c.*, p.stock FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.cart_id = ? AND c.user_id = ?");
$stmt->execute([$cartId, currentUserId()]);
$item = $stmt->fetch();

if ($item) {
    if ($qty <= 0) {
        $del = $pdo->prepare("DELETE FROM cart WHERE cart_id = ?");
        $del->execute([$cartId]);
    } else {
        $qty = min($qty, $item['stock']);
        $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
        $update->execute([$qty, $cartId]);
    }
} else {
    flash('error', 'Cart item not found.');
}

redirect('cart/cart.php');
