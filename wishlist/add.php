<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$productId = (int)($_POST['product_id'] ?? 0);
$redirectTo = $_POST['redirect'] ?? 'customer/wishlist.php';

$stmt = $pdo->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([currentUserId(), $productId]);

if ($stmt->fetch()) {
    $del = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $del->execute([currentUserId(), $productId]);
    flash('success', 'Removed from wishlist.');
} else {
    $insert = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $insert->execute([currentUserId(), $productId]);
    flash('success', 'Added to wishlist.');
}

redirect($redirectTo);
