<?php
/**
 * Reusable helper functions used across the SmartCart project.
 * Requires config/database.php ($pdo) to be loaded before use.
 */

/* ---------------------------------------------------------
   GENERAL HELPERS
--------------------------------------------------------- */

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function formatPrice($price) {
    return 'Rs. ' . number_format((float)$price, 2);
}

function redirect($path) {
    header('Location: ' . BASE_URL . $path);
    exit;
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return;
    }
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}

/* ---------------------------------------------------------
   PRODUCT HELPERS
--------------------------------------------------------- */

function getProductById(PDO $pdo, $productId) {
    $stmt = $pdo->prepare("SELECT p.*, c.category_name, u.full_name AS seller_name
                            FROM products p
                            JOIN categories c ON p.category_id = c.category_id
                            JOIN users u ON p.seller_id = u.user_id
                            WHERE p.product_id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch();
}

function getProductSpecifications(PDO $pdo, $productId) {
    $stmt = $pdo->prepare("SELECT specification_name, specification_value FROM product_specifications WHERE product_id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function getAverageRating(PDO $pdo, $productId) {
    $stmt = $pdo->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM reviews WHERE product_id = ? AND status = 'visible'");
    $stmt->execute([$productId]);
    $row = $stmt->fetch();
    return [
        'avg'   => $row['avg_rating'] ? round($row['avg_rating'], 1) : 0,
        'total' => (int)$row['total'],
    ];
}

function getProductImageUrl($image) {
    if (!empty($image) && file_exists(__DIR__ . '/../uploads/products/' . $image)) {
        return BASE_URL . 'uploads/products/' . $image;
    }
    return BASE_URL . 'assets/images/placeholder.jpg';
}

/**
 * Records that a logged-in user viewed a product (used for recommendations).
 */
function logProductView(PDO $pdo, $userId, $productId) {
    $stmt = $pdo->prepare("INSERT INTO product_views (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$userId, $productId]);
}

/* ---------------------------------------------------------
   SMART FEATURE 1: RULE-BASED PRODUCT RECOMMENDATION
   (No machine learning - simple, understandable SQL rules)
--------------------------------------------------------- */

/**
 * Returns a list of recommended products for a product detail page,
 * based on products from the same category (excluding the product itself).
 */
function getCategoryRecommendations(PDO $pdo, $categoryId, $excludeProductId, $limit = 4) {
    $stmt = $pdo->prepare("SELECT p.*, 
                                  (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.product_id) AS avg_rating
                           FROM products p
                           WHERE p.category_id = ? AND p.product_id != ? AND p.status = 'active'
                           ORDER BY avg_rating DESC, p.created_at DESC
                           LIMIT " . (int)$limit);
    $stmt->execute([$categoryId, $excludeProductId]);
    return $stmt->fetchAll();
}

/**
 * Main smart recommendation function.
 * Combines: view-history categories + purchase-history categories.
 * Falls back to popular/highly-rated products for new users with no history.
 *
 * This is a rule-based recommendation engine written in plain PHP and SQL.
 * It is intentionally simple and explainable - it is NOT an AI/ML model.
 */
function getRecommendedProducts(PDO $pdo, $userId, $limit = 8) {
    $categoryIds = [];

    if ($userId) {
        // 1) Categories from recently viewed products
        $stmt = $pdo->prepare("SELECT DISTINCT p.category_id
                                FROM product_views pv
                                JOIN products p ON pv.product_id = p.product_id
                                WHERE pv.user_id = ?
                                ORDER BY pv.viewed_at DESC
                                LIMIT 5");
        $stmt->execute([$userId]);
        foreach ($stmt->fetchAll() as $row) {
            $categoryIds[] = $row['category_id'];
        }

        // 2) Categories from previous purchases
        $stmt = $pdo->prepare("SELECT DISTINCT p.category_id
                                FROM order_items oi
                                JOIN orders o ON oi.order_id = o.order_id
                                JOIN products p ON oi.product_id = p.product_id
                                WHERE o.user_id = ?");
        $stmt->execute([$userId]);
        foreach ($stmt->fetchAll() as $row) {
            $categoryIds[] = $row['category_id'];
        }
    }

    $categoryIds = array_values(array_unique($categoryIds));

    $products = [];

    if (!empty($categoryIds)) {
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        $sql = "SELECT p.*, (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.product_id) AS avg_rating
                FROM products p
                WHERE p.category_id IN ($placeholders) AND p.status = 'active'
                ORDER BY avg_rating DESC, p.created_at DESC
                LIMIT " . (int)$limit;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($categoryIds);
        $products = $stmt->fetchAll();
    }

    // Fallback: not enough history-based results -> fill with popular/highly rated products
    if (count($products) < $limit) {
        $existingIds = array_column($products, 'product_id');
        $needed = $limit - count($products);

        $sql = "SELECT p.*, (SELECT AVG(rating) FROM reviews r WHERE r.product_id = p.product_id) AS avg_rating,
                       (SELECT COUNT(*) FROM order_items oi WHERE oi.product_id = p.product_id) AS times_ordered
                FROM products p
                WHERE p.status = 'active'";
        if (!empty($existingIds)) {
            $placeholders = implode(',', array_fill(0, count($existingIds), '?'));
            $sql .= " AND p.product_id NOT IN ($placeholders)";
        }
        $sql .= " ORDER BY times_ordered DESC, avg_rating DESC, p.created_at DESC LIMIT " . (int)$needed;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($existingIds);
        $fallback = $stmt->fetchAll();
        $products = array_merge($products, $fallback);
    }

    return $products;
}

/* ---------------------------------------------------------
   SMART FEATURE 2: PRICE DROP NOTIFICATION
--------------------------------------------------------- */

/**
 * Call this function right after a product's price has been updated.
 * It checks all active price alerts for that product and creates a
 * notification for every customer whose target price has been met.
 */
function checkPriceAlerts(PDO $pdo, $productId, $newPrice) {
    $stmt = $pdo->prepare("SELECT pa.*, p.product_name
                            FROM price_alerts pa
                            JOIN products p ON pa.product_id = p.product_id
                            WHERE pa.product_id = ? AND pa.status = 'active' AND pa.target_price >= ?");
    $stmt->execute([$productId, $newPrice]);
    $alerts = $stmt->fetchAll();

    foreach ($alerts as $alert) {
        // Create notification
        $title = "Price Drop Alert!";
        $message = "Good news! The price of " . $alert['product_name'] . " has dropped to " .
                    formatPrice($newPrice) . ", which is at or below your target price of " .
                    formatPrice($alert['target_price']) . ".";

        $insert = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, related_product_id, is_read)
                                  VALUES (?, ?, ?, 'price_drop', ?, 0)");
        $insert->execute([$alert['user_id'], $title, $message, $productId]);

        // Mark alert as notified so we don't send duplicate notifications for the same drop
        $update = $pdo->prepare("UPDATE price_alerts SET status = 'notified', notified_at = NOW() WHERE alert_id = ?");
        $update->execute([$alert['alert_id']]);
    }

    return count($alerts);
}

function getUnreadNotificationCount(PDO $pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetch()['cnt'];
}

/* ---------------------------------------------------------
   SMART FEATURE 3: PRODUCT COMPARISON (session based)
--------------------------------------------------------- */

function getCompareList() {
    if (!isset($_SESSION['compare_products'])) {
        $_SESSION['compare_products'] = [];
    }
    return $_SESSION['compare_products'];
}

function addToCompare($productId) {
    $list = getCompareList();
    if (!in_array($productId, $list)) {
        if (count($list) >= 3) {
            return false; // limit reached
        }
        $list[] = $productId;
        $_SESSION['compare_products'] = $list;
    }
    return true;
}

function removeFromCompare($productId) {
    $list = getCompareList();
    $list = array_values(array_diff($list, [$productId]));
    $_SESSION['compare_products'] = $list;
}

function clearCompare() {
    $_SESSION['compare_products'] = [];
}

/* ---------------------------------------------------------
   CART / STOCK HELPERS
--------------------------------------------------------- */

function getCartCount(PDO $pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) AS cnt FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetch()['cnt'];
}

function getWishlistCount(PDO $pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM wishlist WHERE user_id = ?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetch()['cnt'];
}

/* ---------------------------------------------------------
   IMAGE UPLOAD HELPER
--------------------------------------------------------- */

function handleProductImageUpload($fileInputName) {
    if (empty($_FILES[$fileInputName]['name'])) {
        return null; // no file uploaded, not an error
    }

    $file = $_FILES[$fileInputName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File upload error.");
    }

    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt)) {
        throw new Exception("Invalid image type. Allowed types: jpg, jpeg, png, webp.");
    }

    $maxSize = 3 * 1024 * 1024; // 3 MB
    if ($file['size'] > $maxSize) {
        throw new Exception("Image size must be less than 3MB.");
    }

    $uploadDir = __DIR__ . '/../uploads/products/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $newName = uniqid('product_', true) . '.' . $ext;
    $destination = $uploadDir . $newName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception("Failed to save uploaded image.");
    }

    return $newName;
}
