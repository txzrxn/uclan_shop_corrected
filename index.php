<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$offers_sql = "SELECT offer_id, offer_title, offer_dec FROM tbl_offers ORDER BY offer_id DESC";
$offers_result = mysqli_query($connection, $offers_sql);

if (!$offers_result) {
    error_log("Offer query failed: " . mysqli_error($connection));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="UCLan Legacy Shop - grab discounted University of Lancashire merchandise.">
    <title>UCLan Legacy Shop | Home</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header-inner">
            <a href="index.php" class="site-logo" aria-label="UCLan Student Union Shop Home">
                <img src="images/logo_reverse.png" alt="University of Lancashire logo" class="header-logo-img">
                <div class="logo-text"></div>
                <span class="site-title">Legacy Shop</span>
            </a>
                
                <!-- <span class="site-title">Legacy Shop</span> -->
            
            <button class="hamburger" id="hamburgerBtn" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="primaryNav">&#9776;</button>
            <nav aria-label="Primary navigation" id="primaryNav">
                <ul>
                    <li><a href="index.php" class="active" aria-current="page">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="cart.php" class="cart-nav-link" aria-label="Shopping cart">&#128722; Cart <span class="cart-count-badge" id="cartBadge" aria-live="polite">0</span></a></li>
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
    <main>
        <section class="hero" aria-labelledby="heroHeading">
            <?php if (is_logged_in()): ?>
                <h1 id="heroHeading">Welcome back, <span><?php echo h($_SESSION['user_name']); ?></span>!</h1>
                <p>Good to see you again at the UCLan Legacy Shop. Grab our iconic UCLan merchandise at discounted rates before it is gone forever!</p>
            <?php else: ?>
                <h1 id="heroHeading">Welcome to<span> UCLan </span> Legacy Shop!</h1>
                <p>The University of Lancashire has rebranded. Grab our iconic UCLan merchandise at discounted rates before it is gone forever!</p>
            <?php endif; ?>
            <a href="products.php" class="btn btn-primary">Browse All Products</a>
        </section>

    
    <section class="offers-section" aria-labelledby="offers-heading">
    <h2 id="offers-heading">Current Offers</h2>

    <div class="offers-grid">
        <?php if ($offers_result && mysqli_num_rows($offers_result) > 0): ?>
            <?php while ($offer = mysqli_fetch_assoc($offers_result)): ?>
                <article class="offer-card">
                    <h3><?php echo htmlspecialchars($offer['offer_title']); ?></h3>
                    <p><?php echo htmlspecialchars($offer['offer_dec']); ?></p>
                </article>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No current offers are available.</p>
        <?php endif; ?>
    </div>
     </section>


    <br>

        <!-- Video Section
             Two videos are included from the supplied course resources:
             1. An iFrame embedding the YouTube video from iframe_link.url (YouTube ID: vzbO3x3OUJQ).
                The iFrame element is required by the assignment brief for external media content.
             2. An HTML5 <video> element using the local video.mp4 file supplied on Blackboard. -->
        <section class="video-section" aria-labelledby="videoHeading">
            <h2 id="videoHeading">UCLan in Action</h2>
            <div class="videos-grid">
                <!-- Video 1: External YouTube embed via iFrame (source: iframe_link.url resource) -->
                <div>
                    <div class="video-wrapper">
                        <iframe src="https://www.youtube.com/embed/vzbO3x3OUJQ" title="UCLan promotional video YouTube" allowfullscreen aria-label="UCLan YouTube promotional video"></iframe>
                    </div>
                    <p class="video-label">UCLan Promotional Video (YouTube)</p>
                </div>
                <!-- Video 2: Local MP4 file supplied in course materials (video.mp4) -->
                <div>
                    <div class="video-wrapper">
                        <video src="images/promo.mp4" controls aria-label="UCLan campus video supplied resource">Your browser does not support the video element.</video>
                    </div>
                    <p class="video-label">UCLan Campus Video (Supplied Resource)</p>
                </div>
            </div>
        </section>
<br>
        <!-- UCLan University Summary Section (replaces Why Shop With Us) -->
        <section class="uni-summary" aria-labelledby="uniSummaryHeading">
            <h2 id="uniSummaryHeading" style="text-align:center;">About the University of Lancashire</h2><br>
            <p>The University of Lancashire (UoL), formerly known as the <strong>University of Central Lancashire (UCLan)</strong>, is a public university located in <strong>Preston, Lancashire, England</strong>. It was established in 1828 as the Institution for the Diffusion of Knowledge, making it one of the oldest higher education institutions in the United Kingdom.</p>
            <p>With a strong commitment to widening participation, applied research, and industry partnerships, the university serves a global community of students across a broad range of disciplines — from engineering and health sciences to the arts, business, and computing.</p>
            <p>Following a major rebrand in 2025, the university adopted its new name and visual identity. This Legacy Shop exists to celebrate the UCLan era — giving students, staff, and alumni the chance to own a piece of university history at discounted prices.</p>
        </section><br>

        <section style="text-align:center; padding:2rem 0;" aria-labelledby="ctaHeading">
            <h2 id="ctaHeading" style="font-size:1.5rem; margin-bottom:0.75rem; color:var(--uclan-dark);">Ready to grab a piece of history?</h2>
            <a href="products.php" class="btn btn-primary">Shop Now</a>
        </section>

    
    </main>
    
    <footer class="site-footer" role="contentinfo">
        <div class="footer-inner">
            
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

    <div class="toast" id="toast" role="status" aria-live="polite"></div>
    <script>
        var hamburgerBtn = document.getElementById('hamburgerBtn');
        var primaryNav = document.getElementById('primaryNav');
        hamburgerBtn.addEventListener('click', function() {
            var isOpen = primaryNav.classList.toggle('open');
            hamburgerBtn.setAttribute('aria-expanded', isOpen.toString());
        });
        function updateCartBadge() {
            var cart = JSON.parse(localStorage.getItem('uclanCart')) || [];
            var total = 0;
            for (var i = 0; i < cart.length; i++) { total += cart[i].qty; }
            document.getElementById('cartBadge').textContent = total;
        }
        updateCartBadge();
    </script>
</body>
</html>
