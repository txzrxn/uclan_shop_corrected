<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

if (is_logged_in()) {
    header("Location: account.php");
    exit;
}

function safe_next_destination($value)
{
    $value = trim((string) $value);
    $parts = parse_url($value);
    $allowed_paths = ["account.php", "cart.php", "index.php", "products.php", "item.php"];

    if ($parts === false || isset($parts['scheme']) || isset($parts['host'])) {
        return "account.php";
    }

    $path = $parts['path'] ?? "account.php";
    if (!in_array($path, $allowed_paths, true)) {
        return "account.php";
    }

    $destination = $path;
    if (!empty($parts['query'])) {
        $destination .= '?' . $parts['query'];
    }
    if (!empty($parts['fragment'])) {
        $destination .= '#' . $parts['fragment'];
    }

    return $destination;
}

$errors = [];
$email = "";
$next = safe_next_destination($_GET['next'] ?? "account.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "The login form session expired. Refresh the page and try again.";
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $next = safe_next_destination($_POST['next'] ?? "account.php");

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if ($password === '') {
        $errors[] = "Enter your password.";
    }

    if (!$errors) {
        $stmt = mysqli_prepare($connection, "SELECT id, full_name, email, password_hash FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];

            header("Location: " . $next);
            exit;
        }

        $errors[] = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UCLan Student Union Shop account page.">
    <title>Login | UCLan Student Union Shop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <header class="site-header" role="banner">
        <div class="header-inner">
            <a href="index.php" class="site-logo" aria-label="UCLan Student Union Shop Home">
                <img src="images/logo_reverse.png" alt="University of Lancashire logo" class="header-logo-img">
                <div class="logo-text"></div>
            </a>
            <button class="hamburger-btn" id="hamburger-btn" aria-controls="primary-nav" aria-expanded="false" aria-label="Toggle navigation menu">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </button>
            <nav class="primary-nav" id="primary-nav" aria-label="Primary navigation">
                <ul role="list">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li>
                        <a href="cart.php" class="nav-cart-link" aria-label="Shopping cart">
                            &#128722; Cart
                            <span class="cart-badge" id="cart-badge" aria-label="items in cart" style="display:none;">0</span>
                        </a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <li><a href="account.php">Hi, <?php echo h($_SESSION['user_name']); ?></a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="active" aria-current="page">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main id="main-content">

<section class="page-title-strip" aria-labelledby="login-heading">
    <h1 id="login-heading">Login</h1>
    <p>Login to access your account and checkout area.</p>
</section>

<div class="site-main auth-page">
    <form class="auth-form" method="post" action="login.php" novalidate>
        <h2>Student Union Shop Login</h2>

        <?php if ($errors): ?>
            <div class="form-errors" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo h($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php echo csrf_input(); ?>
        <input type="hidden" name="next" value="<?php echo h($next); ?>">

        <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?php echo h($email); ?>" required autocomplete="email">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">

        <button type="submit" class="btn-primary auth-submit">Login</button>

        <p class="auth-switch">No account yet? <a href="register.php">Create one here</a>.</p>
    </form>
</div>

    </main>
    <footer class="site-footer" role="contentinfo">
        <div class="footer-inner">
            <!-- <div class="footer-logo">
                <img src="images/logo_reverse.png" alt="University of Lancashire logo" class="footer-logo-img">
                <p>Discounted UCLan merchandise available exclusively to University of Lancashire students, staff, and alumni.</p>
            </div> -->
            <div class="footer-logo">
                <img src="images/logo_reverse.png" alt="University of Lancashire logo" class="footer-logo-img">
                <p>Discounted UCLan merchandise available exclusively to University of Lancashire students, staff, and alumni.</p>
                <div class="footer-logo-map">
                    <iframe
                        src="https://www.google.com/maps?q=University+of+Central+Lancashire,+Preston,+PR1+2HE&output=embed"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Map showing the UCLan Student Union location"></iframe>
                    <a href="https://www.google.com/maps/search/?api=1&query=University+of+Central+Lancashire+Preston" target="_blank" rel="noopener" aria-label="Open our location in Google Maps (opens in a new tab)">
                        📍 View on Google Maps &rarr;
                    </a>
                </div>
            </div>
            <nav class="footer-links" aria-label="Footer navigation">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">All Products</a></li>
                    <li><a href="cart.php">Your Cart</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
            <div class="footer-contact">
                <h3>Contact</h3>
                <p>Student Union<br>University of Lancashire<br>Preston, PR1 2HE<br>
                <a href="mailto:union@uclan.ac.uk" style="color:#94A3B8;">union@uclan.ac.uk</a></p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 UCLan Student Union Shop. University of Lancashire. All rights reserved</p>
        </div>
    </footer>
    <div class="toast" id="toast" aria-live="polite"></div>
    <script src="js/nav.js"></script>
</body>
</html>