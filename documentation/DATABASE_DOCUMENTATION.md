# SmartCart - Database Documentation

Database name: **smartcart_db**
Engine: InnoDB (supports foreign keys)
Character set: utf8mb4

All tables use `AUTO_INCREMENT` primary keys and appropriate foreign key
constraints with `ON DELETE CASCADE` (or `ON DELETE SET NULL` where
deleting the parent row shouldn't delete the child, such as
notifications referencing a product that was removed).

---

## 1. users

Stores all three roles: customer, seller, admin.

| Column      | Type                                  | Notes                       |
|-------------|----------------------------------------|------------------------------|
| user_id     | INT, PK, AUTO_INCREMENT                |                              |
| full_name   | VARCHAR(150)                           |                              |
| email       | VARCHAR(150), UNIQUE                   | used for login               |
| password    | VARCHAR(255)                           | bcrypt hash (password_hash)  |
| phone       | VARCHAR(20)                            |                              |
| address     | VARCHAR(255)                           |                              |
| role        | ENUM('customer','seller','admin')      |                              |
| status      | ENUM('active','inactive')              | used to block login          |
| created_at  | DATETIME                               |                              |

## 2. categories

| Column         | Type                     |
|----------------|--------------------------|
| category_id    | INT, PK, AUTO_INCREMENT  |
| category_name  | VARCHAR(100)             |
| description    | VARCHAR(255)             |
| image          | VARCHAR(255)             |
| created_at     | DATETIME                 |

## 3. products

| Column       | Type                        | Notes                              |
|--------------|------------------------------|-------------------------------------|
| product_id   | INT, PK, AUTO_INCREMENT      |                                      |
| seller_id    | INT, FK -> users.user_id     | ON DELETE CASCADE                   |
| category_id  | INT, FK -> categories.category_id | ON DELETE CASCADE              |
| product_name | VARCHAR(150)                 |                                      |
| description  | TEXT                         |                                      |
| price        | DECIMAL(10,2)                |                                      |
| stock        | INT                           |                                      |
| image        | VARCHAR(255)                 | filename inside uploads/products/   |
| status       | ENUM('active','inactive')    |                                      |
| created_at   | DATETIME                      |                                      |
| updated_at   | DATETIME (auto-update)        |                                      |

Indexes: `idx_category` (category_id), `idx_seller` (seller_id)

## 4. product_specifications

Flexible key/value specification table - supports different
specification sets per product type (e.g. laptops vs mobiles).

| Column                | Type                          |
|-----------------------|--------------------------------|
| specification_id      | INT, PK, AUTO_INCREMENT        |
| product_id            | INT, FK -> products.product_id |
| specification_name    | VARCHAR(100)                   |
| specification_value   | VARCHAR(150)                   |

## 5. cart

| Column      | Type                             | Notes                       |
|-------------|-----------------------------------|-------------------------------|
| cart_id     | INT, PK, AUTO_INCREMENT           |                                |
| user_id     | INT, FK -> users.user_id          |                                |
| product_id  | INT, FK -> products.product_id    |                                |
| quantity    | INT                                |                                |
| created_at  | DATETIME                           |                                |

Unique key `(user_id, product_id)` - one cart row per product per user;
quantity is updated instead of inserting duplicates.

## 6. wishlist

Same structure pattern as cart, minus quantity.

| Column        | Type                             |
|---------------|------------------------------------|
| wishlist_id   | INT, PK, AUTO_INCREMENT            |
| user_id       | INT, FK -> users.user_id           |
| product_id    | INT, FK -> products.product_id     |
| created_at    | DATETIME                            |

Unique key `(user_id, product_id)`.

## 7. orders

| Column            | Type                                              |
|-------------------|-----------------------------------------------------|
| order_id          | INT, PK, AUTO_INCREMENT                             |
| user_id           | INT, FK -> users.user_id                            |
| customer_name     | VARCHAR(150)                                        |
| phone             | VARCHAR(20)                                         |
| delivery_address  | VARCHAR(255)                                        |
| total_amount      | DECIMAL(10,2)                                       |
| payment_method    | ENUM('COD','Manual Payment')                        |
| order_status      | ENUM('Pending','Processing','Shipped','Delivered','Cancelled') |
| created_at        | DATETIME                                             |

## 8. order_items

| Column         | Type                             |
|----------------|------------------------------------|
| order_item_id  | INT, PK, AUTO_INCREMENT            |
| order_id       | INT, FK -> orders.order_id         |
| product_id     | INT, FK -> products.product_id     |
| seller_id      | INT, FK -> users.user_id           |
| quantity       | INT                                 |
| price          | DECIMAL(10,2)                       | price at time of order (snapshot) |

`seller_id` is duplicated here (denormalized) so sellers can query their
own order items directly without joining through products, which keeps
"orders containing my products" queries simple and fast.

## 9. reviews

| Column      | Type                                     |
|-------------|---------------------------------------------|
| review_id   | INT, PK, AUTO_INCREMENT                      |
| user_id     | INT, FK -> users.user_id                     |
| product_id  | INT, FK -> products.product_id               |
| rating      | INT, CHECK (1-5)                             |
| comment     | TEXT                                          |
| created_at  | DATETIME                                      |
| status      | ENUM('visible','hidden')                     |

## 10. product_views

Used by the view-history recommendation rule.

| Column      | Type                             |
|-------------|------------------------------------|
| view_id     | INT, PK, AUTO_INCREMENT            |
| user_id     | INT, FK -> users.user_id           |
| product_id  | INT, FK -> products.product_id     |
| viewed_at   | DATETIME                            |

## 11. price_alerts

| Column        | Type                                              |
|---------------|-----------------------------------------------------|
| alert_id      | INT, PK, AUTO_INCREMENT                              |
| user_id       | INT, FK -> users.user_id                             |
| product_id    | INT, FK -> products.product_id                       |
| target_price  | DECIMAL(10,2)                                        |
| status        | ENUM('active','notified','cancelled')                |
| created_at    | DATETIME                                              |
| notified_at   | DATETIME, nullable                                    |

## 12. notifications

| Column               | Type                                        |
|----------------------|-----------------------------------------------|
| notification_id      | INT, PK, AUTO_INCREMENT                        |
| user_id              | INT, FK -> users.user_id                       |
| title                | VARCHAR(150)                                    |
| message              | TEXT                                            |
| type                 | VARCHAR(50)                                     | e.g. 'price_drop', 'order', 'general' |
| related_product_id   | INT, FK -> products.product_id, nullable, ON DELETE SET NULL |
| is_read              | TINYINT(1)                                      |
| created_at           | DATETIME                                        |

---

## Entity Relationship Summary

```
users (1) ----< products (seller_id)
categories (1) ----< products
products (1) ----< product_specifications
users (1) ----< cart >---- (1) products
users (1) ----< wishlist >---- (1) products
users (1) ----< orders
orders (1) ----< order_items >---- (1) products
users (1) ----< reviews >---- (1) products
users (1) ----< product_views >---- (1) products
users (1) ----< price_alerts >---- (1) products
users (1) ----< notifications >---- (0..1) products
```

## Sample Data Summary

The provided `database/smartcart_db.sql` seeds:
- 1 administrator
- 3 sellers
- 5 customers
- 8 categories
- 25 products (with specifications for electronics/computers/mobiles)
- 5 sample reviews
- 8 sample product views
- 2 sample orders with order items
- 3 sample price alerts
- 2 sample notifications

All demo accounts use the password **`password`**.
