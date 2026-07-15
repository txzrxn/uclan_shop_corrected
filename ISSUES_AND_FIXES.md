# UCLan Shop Project Review

## Issues found

| # | Area | Issue | Effect |
|---|---|---|---|
| 1 | `products.php` | The logo linked to `index.html`, but the project uses `index.php`. | Clicking the logo opened a missing page instead of Home. |
| 2 | `cart.php` | Cart item links pointed to `item.html`, which does not exist. | The View item action was broken. |
| 3 | `products.php` | Products were selected from MySQL into `$products`, but the page ignored them and rendered a separate hard-coded JavaScript array. | Database edits did not appear in the catalogue. |
| 4 | `item.php` | The `id` query parameter was ignored. Product details came from `sessionStorage` and another hard-coded array. | Direct product URLs were unreliable and data could differ from the database. |
| 5 | Product data | Prices, titles, stock states, and product counts differed between JavaScript and SQL. | The catalogue, item page, cart, and database could describe different products. |
| 6 | Reviews | `tbl_reviews` existed, but there was no working review form, INSERT query, SELECT query, or review output. | Reviews could neither be submitted nor displayed. |
| 7 | Orders | Proceed to Checkout only displayed a toast message. | No row was inserted into `tbl_orders`. |
| 8 | Cart data | Cart records stored only a name, image, price, and quantity. They did not store the database `product_id`. | The server could not reliably connect a cart item to a database product. |
| 9 | Order integrity | The original cart trusted prices stored in the browser. | A user could alter `localStorage` and change a price before checkout. |
| 10 | Cart security | Cart content from `localStorage` was inserted into `innerHTML`. | Tampered browser data could inject unwanted markup into the page. |
| 11 | Missing JavaScript | Account, login, and registration pages referenced missing `js/nav.js` and `js/products-data.js`. | Mobile navigation and cart badges could fail, with browser 404 errors. |
| 12 | Registration header | The registration page logo markup was commented out. | The header displayed an empty logo link. |
| 13 | `conn.php` | It connected to `co1707_lab_login`, while the project uses `uclan_shop`. | Any page using it connected to the wrong database. |
| 14 | PHP structure | `includes/functions.php` duplicated authentication functions from `includes/auth.php`. | Including both files could cause fatal function redeclaration errors. |
| 15 | `item.php` | `includes/auth.php` was required twice. | Redundant code and poor maintainability. |
| 16 | SQL data | Product 1 used an empty value for a non-empty `product_type` enum. | Import could fail in strict SQL mode or create an invalid enum value. |
| 17 | SQL data | Multiple T-shirts had the same title despite using different images. | Item identification and order records were ambiguous. |
| 18 | Database connection | UTF-8 database configuration was commented out. | Some names and review text could be stored or displayed incorrectly. |
| 19 | Account page | No order history was queried or displayed. | Even a successfully saved order would not be visible to the user. |
| 20 | Form security | Login, registration, review, and checkout actions lacked CSRF protection. | State-changing actions were not protected against forged requests. |
| 21 | Error handling | Raw database errors were shown with `die()`. | Internal database details could be exposed to users. |
| 22 | Project package | It contained duplicate and obsolete CSS, SQL, array, video, and editor files. | Students could edit or import the wrong file. |

## Corrections implemented

1. `products.php` now renders database products and uses real product IDs in links.
2. `item.php` loads one product from MySQL using `item.php?id=<product_id>`.
3. A complete review form now validates and inserts reviews using a prepared statement.
4. Existing reviews are loaded by joining `tbl_reviews` with `users`.
5. Cart records now include `productId`.
6. `place_order.php` validates every product and price against MySQL, recalculates the total on the server, and inserts the order into `tbl_orders`.
7. `account.php` now displays saved order history.
8. Broken `.html` links and missing logo links were corrected.
9. A shared `js/nav.js` file now supports both header variants and updates cart badges.
10. CSRF tokens were added to login, registration, review submission, and checkout.
11. Cart rendering now uses DOM methods and `textContent` rather than trusting browser HTML.
12. Database connection configuration now sets `utf8mb4` and gives a safer public error message.
13. `conn.php` now delegates to the correct shared connection file.
14. Duplicate authentication functions were removed from `includes/functions.php`.
15. A clean database dump and a migration file are included.
16. Obsolete duplicate project files were removed from the corrected package.

## Corrected and new files

- `products.php`
- `item.php`
- `cart.php`
- `place_order.php` (new)
- `account.php`
- `login.php`
- `register.php`
- `conn.php`
- `includes/auth.php`
- `includes/db.php`
- `includes/functions.php`
- `js/nav.js` (new)
- `js/products-data.js` (compatibility note)
- `style.css`
- `uclan_shop_corrected.sql` (clean installation)
- `database_fix.sql` (migration for an existing database)
- `README.md`

## Verification completed

- Every PHP file passes `php -l` syntax validation.
- External JavaScript files pass Node syntax checking.
- Literal local file links were checked for missing targets.
- Old references to `index.html`, `item.html`, the wrong database name, and session-based product selection were removed.

A live MySQL browser test could not be performed in the review environment because it does not provide a running MySQL server. The PHP and SQL workflows were checked statically, and the corrected package includes setup and manual test steps.
