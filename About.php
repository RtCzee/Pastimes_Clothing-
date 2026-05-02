<?php

// About page - company story and values
require __DIR__ . '/includes/bootstrap.php';

render_page_start([
    'title' => 'About | Pastimes',
    'page' => 'About',
]);
?>
<section class="hero hero--about">
    <div class="hero__media">
        <img src="assets/images/hero-about.jpg" alt="Sustainable fashion">
        <div class="hero__overlay hero__overlay--deeper"></div>
    </div>
    <div class="container hero__content hero__content--center hero__content--narrow">
        <h1 class="hero__title">Our Story</h1>
        <p class="hero__text">
            We believe beautiful fashion should not cost the earth. Every piece in our collection is a chance to dress sustainably without compromising on style.
        </p>
    </div>
</section>

<section class="section">
    <div class="container split-section">
        <div class="split-section__content">
            <h2>Fashion Forward, Earth Friendly</h2>
            <div class="copy-stack">
                <p>Pastimes was born from a simple belief: that the most sustainable choice is often the one that already exists. Every year, millions of tons of clothing end up in landfills while perfectly wearable pieces wait to find new homes.</p>
                <p>We started this journey in 2020, hand-selecting quality secondhand pieces that deserve a second chapter. Our team carefully curates each item, ensuring it meets our standards for condition, style, and timelessness.</p>
                <p>When you shop with us, you are not just buying clothes - you are joining a movement toward mindful consumption and circular fashion.</p>
            </div>
            <a class="button" href="Shop.php">Shop Our Collection <?= site_icon('arrow-right', 'icon icon--small') ?></a>
        </div>
        <div class="split-section__media">
            <img src="assets/images/about-rack.jpg" alt="Curated clothing rack">
        </div>
    </div>
</section>

<section class="section section--accent">
    <div class="container">
        <div class="section-heading center-text">
            <h2>What We Stand For</h2>
            <p>Our values guide every decision we make, from the pieces we select to how we serve our community.</p>
        </div>
        <div class="four-up">
            <article class="value-card">
                <div class="value-card__icon value-card__icon--light"><?= site_icon('leaf', 'icon') ?></div>
                <h3>Sustainability</h3>
                <p>Every purchase prevents clothing from entering landfills and reduces the demand for new production.</p>
            </article>
            <article class="value-card">
                <div class="value-card__icon value-card__icon--light"><?= site_icon('sparkles', 'icon') ?></div>
                <h3>Quality</h3>
                <p>We only accept items in excellent or good condition, ensuring you receive pieces that will last.</p>
            </article>
            <article class="value-card">
                <div class="value-card__icon value-card__icon--light"><?= site_icon('heart', 'icon') ?></div>
                <h3>Authenticity</h3>
                <p>Each item comes with its own history. We believe in celebrating the stories clothes carry.</p>
            </article>
            <article class="value-card">
                <div class="value-card__icon value-card__icon--light"><?= site_icon('users', 'icon') ?></div>
                <h3>Community</h3>
                <p>We are building a community of conscious consumers who care about where their clothes come from.</p>
            </article>
        </div>
    </div>
</section>

<section class="section" id="shipping">
    <div class="container container--narrow">
        <div class="section-heading center-text">
            <h2>Shipping &amp; Returns</h2>
        </div>
        <div class="detail-list">
            <article>
                <h3>Shipping</h3>
                <p>We offer free standard shipping on orders over R1,500. Standard shipping within South Africa typically takes 3-5 business days. Express shipping is available for an additional fee and delivers within 1-2 business days.</p>
            </article>
            <article>
                <h3>Returns</h3>
                <p>We accept returns within 14 days of delivery. Items must be unworn, unwashed, and in their original condition with tags attached. Please note that final sale items cannot be returned.</p>
            </article>
            <article>
                <h3>Exchanges</h3>
                <p>Since each item in our collection is unique, we are unable to offer direct exchanges. If you would like a different item, please return your purchase for a refund and place a new order.</p>
            </article>
        </div>
    </div>
</section>

<section class="section section--secondary" id="faq">
    <div class="container container--narrow">
        <div class="section-heading center-text">
            <h2>Frequently Asked Questions</h2>
        </div>
        <div class="faq-grid">
            <article class="faq-card">
                <h3>How do you source your items?</h3>
                <p>We source items from various channels including estate sales, consignment partnerships, and direct submissions from individuals. Every piece goes through a thorough quality inspection before being listed.</p>
            </article>
            <article class="faq-card">
                <h3>How do you determine condition ratings?</h3>
                <p>Excellent condition means the item shows no visible wear. Good condition items may have minor signs of wear that do not affect the overall appearance. Fair condition items have noticeable wear but remain wearable and stylish.</p>
            </article>
            <article class="faq-card">
                <h3>Can I sell my clothes to Pastimes?</h3>
                <p>Yes! We are always looking for quality pieces to add to our collection. Please reach out through our contact page with photos and details of the items you would like to sell.</p>
            </article>
            <article class="faq-card">
                <h3>Are the items authentic?</h3>
                <p>Absolutely. Every item is carefully inspected for authenticity. If we cannot verify an item is genuine, we do not list it. We stand behind every piece we sell.</p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container container--narrow center-text">
        <h2>Ready to Shop Sustainably?</h2>
        <p>Discover your next favorite piece in our curated collection.</p>
        <a class="button" href="Shop.php">Browse Collection <?= site_icon('arrow-right', 'icon icon--small') ?></a>
    </div>
</section>
<?php render_page_end(); ?>
