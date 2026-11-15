<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$cartId = (int)($_GET['cart_id'] ?? 0);

$stmt = $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
$stmt->execute([$cartId, currentUserId()]);

flash('success', 'Item removed from cart.');
redirect('cart/cart.php');
