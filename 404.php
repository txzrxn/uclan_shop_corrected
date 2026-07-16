<?php
// Additional research: custom 404 error pages improve usability by keeping lost
// visitors inside the site instead of showing a raw server error.
// https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404
require_once __DIR__ . '/includes/auth.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="The page you were looking for could not be found on the UCLan Legacy Shop.">
    <title>Page Not Found | UCLan Legacy Shop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<a href="#main-content" class="skip-link">Skip to main content</a>
<header>
    <div class="header-inner">
        <a href="index.php" class="logo-link" aria-label="UCLan Legacy Shop Home">
            <img src="images/logo_reverse.png" alt="University of Lancashire logo">
            <span class="site-title">Legacy Shop</span>
        </a>
        <button class="hamburger" id="hamburgerBtn" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="primaryNav">&#9776;</button>
        <nav aria-label="Primary navigation" id="primaryNav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="cart.php" class="cart-nav-link" aria-label="Shopping cart">&#128722; Cart <span class="cart-count-badge" id="cartBadge">0</span></a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="account.php" class="user-greeting-link">Hi, <?php echo h($_SESSION['user_name']); ?></a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main id="main-content">
    <div class="page-title-strip">
        <h1>Page Not Found</h1>
        <p>Error 404: the page you requested does not exist on the Legacy Shop.</p>
    </div>

    <div class="empty-cart-msg">
        <div class="icon" aria-hidden="true">&#128269;</div>
        <h2>We could not find that page</h2>
        <p>The link may be out of date, or the address may have been typed incorrectly. Try one of the pages below instead.</p>
        <p>
            <a href="index.php" class="btn btn-primary">Go to Home</a>
            <a href="products.php" class="btn btn-secondary">Browse Products</a>
        </p>
    </div>
</main>

<footer>
    <p>&copy; 2026 UCLan Legacy Shop, University of Lancashire Student Union | <a href="index.php">Home</a> | <a href="products.php">Products</a> | <a href="cart.php">Cart</a></p>
</footer>

<script src="js/nav.js"></script>
</body>
</html>
