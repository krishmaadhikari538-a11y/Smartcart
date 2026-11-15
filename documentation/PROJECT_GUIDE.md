# SmartCart - Project Guide (Smart Features & Roles)

This document explains, in detail, how each smart feature works and what
permissions each user role has.

---

## A. Product Recommendation

The recommendation engine lives in `includes/functions.php` inside the
function `getRecommendedProducts(PDO $pdo, $userId, $limit = 8)`. It is a
**rule-based recommendation system** written in plain PHP and SQL - it is
explicitly NOT machine learning or AI, and is described honestly as such.

It works in the following order:

### 1. Category-based recommendation
Implemented in `getCategoryRecommendations()`. On a product detail page,
products from the same category are shown (excluding the currently viewed
product), ordered by average rating and recency.

### 2. View-history recommendation
Every time a logged-in customer opens a product detail page,
`logProductView()` inserts a row into `product_views` (user_id,
product_id, viewed_at). `getRecommendedProducts()` looks at the
customer's 5 most recently viewed products and collects their
categories.

### 3. Purchase-history recommendation
`getRecommendedProducts()` also looks at the categories of all products
the customer has previously ordered (via `order_items` joined to
`orders` and `products`).

### 4. Combining the rules
The categories gathered from steps 2 and 3 are merged (duplicates
removed). Products from those categories are fetched, ordered by average
rating and recency.

### 5. Fallback for new users / insufficient history
If the customer has no view or purchase history (or not enough matching
products), the function fills the remaining slots with the platform's
most popular products (ranked by number of times ordered) and highest
rated products. This guarantees every customer - even a brand new one -
always sees a recommendation section.

---

## B. Price Drop Notification

### How a customer creates an alert
On a product detail page, a logged-in customer clicks **"Set Price
Alert"**, which opens `alerts/set_alert.php`. The customer enters a
target price that must be lower than the current price. This is
validated server-side.

### How target prices are stored
The alert is stored in the `price_alerts` table with:
- `user_id`, `product_id`, `target_price`, `status` (`active`,
  `notified`, or `cancelled`), `created_at`, `notified_at`.

If the customer already has an active alert on that product, the target
price is simply updated instead of creating a duplicate row.

### What happens when a seller changes a price
In `seller/edit_product.php`, whenever the submitted price is lower than
the product's previous price, the seller module calls
`triggerPriceAlertCheck()` (a thin wrapper in `alerts/check_alerts.php`
around `checkPriceAlerts()` in `includes/functions.php`).

### How the new price is checked
`checkPriceAlerts($pdo, $productId, $newPrice)` selects all **active**
price alerts for that product where `target_price >= newPrice`.

### How notifications are generated
For every matching alert, a row is inserted into the `notifications`
table with a friendly message, e.g.:

> "Good news! The price of Wireless Headphones has dropped to Rs. 3,800,
> which is at or below your target price of Rs. 4,000."

The notification is linked to the product via `related_product_id`, and
`is_read` starts at `0`.

### How duplicate notifications are prevented
Immediately after creating the notification, the matching alert's status
is updated to `notified` (with a `notified_at` timestamp). Because
`checkPriceAlerts()` only looks at alerts with `status = 'active'`, an
already-notified alert will never trigger a second notification for the
same target, even if the seller lowers the price again later.

### The notification UI
- A bell icon with an unread-count badge appears in the customer
  navigation bar (`includes/navbar.php`), powered by
  `getUnreadNotificationCount()`.
- `customer/notifications.php` lists all notifications, newest first,
  and lets the customer mark one or all as read.

---

## C. Product Comparison

### How products are added
On the product listing or product detail page, an **"Add to Compare"**
button submits to `compare/add.php`, which calls `addToCompare($productId)`
in `includes/functions.php`.

### Maximum comparison limit
`addToCompare()` checks `count($_SESSION['compare_products'])`. If it is
already 3, the function returns `false` and the user sees an error
message asking them to remove a product first.

### How PHP sessions are used
The list of product IDs being compared is stored entirely in
`$_SESSION['compare_products']` - no database table is needed for this
feature, since a comparison list is inherently temporary and specific to
the current browsing session.

### How specifications are dynamically displayed
Products can have completely different specification sets (a laptop has
RAM/Processor/Storage/Display; a mobile has RAM/Battery/Camera/Storage).
Instead of hardcoding columns, all specs are stored in the flexible
`product_specifications` table (`product_id`, `specification_name`,
`specification_value`).

`compare/compare.php`:
1. Loads each compared product's specs into an associative array.
2. Builds a single list of every unique specification name that appears
   across the selected products (`$allSpecNames`).
3. Renders one table row per specification name, showing each product's
   value for that spec, or `-` if that product doesn't have that
   specification.

This means the comparison table automatically adapts to whatever
products are being compared, with no code changes needed for new product
types.

---

## D. User Roles & Permissions

### Customer
- Register, log in, log out, manage own profile
- Browse/search/filter products, view product details
- Add to cart, update quantities, remove from cart, checkout
- Add/remove wishlist items, move wishlist items to cart
- Add up to 3 products to comparison, view comparison table
- View recommended products (home page, dashboard, product page)
- Set/cancel price alerts, view notifications
- Place orders, view order history and order details
- Submit ratings and reviews

### Seller
- Log in, log out, manage own profile (via shared session, no separate
  seller profile page - kept simple for the scope of this project)
- View seller dashboard (products, orders, pending/completed counts, low
  stock, recent products)
- Add, edit, and delete their own products (delete blocked if the
  product already has orders, to preserve order history integrity)
- Upload/replace product images
- Update stock and price (price decreases automatically trigger price
  alert checks)
- View orders that contain their products, update order status
- Cannot access admin pages or another seller's products

### Administrator
- Log in securely
- View admin dashboard with system-wide statistics
- Manage customers (activate/deactivate)
- Manage sellers (approve/activate/deactivate)
- Manage categories (add/edit/delete - delete blocked if products exist
  in that category)
- View and manage all products (activate/deactivate/delete)
- View all orders and update their status
- View and moderate reviews (hide/show/delete inappropriate reviews)
- Full system-wide access; cannot be restricted by other roles
