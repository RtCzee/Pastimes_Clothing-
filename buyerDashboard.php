<?php
session_start();
require_once 'data/DBConn.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit;
}

$buyerID   = $_SESSION['userID'];
$buyerName = $_SESSION['username'] ?? 'Buyer';
$firstName  = explode(' ', $_SESSION['fullName'] ?? $buyerName)[0];

// FETCH all items from all sellers
$items  = [];
$stmt = $conn->prepare("SELECT c.*, u.username as sellerName FROM tblClothes c JOIN tblUser u ON c.sellerID = u.userID ORDER BY c.itemID DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

$totalItems = count($items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Buyer Dashboard — Pastimes</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
 
  <style>
    .dash-hero{background:linear-gradient(135deg,var(--foreground) 0%,rgba(0,0,0,.95) 100%);color:var(--white);padding:4rem 2rem 3.5rem;box-shadow:0 4px 16px rgba(0,0,0,.1)}
    .dash-hero__inner{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:2rem}
    .dash-hero__title{font-family:'Playfair Display',serif;font-size:clamp(2rem,4.5vw,3.2rem);font-weight:600;margin:0 0 .5rem;color:var(--white);letter-spacing:-.02em}
    .dash-hero__sub{color:rgba(255,255,255,.8);font-size:1rem;margin:0;font-weight:400;line-height:1.5}
    .dash-hero__actions{display:flex;gap:1rem;flex-wrap:wrap}
    .buyer-browse-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1.75rem}
    .product-browse-card{display:flex;flex-direction:column;background:var(--white);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:all .3s cubic-bezier(.25,.46,.45,.94);box-shadow:0 2px 8px rgba(0,0,0,.05)}
    .product-browse-card:hover{box-shadow:0 12px 32px rgba(0,0,0,.12);transform:translateY(-6px)}
    .product-browse-card__img{aspect-ratio:3/4;background:linear-gradient(135deg,#f5f3f0 0%,#e9e5dd 100%);overflow:hidden;position:relative}
    .product-browse-card__img img{width:100%;height:100%;object-fit:cover;transition:transform .5s cubic-bezier(.25,.46,.45,.94)}
    .product-browse-card:hover .product-browse-card__img img{transform:scale(1.08)}
    .product-browse-card__placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:rgba(0,0,0,.15)}
    .product-browse-card__body{padding:1.25rem;flex:1;display:flex;flex-direction:column;gap:.5rem}
    .product-browse-card__name{margin:0;font-size:1rem;font-weight:600;color:var(--foreground);line-height:1.3}
    .product-browse-card__seller{margin:0;font-size:.8rem;color:var(--muted)}
    .product-browse-card__desc{margin:0;font-size:.85rem;color:var(--muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .product-browse-card__price{margin-top:auto;padding-top:.75rem;font-size:1.1rem;font-weight:700;color:var(--accent)}
    .product-browse-card__action{display:flex;gap:.75rem;padding:1.25rem;border-top:1px solid var(--border);background:var(--secondary)}
    .product-browse-card__action .button{flex:1;font-size:.85rem;font-weight:500}
    .browse-empty{text-align:center;padding:5rem 2rem}
    .browse-empty__icon{display:inline-flex;align-items:center;justify-content:center;width:5rem;height:5rem;border-radius:999px;background:var(--accent);margin:0 auto 1.5rem;opacity:.9}
    .browse-empty h3{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:600;margin:0 0 .75rem;letter-spacing:-.01em}
    .browse-empty p{font-size:.95rem;color:var(--muted);margin:0 0 1.5rem;line-height:1.6}
    @media(max-width:768px){.dash-hero__inner{flex-direction:column;align-items:flex-start;gap:1.5rem}.dash-hero__actions{width:100%;flex-direction:column}.dash-hero__actions .button{width:100%}}
    @media(min-width:640px){.buyer-browse-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media(min-width:1024px){.buyer-browse-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
  </style>
</head>
<body>

<header class="site-header">
  <div class="container">
    <div class="site-header__inner">
      <a href="index.php" class="site-logo">Pastimes</a>
      <nav class="site-nav--desktop">
        <a href="index.php" class="site-nav__link">Home</a>
        <a href="Shop.php" class="site-nav__link">Shop</a>
        <a href="About.php" class="site-nav__link">About</a>
        <a href="Contact.php" class="site-nav__link">Contact</a>
        <a href="buyerDashboard.php" class="site-nav__link is-active">Browse</a>
      </nav>
      <div class="site-header__actions">
        <a href="logout.php" class="button button--outline button--small">Sign out</a>
        <button class="menu-toggle" aria-label="Toggle menu" aria-expanded="false">
          <svg class="icon menu-toggle__open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          <svg class="icon menu-toggle__close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>
  </div>
  <nav class="site-nav--mobile">
    <div class="container">
      <div class="site-nav__mobile-inner">
        <a href="index.php" class="site-nav__link">Home</a>
        <a href="Shop.php" class="site-nav__link">Shop</a>
        <a href="About.php" class="site-nav__link">About</a>
        <a href="buyerDashboard.php" class="site-nav__link is-active">Browse</a>
        <a href="logout.php" class="site-nav__link">Sign out</a>
      </div>
    </div>
  </nav>
</header>

<main class="site-main">

  <div class="dash-hero">
    <div class="container">
      <div class="dash-hero__inner">
        <div>
          <h1 class="dash-hero__title">Welcome, <?= htmlspecialchars($firstName) ?></h1>
          <p class="dash-hero__sub">Discover curated secondhand pieces from our sellers.</p>
        </div>
        <div class="dash-hero__actions">
          <a href="Shop.php" class="button button--light">
            <svg class="icon icon--small" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
            Shop All
          </a>
        </div>
      </div>
    </div>
  </div>

  <section class="section">
    <div class="container">

      <div style="margin-bottom: 2rem;">
        <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:600;margin:0 0 0.5rem;">Available Items</h2>
        <span style="font-size:.85rem;color:var(--muted);background:var(--accent);color:white;padding:.4rem .8rem;border-radius:999px;font-weight:600;display:inline-block;"><?= $totalItems ?> item<?= $totalItems !== 1 ? 's' : '' ?> available</span>
      </div>

      <?php if (empty($items)): ?>
        <div class="browse-empty">
          <div class="browse-empty__icon">
            <svg class="icon icon--large" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/></svg>
          </div>
          <h3>No items available</h3>
          <p>Check back soon for new listings from our sellers.</p>
          <a href="Shop.php" class="button">Shop Featured Items</a>
        </div>
      <?php else: ?>
        <div class="buyer-browse-grid">
          <?php foreach ($items as $item): ?>
            <div class="product-browse-card">
              <div class="product-browse-card__img">
                <?php if (!empty($item['image']) && file_exists(__DIR__ . '/' . $item['image'])): ?>
                  <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['itemName']) ?>">
                <?php else: ?>
                  <div class="product-browse-card__placeholder">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  </div>
                <?php endif; ?>
              </div>
              <div class="product-browse-card__body">
                <h3 class="product-browse-card__name"><?= htmlspecialchars($item['itemName']) ?></h3>
                <p class="product-browse-card__seller">By <?= htmlspecialchars($item['sellerName']) ?></p>
                <?php if (!empty($item['description'])): ?>
                  <p class="product-browse-card__desc"><?= htmlspecialchars($item['description']) ?></p>
                <?php endif; ?>
                <p class="product-browse-card__price">R<?= number_format((float)$item['price'], 2) ?></p>
              </div>
              <div class="product-browse-card__action">
                <a href="Cart.php" class="button button--small">Add to Cart</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <section class="banner">
    <div class="banner__media">
      <img src="assets/images/banner-home.jpg" alt="Fashion editorial">
      <div class="banner__overlay"></div>
    </div>
    <div class="container banner__content">
      <h2>Every Piece Has a Story</h2>
      <p>Explore our curated collection of pre-loved fashion.</p>
      <a class="button button--light" href="Shop.php">Explore More</a>
    </div>
  </section>

</main>

<footer class="site-footer">
  <div class="container">
    <div class="site-footer__grid">
      <div>
        <p class="site-footer__logo">Pastimes</p>
        <p class="site-footer__text" style="margin-top:.75rem;font-size:.88rem;">Sustainable thrift fashion for the conscious wardrobe.</p>
      </div>
      <div>
        <p class="site-footer__heading">Shop</p>
        <ul class="site-footer__list">
          <li><a href="Shop.php">All products</a></li>
          <li><a href="Shop.php?sort=new">New arrivals</a></li>
        </ul>
      </div>
      <div>
        <p class="site-footer__heading">Support</p>
        <ul class="site-footer__list">
          <li><a href="About.php">About Us</a></li>
          <li><a href="Contact.php">Contact</a></li>
        </ul>
      </div>
      <div>
        <p class="site-footer__heading">Account</p>
        <ul class="site-footer__list">
          <li><a href="buyerDashboard.php">My Dashboard</a></li>
          <li><a href="logout.php">Sign out</a></li>
        </ul>
      </div>
    </div>
    <div class="site-footer__bottom">
      <p>&copy; <?= date('Y') ?> Pastimes. All rights reserved.</p>
    </div>
  </div>
</footer>

<script>
  const toggle = document.querySelector('.menu-toggle');
  const mobileNav = document.querySelector('.site-nav--mobile');
  toggle.addEventListener('click', () => {
    const open = toggle.classList.toggle('is-open');
    mobileNav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open);
  });
</script>
</body>
</html>
