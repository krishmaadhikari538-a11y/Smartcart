# SmartCart - A Smart E-Commerce Platform

A BCA Fourth Semester Project built with **Core PHP**, **MySQL**, and **Bootstrap 5**.

SmartCart is a fully functional e-commerce web application featuring three
smart modules on top of standard e-commerce functionality:

- **Rule-based Product Recommendation** (category, view-history, and
  purchase-history based - no machine learning)
- **Price Drop Notification System**
- **Product Comparison** (up to 3 products, side by side, with dynamic
  specifications)

---

## 1. Project Description

SmartCart allows customers to browse products, manage a cart and wishlist,
compare products, receive smart recommendations, set price-drop alerts,
and place orders. Sellers can manage their own products and view/update
orders that contain their products. Administrators manage users, sellers,
categories, products, orders, and reviews.

## 2. Main Features

- Customer / Seller / Admin roles with strict role-based access control
- Product catalog with categories, search, filter, and sort
- Shopping cart and wishlist
- Product comparison (max 3 products) using PHP sessions
- Smart, rule-based product recommendations
- Price drop alert + in-app notification system
- Checkout with Cash on Delivery / Manual Payment
- Order history and order status tracking
- Product reviews and ratings
- Admin dashboard with full CRUD over users, sellers, categories, products,
  orders, and reviews
- Seller dashboard with product & order management, low-stock alerts
- Secure authentication (password hashing, prepared statements, session
  and role checks)

## 3. Technologies Used

| Layer      | Technology                          |
|------------|--------------------------------------|
| Frontend   | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Backend    | Core PHP (PDO with prepared statements) |
| Database   | MySQL                                |
| Server     | Apache (via XAMPP)                   |

## 4. System Requirements

- XAMPP (PHP 7.4+ / PHP 8.x, MySQL, Apache)
- A modern web browser (Chrome, Firefox, Edge)
- Visual Studio Code (or any code editor) - optional, for viewing the code

## 5. Installation Instructions

1. Install and open **XAMPP**.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Extract the `smartcart` folder into your XAMPP `htdocs` directory, so
   the path looks like: `C:/xampp/htdocs/smartcart/` (Windows) or
   `/opt/lampp/htdocs/smartcart/` (Linux).
4. Open your browser and go to `http://localhost/phpmyadmin`.
5. Click **New** to create a database, or simply import the SQL file
   below - it creates the database automatically.

## 6. Database Import Instructions

1. In phpMyAdmin, click the **Import** tab.
2. Click **Choose File** and select `database/smartcart_db.sql` from the
   project folder.
3. Click **Go**. This will:
   - Create the `smartcart_db` database
   - Create all required tables and relationships
   - Insert sample categories, users, products, orders, reviews, price
     alerts, and notifications

## 7. Configuration Instructions

Open `config/database.php` and confirm the following match your local
MySQL setup (defaults work for a fresh XAMPP install):

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'smartcart_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

If your MySQL root user has a password, update `DB_PASS` accordingly.

## 8. How to Run Using XAMPP

1. Make sure Apache and MySQL are running (green in XAMPP Control Panel).
2. Make sure you have imported `database/smartcart_db.sql`.
3. Open your browser and navigate to:

   ```
   http://localhost/smartcart/
   ```

4. You should see the SmartCart homepage.

## 9. Sample Login Credentials

All sample accounts use the password: **`password`**

| Role      | Email                        | Password  |
|-----------|-------------------------------|-----------|
| Admin     | admin@smartcart.com           | password  |
| Seller    | raj.seller@smartcart.com      | password  |
| Seller    | himal.seller@smartcart.com    | password  |
| Seller    | everest.seller@smartcart.com  | password  |
| Customer  | sita@smartcart.com            | password  |
| Customer  | ramesh@smartcart.com          | password  |
| Customer  | anita@smartcart.com           | password  |

You can also register new customer or seller accounts from the
Register page.

## 10. Folder Structure

```
smartcart/
├── index.php
├── README.md
├── config/database.php
├── includes/            (header, footer, navbar, auth, functions, session)
├── assets/              (css, js, images)
├── uploads/products/    (uploaded product images)
├── customer/            (dashboard, profile, orders, wishlist, alerts...)
├── seller/              (dashboard, products, orders...)
├── admin/               (dashboard, users, sellers, categories, products...)
├── products/            (product listing & details)
├── cart/                (cart management)
├── wishlist/
├── compare/
├── alerts/              (price alert logic)
├── auth/                (login, register, logout)
├── checkout/
├── database/smartcart_db.sql
└── documentation/       (PROJECT_GUIDE, DATABASE_DOCUMENTATION, TEST_CASES)
```

## 11. Smart Feature Explanation (Summary)

See `documentation/PROJECT_GUIDE.md` for full details. In short:

- **Recommendation**: `getRecommendedProducts()` in `includes/functions.php`
  combines a customer's recently viewed categories and purchased
  categories, and falls back to popular/highly-rated products when there
  isn't enough history. This is plain rule-based SQL/PHP logic - not
  machine learning.
- **Price Drop Notification**: When a seller updates a product's price
  (`seller/edit_product.php`), `checkPriceAlerts()` checks all active
  alerts for that product and creates a notification for every customer
  whose target price has been met, then marks that alert as `notified` to
  avoid duplicate notifications.
- **Product Comparison**: Product IDs are stored in
  `$_SESSION['compare_products']` (max 3). The compare page dynamically
  builds a table of all specification names found across the selected
  products, pulling values from the flexible `product_specifications`
  table.

## 12. Known Limitations

- No real payment gateway integration (Cash on Delivery / Manual Payment
  only, as required for a student project).
- No email/SMS notifications - all notifications are in-app only.
- Recommendation logic is rule-based, not AI/ML-based.
- Single product image per product (no image gallery).

## 13. Future Improvements

- Add a proper payment gateway integration (eSewa/Khalti for Nepal).
- Add email notifications for price drops and order status changes.
- Add pagination for large product catalogs.
- Add an admin analytics/reports section with charts.
- Add multi-image support per product.

---

**Project Type:** BCA Fourth Semester Project
**Built for demonstration and viva purposes.**
