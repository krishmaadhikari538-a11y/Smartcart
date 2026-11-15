<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$productId = (int)($_GET['product_id'] ?? 0);

if ($productId > 0) {
    removeFromCompare($productId);
}

redirect('compare/compare.php');
