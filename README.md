# UCLan Legacy Shop, Corrected Database Version

This version corrects the product, review, cart, order, login, and navigation problems in the submitted project.

## Main architecture

- Products are loaded from `tbl_products` in MySQL.
- A product is opened with `item.php?id=<product_id>`.
- Reviews are stored in `tbl_reviews` and linked to the logged-in user and selected product.
- The browser cart uses `localStorage`, but checkout is processed by `place_order.php`.
- Checkout re-reads product names, prices, and stock states from MySQL before saving an order.
- Completed order details are stored as JSON in `tbl_orders.product_ids` because that is the table structure supplied in the project.
- Order history is displayed in `account.php`.

## XAMPP setup

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin.
3. Create a database named `uclan_shop`.
4. Select the database and import `uclan_shop_corrected.sql`.
5. Copy the `uclan_shop_corrected` folder into `C:\xampp\htdocs\`.
6. Open `http://localhost/uclan_shop_corrected/`.
7. Register a new account because the clean SQL file does not include personal user records.

The default connection in `includes/db.php` is:

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'uclan_shop';
```

Change these values for a different XAMPP setup or for the Vesta server.

## Existing database option

To preserve an already imported database and its current users:

1. Back up the database first.
2. Select the existing `uclan_shop` database in phpMyAdmin.
3. Import `database_fix.sql` once.
4. Replace the project PHP, CSS, and JavaScript files with the corrected versions.

Do not run `database_fix.sql` repeatedly because it adds the `product_stock` column.

## Manual testing checklist

### Navigation

- Click the logo on Home, Products, Item, Cart, Login, Register, and Account.
- Confirm every logo opens `index.php`.
- Test the mobile hamburger menu.

### Products

- Confirm products appear from the database.
- Test category and stock filters.
- Open a product and confirm the URL contains a database ID, such as `item.php?id=21`.

### Reviews

- Open a product while logged out and confirm existing reviews are visible.
- Log in and submit a rating, title, and review.
- Confirm a new row appears in `tbl_reviews` and the review appears on the item page.

### Orders

- Add one or more products to the cart.
- Change quantities and optionally apply a discount code.
- Click Place Order.
- Confirm a row appears in `tbl_orders`.
- Open Account and confirm the order, items, quantities, and total are displayed.

### Security and validation

- Try an invalid product ID, such as `item.php?id=99999`.
- Change a cart quantity outside 1 to 10 and confirm it is rejected.
- Try to check out with an old cart item that lacks a database product ID and confirm the user is asked to re-add it.

## Discount codes

- `LEGACY10`: 10% off
- `UCLAN20`: 20% off
- `BOGOF`: 50% off
- `G21406018`: 25% off

The server validates the code again during checkout. Browser calculations are not trusted as the final database total.

## Important limitation

This is an assessment shop workflow. Place Order records an order in MySQL but does not process a real payment.

See `ISSUES_AND_FIXES.md` for the full audit.
