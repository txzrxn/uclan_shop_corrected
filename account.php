<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

require_login();

$orders = [];
$user_id = (int) $_SESSION['user_id'];
$order_stmt = mysqli_prepare(
    $connection,
    'SELECT order_id, order_date, product_ids FROM tbl_orders WHERE user_id = ? ORDER BY order_date DESC, order_id DESC'
);

if ($order_stmt) {
    mysqli_stmt_bind_param($order_stmt, 'i', $user_id);
    mysqli_stmt_execute($order_stmt);
    $order_result = mysqli_stmt_get_result($order_stmt);

    while ($order = mysqli_fetch_assoc($order_result)) {
        $order['payload'] = decode_order_payload($order['product_ids']);
        $orders[] = $order;
    }

    mysqli_stmt_close($order_stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UCLan Legacy Shop account and order history.">
    <title>My Account | UCLan Legacy Shop</title>
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
                <li><a href="cart.php" class="cart-nav-link">&#128722; Cart <span class="cart-count-badge" id="cartBadge">0</span></a></li>
                <li><a href="account.php" class="active" aria-current="page">Hi, <?php echo h($_SESSION['user_name']); ?></a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</header>

<main id="main-content">
    <div class="page-title-strip">
        <h1>Welcome, <?php echo h($_SESSION['user_name']); ?>!</h1>
        <p>View your profile and orders saved in the database.</p>
    </div>

    <div class="account-layout">
        <section class="account-panel" aria-labelledby="accountDetailsHeading">
            <h2 id="accountDetailsHeading">Account Details</h2>
            <p><strong>Name:</strong> <?php echo h($_SESSION['user_name']); ?></p>
            <p><strong>Email:</strong> <?php echo h($_SESSION['user_email']); ?></p>
            <div class="account-actions">
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                <a href="cart.php" class="btn btn-secondary">View Cart</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </section>

        <section class="order-history" aria-labelledby="orderHistoryHeading">
            <h2 id="orderHistoryHeading">Order History</h2>

            <?php if (!$orders): ?>
                <p class="empty-state">No orders have been saved yet.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <?php $payload = $order['payload']; ?>
                    <article class="order-card">
                        <div class="order-card-heading">
                            <h3>Order #<?php echo (int) $order['order_id']; ?></h3>
                            <time datetime="<?php echo h(date('c', strtotime($order['order_date']))); ?>"><?php echo h(date('j F Y, g:i a', strtotime($order['order_date']))); ?></time>
                        </div>

                        <?php if (!empty($payload['items']) && is_array($payload['items'])): ?>
                            <ul class="order-items">
                                <?php foreach ($payload['items'] as $item): ?>
                                    <li>
                                        <span><?php echo h($item['title'] ?? 'Product'); ?> x <?php echo (int) ($item['quantity'] ?? 0); ?></span>
                                        <strong><?php echo h(format_price($item['line_total'] ?? 0)); ?></strong>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (!empty($payload['discount_code'])): ?>
                                <p class="order-discount">Discount: <?php echo h($payload['discount_code']); ?>, -<?php echo h(format_price($payload['discount_amount'] ?? 0)); ?></p>
                            <?php endif; ?>
                            <p class="order-total">Total: <?php echo h(format_price($payload['total'] ?? 0)); ?></p>
                        <?php else: ?>
                            <p>This is an older order record: <?php echo h($payload['legacy_value'] ?? $order['product_ids']); ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</main>

<footer>
    <p>&copy; 2026 UCLan Legacy Shop, University of Lancashire Student Union | <a href="index.php">Home</a> | <a href="products.php">Products</a> | <a href="cart.php">Cart</a></p>
</footer>

<script src="js/nav.js"></script>
</body>
</html>
