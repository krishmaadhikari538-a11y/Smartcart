# SmartCart - Test Cases

Each test case includes: Test Case ID, Module, Test Description, Input,
Expected Result, Actual Result, and Status. "Actual Result" and "Status"
are left for the tester to fill in during manual testing / viva
demonstration.

| Test Case ID | Module | Test Description | Input | Expected Result | Actual Result | Status |
|---|---|---|---|---|---|---|
| TC-01 | Registration | Register a new customer with valid details | Valid name, unique email, phone, address, password (6+ chars), matching confirm password | Account created, redirected to login with success message | | |
| TC-02 | Registration | Register with an already-used email | Existing email (e.g. sita@smartcart.com) | Error: "This email is already registered." | | |
| TC-03 | Registration | Register with mismatched passwords | password="abc123", confirm="abc321" | Error: "Passwords do not match." | | |
| TC-04 | Registration | Register with a short password | password="123" | Error: "Password must be at least 6 characters." | | |
| TC-05 | Login | Login with valid customer credentials | sita@smartcart.com / password | Redirected to customer dashboard | | |
| TC-06 | Login | Login with invalid password | sita@smartcart.com / wrongpass | Error: "Invalid email or password." | | |
| TC-07 | Login | Login with non-existent email | notreal@smartcart.com / password | Error: "Invalid email or password." | | |
| TC-08 | Role-based Access | Customer tries to open /admin/dashboard.php directly | Logged in as customer, visit admin URL | Redirected away with unauthorized error | | |
| TC-09 | Role-based Access | Seller tries to open /admin/dashboard.php directly | Logged in as seller, visit admin URL | Redirected away with unauthorized error | | |
| TC-10 | Role-based Access | Unauthenticated user tries to open /customer/dashboard.php | Not logged in, visit customer dashboard URL | Redirected to login page | | |
| TC-11 | Add Product | Seller adds a new product with valid data and image | Name, category, description, price, stock, image file | Product saved, appears in seller's product list | | |
| TC-12 | Add Product | Seller adds a product with an invalid image type | Upload a .txt file as image | Error: "Invalid image type." | | |
| TC-13 | Edit Product | Seller edits a product and lowers the price below a customer's alert target | Existing product with an active price alert, new price <= target price | Price updated; notification created for that customer; alert status becomes 'notified' | | |
| TC-14 | Edit Product | Seller edits a product and raises the price | New price higher than old price | Price updated; no price alert notifications triggered | | |
| TC-15 | Delete Product | Seller attempts to delete a product that has existing orders | Product with order history | Deletion blocked with explanatory error message | | |
| TC-16 | Add to Cart | Customer adds an in-stock product to the cart | Valid product with stock > 0 | Product appears in cart, cart badge count increases | | |
| TC-17 | Add to Cart | Customer attempts to add an out-of-stock product | Product with stock = 0 | Error: "This product is currently out of stock." | | |
| TC-18 | Update Cart | Customer updates quantity beyond available stock | Quantity greater than stock | Quantity is capped at the maximum available stock | | |
| TC-19 | Checkout | Customer places an order with a full cart and valid details | Valid name, phone, address, COD payment | Order created, order items created, stock reduced, cart cleared | | |
| TC-20 | Checkout / Stock Validation | Two customers try to buy the last unit of the same product around the same time | Concurrent orders for a product with stock = 1 | Second order is blocked with an insufficient stock error before submission completes | | |
| TC-21 | Wishlist | Customer adds a product to the wishlist | Valid product | Product appears in "My Wishlist" | | |
| TC-22 | Wishlist | Customer moves a wishlist item to the cart | Product already in wishlist | Product added to cart via the "Move to Cart" button | | |
| TC-23 | Product Comparison | Customer adds 3 different products to compare | 3 valid product IDs | All 3 appear in the comparison table with specs displayed side by side | | |
| TC-24 | Product Comparison | Customer attempts to add a 4th product to compare | 4th product ID while 3 are already selected | Error: "You can only compare up to 3 products at a time." | | |
| TC-25 | Smart Recommendation | New customer with no view/order history visits homepage | New customer account | Recommended section falls back to popular/highly-rated products | | |
| TC-26 | Smart Recommendation | Customer who viewed several Electronics products visits homepage | Customer with product_views in Electronics category | Recommended section includes other Electronics products | | |
| TC-27 | Price Alert Creation | Customer sets a price alert with a target price lower than the current price | Target price < current price | Alert saved with status 'active' | | |
| TC-28 | Price Alert Creation | Customer sets a price alert with a target price higher than or equal to the current price | Target price >= current price | Error: "Target price must be lower than the current price." | | |
| TC-29 | Price Drop Notification | Seller lowers price to or below a customer's target price | Product price update meeting an active alert's target | Notification created for the customer; unread badge count increases | | |
| TC-30 | Price Drop Notification | Seller lowers the price again after an alert was already marked 'notified' | Same product, alert already notified | No duplicate notification is created for that same alert | | |
| TC-31 | Review Submission | Customer submits a rating and comment for a product | Rating 1-5, non-empty comment | Review saved and displayed under the product with visible status | | |
| TC-32 | Review Submission | Customer submits a review with no comment | Empty comment field | Error: "Please provide a valid rating and comment." | | |
| TC-33 | Admin - Category Management | Admin deletes a category that has no products | Empty category | Category deleted successfully | | |
| TC-34 | Admin - Category Management | Admin attempts to delete a category with existing products | Category with assigned products | Deletion blocked with explanatory error message | | |
| TC-35 | Admin - Review Moderation | Admin hides an inappropriate review | Existing visible review | Review status changes to 'hidden' and disappears from the product page | | |
| TC-36 | Admin - Order Management | Admin updates an order's status | Existing order, new status selected | Order status updated and reflected on customer's order page | | |
| TC-37 | Notifications | Customer marks a single notification as read | One unread notification | Notification's unread styling disappears; unread badge count decreases | | |
| TC-38 | Notifications | Customer marks all notifications as read | Multiple unread notifications | All notifications marked as read; badge count becomes 0 | | |
| TC-39 | Logout | Logged-in user clicks Logout | Active session | Session destroyed; redirected to login page; protected pages become inaccessible | | |
| TC-40 | Seller Order Management | Seller updates the status of an order containing their product | Valid order ID, new status | Order status updated; visible to admin and the customer | | |

## How to Use This Document

1. Perform each test case manually using the demo accounts provided in
   the README.
2. Record the actual result observed.
3. Mark the Status column as **Pass** or **Fail**.
4. Use this table during the project viva/demonstration to show that all
   core functionality has been verified.
