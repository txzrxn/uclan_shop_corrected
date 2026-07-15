<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
if (is_logged_in()) {
    header("Location: account.php");
    exit;
}

$errors = [];
$full_name = "";
$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "The registration form session expired. Refresh the page and try again.";
    }

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($full_name === '') {
        $errors[] = "Enter your full name.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

     if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (!$errors) {
        $stmt = mysqli_prepare($connection, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_fetch_assoc($result)) {
            $errors[] = "An account already exists with this email.";
        }
        mysqli_stmt_close($stmt);
    }

    if (!$errors) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($connection, "INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $full_name, $email, $password_hash);

        if (mysqli_stmt_execute($stmt)) {
            $new_user_id = mysqli_insert_id($connection);
            mysqli_stmt_close($stmt);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['user_name'] = $full_name;
            $_SESSION['user_email'] = $email;
            header("Location: account.php");
            exit;
        }

        $errors[] = "Account could not be created. Try again.";
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UCLan Student Union Shop account page.">
    <title>Register | UCLan Student Union Shop</title>
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
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php" class="active" aria-current="page">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main id="main-content">

<section class="page-title-strip" aria-labelledby="register-heading">
    <h1 id="register-heading">Create Account</h1>
    <p>Register to use the protected parts of the shop.</p>
</section>

<div class="site-main auth-page">
    <form class="auth-form" method="post" action="register.php" novalidate>
        <h2>Create a new account</h2>
        <?php echo csrf_input(); ?>

        <?php if ($errors): ?>
            <div class="form-errors" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo h($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo h($full_name); ?>" required autocomplete="name">

          <label for="email">Email address</label>
        <input type="email" id="email" name="email" value="<?php echo h($email); ?>" required autocomplete="email">
 
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8" aria-describedby="passwordHelp">
        <p class="field-hint" id="passwordHelp">At least 8 characters, with an uppercase letter, a lowercase letter, and a number.</p>
 
        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" minlength="8">

        <button type="submit" class="btn-primary auth-submit">Create Account</button>

        <p class="auth-switch">Already registered? <a href="login.php">Login here</a>.</p>
    </form>
</div>

    </main>
    <footer class="site-footer" role="contentinfo">
        <div class="footer-inner">
            <div class="footer-logo">
                <img src="images/logo_reverse.png" alt="University of Lancashire logo" class="footer-logo-img">
                <p>Discounted UCLan merchandise available exclusively to University of Lancashire students, staff, and alumni.</p>
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