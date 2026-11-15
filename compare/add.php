<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$productId = (int)($_POST['product_id'] ?? 0);
$redirectTo = $_POST['redirect'] ?? 'compare/compare.php';

if ($productId > 0) {
    $ok = addToCompare($productId);
    if (!$ok) {
        flash('error', 'You can only compare up to 3 products at a time. Remove one first.');
    } else {
        flash('success', 'Product added to comparison.');
    }
}

redirect($redirectTo);
