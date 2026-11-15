<?php
/**
 * The order-placement logic lives in checkout.php (it handles the POST
 * submission from the checkout form directly). This file is kept so the
 * folder structure matches the project documentation, and simply
 * redirects back to the checkout page.
 */
require_once __DIR__ . '/../includes/session.php';
header('Location: ' . BASE_URL . 'checkout/checkout.php');
exit;
