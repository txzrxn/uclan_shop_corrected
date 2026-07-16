 UCLan Legacy Shop, Corrected Database Version

## Submission information (required by the brief)

- **Student name:** TAZRYANBINTE KASHEM
- **Student ID:**G21406018
- **Homepage URL:** http://localhost/kashem_tazryan/
-
- 

Both dummy accounts are inserted automatically when `uclan_shop_corrected.sql` is imported, together with two pre-populated reviews (with scores) for product 21 (Red UCLan Hoodie), so the average rating is visible on `item.php?id=21` without any manual setup.

## Version control (GitHub)

Initialise the repository at the start of development and commit after each working feature, with clear messages, for example:

```
git init
git add .
git commit -m "Add database-driven product listing with type filter"
```

Aim for regular, small commits (one feature or fix per commit) so the history can be shown during the lab demo.

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
7. Log in with the dummy account `demo@uclan.ac.uk` / `Legacy2026`, or register a new account.

## Features mapped to the 80+ criteria

- Homepage presents live offers from `tbl_offers` and greets the logged-in user by name in the hero section (PHP sessions across all pages).
- Products, categories, and the stock filter are all served by fresh MySQL queries — every filter link re-queries the database (no client-side filtering).
- Textual search uses `LIKE` with `%` wildcards on the title and description in a prepared statement.
- Each product card offers **Read More** and **Add to Basket**; guests who select Add to Basket are redirected to `login.php` and returned to the product afterwards.
- `item.php` uses a PHP GET variable (`?id=`) to query the selected product; no `sessionStorage` product data.
- `tbl_reviews` ships with two seeded, scored reviews for product 21; PHP averages the scores and shows the overall rating numerically and as stars. Logged-in users can post reviews (title, description, rating) via a prepared INSERT.
- Checkout validates prices server-side, inserts into `tbl_orders`, confirms the record, thanks the user for their custom, and empties the cart.
- Passwords use bcrypt (`password_hash`/`password_verify`); strong password validation, `htmlspecialchars()` on all output, prepared statements everywhere, CSRF tokens on all state-changing forms, and safe redirect validation on `login.php?next=`.
- Usability and accessibility: skip links, ARIA labels/live regions, breadcrumbs, responsive hamburger navigation, and a custom `404.php` error page (wired up through `.htaccess`).

The default connection in `includes/db.php` is:

```php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'uclan_shop';
```

Change these values for a different XAMPP setup or for the Vesta server.


## Manual testing checklist

demo user ID: demo@gmail.com 
password: Demo@0123

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

