<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireCustomer();

$wishlistId = (int)($_GET['wishlist_id'] ?? 0);

$stmt = $pdo->prepare("DELETE FROM wishlist WHERE wishlist_id = ? AND user_id = ?");
$stmt->execute([$wishlistId, currentUserId()]);

flash('success', 'Removed from wishlist.');
redirect('customer/wishlist.php');
