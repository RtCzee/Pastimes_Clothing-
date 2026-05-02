<?php

// Load all products from the data file
$siteProducts = require __DIR__ . '/../data/products.php';

// Get all products
function site_products()
{
    global $siteProducts;

    return $siteProducts;
}

// Get available product categories
function site_categories()
{
    return array('All', 'Tops', 'Bottoms', 'Dresses', 'Outerwear', 'Accessories');
}

// Get available product sizes
function site_sizes()
{
    return array('All', 'XS', 'S', 'M', 'L', 'XL');
}

// Get available product conditions
function site_conditions()
{
    return array('All', 'Excellent', 'Good', 'Fair');
}

// Escape HTML special characters for safe output
function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Format price with R currency symbol
function format_price($price)
{
    return 'R' . number_format($price);
}

// Get current year for footer
function current_year()
{
    return date('Y');
}

// Find a product by ID
function find_product($id)
{
    foreach (site_products() as $product) {
        if ($product['id'] === $id) {
            return $product;
        }
    }

    return null;
}

// Filter products by category, size, and condition
function filter_products($category = 'All', $size = 'All', $condition = 'All')
{
    return array_values(array_filter(site_products(), static function ($product) use ($category, $size, $condition) {
        if ($category !== 'All' && $product['category'] !== $category) {
            return false;
        }

        if ($size !== 'All' && $product['size'] !== $size) {
            return false;
        }

        if ($condition !== 'All' && $product['condition'] !== $condition) {
            return false;
        }

        return true;
    }));
}

// Sort products by featured, price, or new arrival
function sort_products($products, $sortBy = 'featured')
{
    usort($products, static function ($left, $right) use ($sortBy) {
        $leftPrice = isset($left['price']) ? $left['price'] : 0;
        $rightPrice = isset($right['price']) ? $right['price'] : 0;
        $leftNew = !empty($left['new']) ? 1 : 0;
        $rightNew = !empty($right['new']) ? 1 : 0;
        $leftFeatured = !empty($left['featured']) ? 1 : 0;
        $rightFeatured = !empty($right['featured']) ? 1 : 0;

        switch ($sortBy) {
            case 'price-low':
                if ($leftPrice === $rightPrice) {
                    return 0;
                }
                return ($leftPrice < $rightPrice) ? -1 : 1;
            case 'price-high':
                if ($rightPrice === $leftPrice) {
                    return 0;
                }
                return ($rightPrice < $leftPrice) ? -1 : 1;
            case 'new':
                if ($rightNew === $leftNew) {
                    return 0;
                }
                return ($rightNew < $leftNew) ? -1 : 1;
            default:
                if ($rightFeatured === $leftFeatured) {
                    return 0;
                }
                return ($rightFeatured < $leftFeatured) ? -1 : 1;
        }
    });

    return $products;
}

// Return selected attribute for form controls
function selected_attr($expected, $value)
{
    return $expected === $value ? ' selected' : '';
}

// Return active class for nav links
function active_class($pageName, $currentPage)
{
    return $pageName === $currentPage ? ' is-active' : '';
}

// Render SVG icons by name
function site_icon($name, $class = 'icon')
{
    $icons = array(
        'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'leaf' => '<path d="M11 20A7 7 0 0 1 4 13C4 7 10 4 20 4c0 10-3 16-9 16Z"/><path d="M11 20c0-6 2-10 9-16"/>',
        'recycle' => '<path d="m7.9 20 2-3.5"/><path d="M2 12h4.5l1.7-3"/><path d="m7.1 4 2 3.5"/><path d="m14 4 1.8 3.2H21"/><path d="m16.9 20-2-3.5"/><path d="M14 20h-4"/><path d="m17 9 2.5 4.3-2.5 4.2"/><path d="m7 9-2.5 4.3L7 17.5"/>',
        'heart' => '<path d="m12 21-1.4-1.2C5.4 15 2 11.9 2 8a4 4 0 0 1 7-2.6A4 4 0 0 1 16 8c0 3.9-3.4 7-8.6 11.8Z"/>',
        'shopping-bag' => '<path d="M6 8h12l-1 12H7L6 8Z"/><path d="M9 8a3 3 0 1 1 6 0"/>',
        'menu' => '<path d="M4 7h16"/><path d="M4 12h16"/><path d="M4 17h16"/>',
        'x' => '<path d="m6 6 12 12"/><path d="m18 6-12 12"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
        'facebook' => '<path d="M14 8h2V4h-2a4 4 0 0 0-4 4v3H8v4h2v5h4v-5h2.2l.8-4H14V8a1 1 0 0 1 1-1Z"/>',
        'twitter' => '<path d="M22 5.8c-.7.3-1.5.5-2.3.7a4 4 0 0 0-6.9 2.7v.9A11.3 11.3 0 0 1 3 5.6s-4 9 5 13a12 12 0 0 1-7 2c9 5 20 0 20-11.5v-.5c.8-.5 1.5-1.2 2-2Z"/>',
        'sliders-horizontal' => '<path d="M21 4H14"/><path d="M10 4H3"/><path d="M21 12h-8"/><path d="M9 12H3"/><path d="M21 20h-4"/><path d="M13 20H3"/><circle cx="12" cy="4" r="2"/><circle cx="11" cy="12" r="2"/><circle cx="15" cy="20" r="2"/>',
        'check' => '<path d="m5 12 5 5L20 7"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'map-pin' => '<path d="M12 21s-6-5.3-6-11a6 6 0 0 1 12 0c0 5.7-6 11-6 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7l.4 2.6a2 2 0 0 1-.6 1.8l-1.3 1.3a16 16 0 0 0 6.9 6.9l1.3-1.3a2 2 0 0 1 1.8-.6l2.6.4A2 2 0 0 1 22 16.9Z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'send' => '<path d="M22 2 11 13"/><path d="m22 2-7 20-4-9-9-4Z"/>',
        'minus' => '<path d="M5 12h14"/>',
        'plus' => '<path d="M12 5v14"/><path d="M5 12h14"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'truck' => '<path d="M10 17h4"/><path d="M3 6h11v9H3Z"/><path d="M14 10h4l3 3v2h-7Z"/><circle cx="7.5" cy="18.5" r="1.5"/><circle cx="17.5" cy="18.5" r="1.5"/>',
        'refresh-cw' => '<path d="M21 12a9 9 0 1 1-2.6-6.4"/><path d="M21 3v6h-6"/>',
        'shield' => '<path d="M12 3 5 6v6c0 5 3.4 8.8 7 10 3.6-1.2 7-5 7-10V6l-7-3Z"/>',
        'sparkles' => '<path d="M12 3 13.7 8.3 19 10l-5.3 1.7L12 17l-1.7-5.3L5 10l5.3-1.7L12 3Z"/><path d="M19 14 20 17l3 1-3 1-1 3-1-3-3-1 3-1 1-3Z"/><path d="M5 14 5.8 16.2 8 17l-2.2.8L5 20l-.8-2.2L2 17l2.2-.8L5 14Z"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3.5"/><path d="M22 21v-2a4 4 0 0 0-3-3.9"/><path d="M16 3.1a3.5 3.5 0 0 1 0 6.8"/>',
    );

    $paths = isset($icons[$name]) ? $icons[$name] : '';

    return '<svg class="' . h($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}

function render_product_card($product)
{
    ?>
    <article
        class="product-card"
        data-product-id="<?= h($product['id']) ?>"
        data-category="<?= h($product['category']) ?>"
        data-size="<?= h($product['size']) ?>"
        data-condition="<?= h($product['condition']) ?>"
        data-price="<?= h((string) $product['price']) ?>"
        data-featured="<?= !empty($product['featured']) ? '1' : '0' ?>"
        data-new="<?= !empty($product['new']) ? '1' : '0' ?>"
    >
        <a class="product-card__image-link" href="Product.php?id=<?= h($product['id']) ?>">
            <div class="product-card__image-wrap">
                <img class="product-card__image" src="<?= h($product['image']) ?>" alt="<?= h($product['name']) ?>">
                <?php if (!empty($product['new'])): ?>
                    <span class="product-card__badge product-card__badge--dark">New</span>
                <?php endif; ?>
                <?php if (!empty($product['originalPrice'])): ?>
                    <span class="product-card__badge product-card__badge--light product-card__badge--right">Sale</span>
                <?php endif; ?>
            </div>
        </a>
        <div class="product-card__body">
            <div class="product-card__top">
                <div>
                    <a class="product-card__title-link" href="Product.php?id=<?= h($product['id']) ?>">
                        <h3 class="product-card__title"><?= h($product['name']) ?></h3>
                    </a>
                    <p class="product-card__meta"><?= h($product['category']) ?> &middot; Size <?= h($product['size']) ?></p>
                </div>
            </div>
            <div class="product-card__bottom">
                <div class="product-card__price-wrap">
                    <span class="product-card__price"><?= h(format_price((int) $product['price'])) ?></span>
                    <?php if (!empty($product['originalPrice'])): ?>
                        <span class="product-card__price-old"><?= h(format_price((int) $product['originalPrice'])) ?></span>
                    <?php endif; ?>
                </div>
                <button
                    class="button button--outline button--small"
                    type="button"
                    data-add-to-cart="<?= h($product['id']) ?>"
                >
                    Add to Cart
                </button>
            </div>
        </div>
    </article>
    <?php
}

function render_page_start($options = array())
{
    $pageTitle = isset($options['title']) ? $options['title'] : 'Pastimes | Sustainable Thrift Fashion';
    $description = isset($options['description']) ? $options['description'] : 'Discover curated secondhand clothing in South Africa. Sustainable fashion with a story. Give clothes a second chance.';
    $pageName = isset($options['page']) ? $options['page'] : 'Home';
    $bodyClass = isset($options['bodyClass']) ? $options['bodyClass'] : '';
    $productsJson = json_encode(site_products(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="<?= h($description) ?>">
        <title><?= h($pageTitle) ?></title>
        <link rel="icon" type="image/svg+xml" href="assets/icons/icon.svg">
        <link rel="apple-touch-icon" href="assets/icons/apple-icon.png">
        <link rel="stylesheet" href="assets/css/styles.css">
        <script>
            window.PASTIMES_PRODUCTS = <?= $productsJson ?: '[]' ?>;
        </script>
    </head>
    <body class="<?= h($bodyClass) ?>" data-page="<?= h($pageName) ?>">
        <header class="site-header">
            <div class="container site-header__inner">
                <a class="site-logo" href="Home.php">Pastimes</a>
                <nav class="site-nav site-nav--desktop" aria-label="Primary">
                    <a class="site-nav__link<?= active_class('Home', $pageName) ?>" href="Home.php">Home</a>
                    <a class="site-nav__link<?= active_class('Shop', $pageName) ?>" href="Shop.php">Shop</a>
                    <a class="site-nav__link<?= active_class('About', $pageName) ?>" href="About.php">About</a>
                    <a class="site-nav__link<?= active_class('Contact', $pageName) ?>" href="Contact.php">Contact</a>
                </nav>
                <div class="site-header__actions">
                    <a class="cart-link" href="Cart.php" aria-label="Open cart">
                        <?= site_icon('shopping-bag', 'icon icon--small') ?>
                        <span class="cart-count" data-cart-count>0</span>
                    </a>
                    <button class="menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-nav">
                        <span class="menu-toggle__open"><?= site_icon('menu', 'icon icon--small') ?></span>
                        <span class="menu-toggle__close"><?= site_icon('x', 'icon icon--small') ?></span>
                        <span class="sr-only">Menu</span>
                    </button>
                </div>
            </div>
            <nav class="site-nav site-nav--mobile" id="mobile-nav" aria-label="Mobile" data-mobile-nav>
                <div class="container site-nav__mobile-inner">
                    <a class="site-nav__link<?= active_class('Home', $pageName) ?>" href="Home.php">Home</a>
                    <a class="site-nav__link<?= active_class('Shop', $pageName) ?>" href="Shop.php">Shop</a>
                    <a class="site-nav__link<?= active_class('About', $pageName) ?>" href="About.php">About</a>
                    <a class="site-nav__link<?= active_class('Contact', $pageName) ?>" href="Contact.php">Contact</a>
                </div>
            </nav>
        </header>
        <main class="site-main">
    <?php
}

function render_page_end()
{
    ?>
        </main>
        <footer class="site-footer">
            <div class="container site-footer__grid">
                <div>
                    <a class="site-footer__logo" href="Home.php">Pastimes</a>
                    <p class="site-footer__text">
                        Curated secondhand fashion for the conscious consumer. Every piece tells a story.
                    </p>
                </div>
                <div>
                    <h4 class="site-footer__heading">Shop</h4>
                    <ul class="site-footer__list">
                        <li><a href="Shop.php?category=Tops">Tops</a></li>
                        <li><a href="Shop.php?category=Bottoms">Bottoms</a></li>
                        <li><a href="Shop.php?category=Dresses">Dresses</a></li>
                        <li><a href="Shop.php?category=Outerwear">Outerwear</a></li>
                        <li><a href="Shop.php?category=Accessories">Accessories</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="site-footer__heading">Info</h4>
                    <ul class="site-footer__list">
                        <li><a href="About.php">About Us</a></li>
                        <li><a href="Contact.php">Contact</a></li>
                        <li><a href="About.php#shipping">Shipping &amp; Returns</a></li>
                        <li><a href="About.php#faq">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="site-footer__heading">Stay Connected</h4>
                    <p class="site-footer__text">
                        Subscribe for new arrivals and exclusive offers.
                    </p>
                    <form class="inline-form" data-simple-form>
                        <input class="field-input field-input--dark" type="email" placeholder="Your email" aria-label="Your email">
                        <button class="button button--light" type="submit">Join</button>
                    </form>
                    <div class="site-footer__socials">
                        <a href="#" aria-label="Instagram"><?= site_icon('instagram', 'icon icon--small') ?></a>
                        <a href="#" aria-label="Facebook"><?= site_icon('facebook', 'icon icon--small') ?></a>
                        <a href="#" aria-label="Twitter"><?= site_icon('twitter', 'icon icon--small') ?></a>
                    </div>
                </div>
            </div>
            <div class="container site-footer__bottom">
                <p>&copy; <?= h(current_year()) ?> Pastimes. All rights reserved.</p>
            </div>
        </footer>
        <script src="assets/js/app.js"></script>
    </body>
    </html>
    <?php
}
