<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$productId = (int)($_POST['product_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if ($productId <= 0 || $rating < 1 || $rating > 5 || $comment === '') {
    flash('error', 'Please provide a valid rating and comment.');
} else {
    $product = getProductById($pdo, $productId);
    if (!$product) {
        flash('error', 'Invalid product.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, comment, status) VALUES (?, ?, ?, ?, 'visible')");
        $stmt->execute([currentUserId(), $productId, $rating, $comment]);
        flash('success', 'Thank you for your review!');
    }
}

redirect('products/product_details.php?id=' . $productId);
