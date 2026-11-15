-- =========================================================
-- SmartCart Database
-- =========================================================

DROP DATABASE IF EXISTS smartcart_db;
CREATE DATABASE smartcart_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smartcart_db;

-- ---------------------------------------------------------
-- users
-- ---------------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    role ENUM('customer','seller','admin') NOT NULL DEFAULT 'customer',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- categories
-- ---------------------------------------------------------
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- products
-- ---------------------------------------------------------
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_seller (seller_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- product_specifications
-- ---------------------------------------------------------
CREATE TABLE product_specifications (
    specification_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    specification_name VARCHAR(100) NOT NULL,
    specification_value VARCHAR(150) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- cart
-- ---------------------------------------------------------
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_cart (user_id, product_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- wishlist
-- ---------------------------------------------------------
CREATE TABLE wishlist (
    wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY uniq_wishlist (user_id, product_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- orders
-- ---------------------------------------------------------
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    delivery_address VARCHAR(255) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('COD','Manual Payment') NOT NULL DEFAULT 'COD',
    order_status ENUM('Pending','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- order_items
-- ---------------------------------------------------------
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    seller_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- reviews
-- ---------------------------------------------------------
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- product_views
-- ---------------------------------------------------------
CREATE TABLE product_views (
    view_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    viewed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- price_alerts
-- ---------------------------------------------------------
CREATE TABLE price_alerts (
    alert_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    target_price DECIMAL(10,2) NOT NULL,
    status ENUM('active','notified','cancelled') NOT NULL DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    notified_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- notifications
-- ---------------------------------------------------------
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    related_product_id INT DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (related_product_id) REFERENCES products(product_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- SAMPLE DATA
-- =========================================================

-- Passwords below are all: password
-- Hash generated with PHP password_hash('password', PASSWORD_DEFAULT)
-- (all users share this hash for demo login convenience)
INSERT INTO users (full_name, email, password, phone, address, role, status) VALUES
('Site Administrator', 'admin@smartcart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9800000001', 'Kathmandu, Nepal', 'admin', 'active'),
('Raj Electronics', 'raj.seller@smartcart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9800000002', 'New Road, Kathmandu', 'seller', 'active'),
('Himal Fashion House', 'himal.seller@smartcart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9800000003', 'Putalisadak, Kathmandu', 'seller', 'active'),
('Everest Home Store', 'everest.seller@smartcart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9800000004', 'Baneshwor, Kathmandu', 'seller', 'active'),
('Sita Sharma', 'sita@smartcart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9811111111', 'Lalitpur, Nepal', 'customer', 'active'),
('Ramesh Thapa', 'ramesh@smartcart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9811111112', 'Bhaktapur, Nepal', 'customer', 'active'),
('Anita Gurung', 'anita@smartcart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9811111113', 'Kathmandu, Nepal', 'customer', 'active'),
('Bikash Rai', 'bikash@smartcart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9811111114', 'Pokhara, Nepal', 'customer', 'active'),
('Puja Karki', 'puja@smartcart.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9811111115', 'Chitwan, Nepal', 'customer', 'active');

INSERT INTO categories (category_name, description, image) VALUES
('Electronics', 'Electronic gadgets and devices', 'electronics.jpg'),
('Mobile Phones', 'Smartphones and accessories', 'mobiles.jpg'),
('Computers', 'Laptops, desktops and accessories', 'computers.jpg'),
('Fashion', 'Clothing and apparel', 'fashion.jpg'),
('Home and Kitchen', 'Home appliances and kitchenware', 'home.jpg'),
('Books', 'Books of all genres', 'books.jpg'),
('Sports', 'Sports and fitness equipment', 'sports.jpg'),
('Accessories', 'Watches, bags and other accessories', 'accessories.jpg');

-- Products (seller_id: 2=Raj Electronics, 3=Himal Fashion, 4=Everest Home)
INSERT INTO products (seller_id, category_id, product_name, description, price, stock, image, status) VALUES
(2, 2, 'Samsung Galaxy A14', 'Affordable smartphone with 6.6 inch display and 50MP camera.', 24999.00, 25, 'placeholder.jpg', 'active'),
(2, 2, 'iPhone 13', 'Apple iPhone 13 with A15 Bionic chip.', 119999.00, 10, 'placeholder.jpg', 'active'),
(2, 2, 'Redmi Note 12', 'Budget-friendly smartphone with AMOLED display.', 22999.00, 30, 'placeholder.jpg', 'active'),
(2, 3, 'Dell Inspiron 15', 'Core i5 laptop, 8GB RAM, 512GB SSD.', 84999.00, 12, 'placeholder.jpg', 'active'),
(2, 3, 'HP Pavilion 14', 'Core i5, 16GB RAM, lightweight laptop.', 92999.00, 8, 'placeholder.jpg', 'active'),
(2, 3, 'Lenovo IdeaPad Slim 3', 'Ryzen 5, 8GB RAM budget laptop.', 69999.00, 15, 'placeholder.jpg', 'active'),
(2, 1, 'Sony WH-CH510 Headphones', 'Wireless on-ear headphones with long battery life.', 4999.00, 40, 'placeholder.jpg', 'active'),
(2, 1, 'JBL Flip 6 Speaker', 'Portable Bluetooth speaker with deep bass.', 12999.00, 20, 'placeholder.jpg', 'active'),
(2, 1, 'Mi Smart Band 7', 'Fitness tracker with heart rate monitor.', 5499.00, 35, 'placeholder.jpg', 'active'),
(2, 1, 'Canon EOS 1500D DSLR', 'Entry-level DSLR camera with 18-55mm lens.', 54999.00, 6, 'placeholder.jpg', 'active'),
(3, 4, 'Men Formal Shirt', 'Cotton formal shirt, slim fit.', 1499.00, 60, 'placeholder.jpg', 'active'),
(3, 4, 'Women Kurti Set', 'Printed cotton kurti with palazzo.', 1899.00, 45, 'placeholder.jpg', 'active'),
(3, 4, 'Denim Jacket', 'Unisex denim jacket, casual wear.', 2999.00, 25, 'placeholder.jpg', 'active'),
(3, 4, 'Sports Shoes', 'Running shoes with cushioned sole.', 3499.00, 50, 'placeholder.jpg', 'active'),
(3, 8, 'Leather Wallet', 'Genuine leather bifold wallet.', 999.00, 70, 'placeholder.jpg', 'active'),
(3, 8, 'Analog Wrist Watch', 'Stainless steel analog watch.', 2499.00, 30, 'placeholder.jpg', 'active'),
(3, 8, 'Travel Backpack', '35L water-resistant travel backpack.', 2799.00, 28, 'placeholder.jpg', 'active'),
(4, 5, 'Non-stick Cookware Set', '5-piece non-stick cookware set.', 3999.00, 22, 'placeholder.jpg', 'active'),
(4, 5, 'Electric Kettle 1.8L', 'Stainless steel electric kettle.', 1599.00, 40, 'placeholder.jpg', 'active'),
(4, 5, 'Induction Cooktop', '2000W induction cooktop with touch panel.', 3299.00, 18, 'placeholder.jpg', 'active'),
(4, 5, 'Mixer Grinder 750W', '3-jar mixer grinder for kitchen use.', 4499.00, 20, 'placeholder.jpg', 'active'),
(4, 7, 'Yoga Mat', 'Anti-slip yoga and exercise mat.', 899.00, 55, 'placeholder.jpg', 'active'),
(4, 7, 'Dumbbell Set 10kg', 'Adjustable dumbbell set for home workout.', 3499.00, 15, 'placeholder.jpg', 'active'),
(4, 6, 'Learn PHP and MySQL', 'Beginner to advanced guide on PHP and MySQL.', 799.00, 33, 'placeholder.jpg', 'active'),
(4, 6, 'Introduction to Database Systems', 'Textbook covering DBMS fundamentals.', 999.00, 24, 'placeholder.jpg', 'active'),
(4, 6, 'The Art of Web Design', 'Practical guide to modern web design.', 649.00, 27, 'placeholder.jpg', 'active');

-- Product specifications (flexible key-value)
INSERT INTO product_specifications (product_id, specification_name, specification_value) VALUES
(1,'RAM','4GB'),(1,'Storage','128GB'),(1,'Battery','5000mAh'),(1,'Camera','50MP'),
(2,'RAM','4GB'),(2,'Storage','128GB'),(2,'Battery','3240mAh'),(2,'Camera','12MP Dual'),
(3,'RAM','6GB'),(3,'Storage','128GB'),(3,'Battery','5000mAh'),(3,'Camera','48MP'),
(4,'RAM','8GB'),(4,'Processor','Intel Core i5'),(4,'Storage','512GB SSD'),(4,'Display','15.6 inch FHD'),
(5,'RAM','16GB'),(5,'Processor','Intel Core i5'),(5,'Storage','512GB SSD'),(5,'Display','14 inch FHD'),
(6,'RAM','8GB'),(6,'Processor','AMD Ryzen 5'),(6,'Storage','256GB SSD'),(6,'Display','15.6 inch FHD'),
(7,'Type','On-Ear'),(7,'Battery Life','35 hours'),(7,'Connectivity','Bluetooth 5.0'),
(8,'Battery Life','12 hours'),(8,'Waterproof','IP67'),(8,'Connectivity','Bluetooth 5.1'),
(9,'Display','AMOLED'),(9,'Battery Life','14 days'),(9,'Water Resistance','5ATM'),
(10,'Sensor','24.1 MP APS-C'),(10,'Video','Full HD'),(10,'Lens','18-55mm');

-- Sample reviews
INSERT INTO reviews (user_id, product_id, rating, comment, status) VALUES
(5, 1, 5, 'Great phone for the price!', 'visible'),
(6, 1, 4, 'Good battery life.', 'visible'),
(7, 4, 5, 'Excellent laptop for coding.', 'visible'),
(8, 7, 4, 'Comfortable and good sound.', 'visible'),
(5, 24, 5, 'Very helpful book for learning PHP.', 'visible');

-- Sample product views (for view-history recommendation)
INSERT INTO product_views (user_id, product_id) VALUES
(5, 1), (5, 3), (5, 2), (6, 4), (6, 5), (7, 7), (7, 8), (7, 9);

-- Sample orders
INSERT INTO orders (user_id, customer_name, phone, delivery_address, total_amount, payment_method, order_status) VALUES
(5, 'Sita Sharma', '9811111111', 'Lalitpur, Nepal', 29998.00, 'COD', 'Delivered'),
(6, 'Ramesh Thapa', '9811111112', 'Bhaktapur, Nepal', 84999.00, 'COD', 'Processing');

INSERT INTO order_items (order_id, product_id, seller_id, quantity, price) VALUES
(1, 1, 2, 1, 24999.00),
(1, 7, 2, 1, 4999.00),
(2, 4, 2, 1, 84999.00);

-- Sample price alerts
INSERT INTO price_alerts (user_id, product_id, target_price, status) VALUES
(5, 2, 100000.00, 'active'),
(6, 4, 80000.00, 'active'),
(7, 7, 4500.00, 'active');

-- Sample notifications
INSERT INTO notifications (user_id, title, message, type, related_product_id, is_read) VALUES
(5, 'Welcome to SmartCart', 'Thank you for joining SmartCart. Start exploring smart deals now!', 'general', NULL, 0),
(6, 'Order Update', 'Your order #2 is now being processed.', 'order', NULL, 0);
