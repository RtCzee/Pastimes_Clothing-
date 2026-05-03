<?php

// Home page - displays featured products and new arrivals
require __DIR__ . '/includes/bootstrap.php';

$featuredProducts = array_slice(array_values(array_filter(site_products(), static function ($product) {
    return !empty($product['featured']);
})), 0, 4);
$newArrivals = array_slice(array_values(array_filter(site_products(), static function ($product) {
    return !empty($product['new']);
})), 0, 4);

render_page_start([
    'title' => 'Pastimes | Sustainable Thrift Fashion',
    'page' => 'Home',
]);
?>
<section class="hero hero--home">
    <div class="hero__media">
        <img src="assets/images/hero-home.jpg" alt="Sustainable fashion">
        <div class="hero__overlay"></div>
    </div>
    <div class="container hero__content hero__content--center">
        <h1 class="hero__title">Pre-Loved Fashion,<br><span>Re-Loved Style</span></h1>
        <p class="hero__text">
            Curated secondhand pieces for the conscious wardrobe. Every item tells a story worth continuing.
        </p>
        <div class="button-row button-row--center">
            <a class="button" href="Shop.php">Shop Collection <?= site_icon('arrow-right', 'icon icon--small') ?></a>
            <a class="button button--ghost-light" href="About.php">Our Story <?= site_icon('arrow-right', 'icon icon--small') ?></a>
        </div>
    </div>
</section>

<section class="section section--bordered">
    <div class="container">
        <div class="three-up">
            <article class="value-card">
                <div class="value-card__icon"><?= site_icon('leaf', 'icon') ?></div>
                <h3>Sustainable Fashion</h3>
                <p>By choosing secondhand, you help reduce fashion waste and extend the lifecycle of quality garments.</p>
            </article>
            <article class="value-card">
                <div class="value-card__icon"><?= site_icon('heart', 'icon') ?></div>
                <h3>Carefully Curated</h3>
                <p>Each piece is hand-selected for quality, style, and timeless appeal. We only offer items we love.</p>
            </article>
            <article class="value-card">
                <div class="value-card__icon"><?= site_icon('recycle', 'icon') ?></div>
                <h3>Circular Economy</h3>
                <p>Join the movement towards mindful consumption. Great style does not have to come at a cost to our planet.</p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading section-heading--split">
            <div>
                <h2>Featured Pieces</h2>
                <p>Our editors&apos; picks for the season</p>
            </div>
            <a class="section-heading__link" href="Shop.php">View All <?= site_icon('arrow-right', 'icon icon--tiny') ?></a>
        </div>
        <div class="product-grid product-grid--four">
            <?php foreach ($featuredProducts as $product): ?>
                <?php render_product_card($product); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="banner">
    <div class="banner__media">
        <img src="assets/images/banner-home.jpg" alt="Fashion editorial">
        <div class="banner__overlay"></div>
    </div>
    <div class="container banner__content">
        <h2>Style is Forever</h2>
        <p>Discover timeless pieces that transcend trends and seasons.</p>
        <a class="button button--light" href="Shop.php">Explore Now</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading section-heading--split">
            <div>
                <h2>New Arrivals</h2>
                <p>Fresh finds, just added to our collection</p>
            </div>
            <a class="section-heading__link" href="Shop.php?sort=new">View All New <?= site_icon('arrow-right', 'icon icon--tiny') ?></a>
        </div>
        <div class="product-grid product-grid--four">
            <?php foreach ($newArrivals as $product): ?>
                <?php render_product_card($product); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--accent">
    <div class="container container--narrow center-text">
        <h2>Join Our Community</h2>
        <p>Be the first to know about new arrivals, exclusive offers, and sustainable fashion tips.</p>
        <form class="newsletter-form" data-simple-form>
            <input class="field-input" type="email" placeholder="Enter your email" aria-label="Enter your email">
            <button class="button" type="submit">Subscribe</button>
        </form>
    </div>
</section>

<a href="adminLogin.php" style="position:fixed;bottom:10px;right:10px;font-size:12px;color:gray;">
Admin
</a>
<?php render_page_end(); ?>
