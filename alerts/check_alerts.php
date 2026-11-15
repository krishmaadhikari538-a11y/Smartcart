<?php
/**
 * This file is not meant to be opened directly in the browser.
 * It exposes a small wrapper around checkPriceAlerts() and is included
 * automatically by seller/edit_product.php whenever a seller updates a
 * product's price. See includes/functions.php for the actual logic.
 */

require_once __DIR__ . '/../includes/functions.php';

function triggerPriceAlertCheck(PDO $pdo, $productId, $newPrice) {
    return checkPriceAlerts($pdo, $productId, $newPrice);
}
