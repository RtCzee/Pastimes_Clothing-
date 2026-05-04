<?php
session_start();
require_once 'data/DBConn.php';

if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit;
}

$sellerID   = $_SESSION['userID'];
$sellerName = $_SESSION['username'] ?? 'Seller';
$firstName  = explode(' ', $_SESSION['fullName'] ?? $sellerName)[0];

$message = '';
$error   = '';

// Ensure the live schema has the sellerID column used by this dashboard.
$columnCheck = $conn->query("SHOW COLUMNS FROM tblClothes LIKE 'sellerID'");
if (!$columnCheck) {
  die('Unable to verify listing schema: ' . $conn->error);
}

if ($columnCheck->num_rows === 0) {
  if (!$conn->query("ALTER TABLE tblClothes ADD COLUMN sellerID INT NULL")) {
    die('Unable to update listing schema: ' . $conn->error);
  }
}

// DELETE
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delID = (int)$_GET['delete'];
    $stmt  = $conn->prepare("DELETE FROM tblClothes WHERE itemID = ? AND sellerID = ?");
    if (!$stmt) {
        die('Unable to prepare delete query: ' . $conn->error);
    }
    $stmt->bind_param("ii", $delID, $sellerID);
    $stmt->execute();
    $stmt->close();
    $message = 'Listing removed successfully.';
}

// LOAD item for editing
$editItem = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editID = (int)$_GET['edit'];
    $stmt   = $conn->prepare("SELECT * FROM tblClothes WHERE itemID = ? AND sellerID = ?");
    if (!$stmt) {
        die('Unable to prepare edit query: ' . $conn->error);
    }
    $stmt->bind_param("ii", $editID, $sellerID);
    $stmt->execute();
    $editItem = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// ADD / EDIT submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemID      = isset($_POST['itemID']) && is_numeric($_POST['itemID']) ? (int)$_POST['itemID'] : null;
    $itemName    = trim($_POST['itemName'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $imagePath   = '';

    if (empty($itemName) || empty($price)) {
        $error = 'Item name and price are required.';
    } elseif (!is_numeric($price) || (float)$price <= 0) {
        $error = 'Please enter a valid price.';
    } else {
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/assets/images/clothes/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG or WEBP images are allowed.';
            } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                $error = 'Image must be under 3 MB.';
            } else {
                $filename  = 'item_' . time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
                $imagePath = 'assets/images/clothes/' . $filename;
            }
        }

        if (empty($error)) {
            if ($itemID) {
                if (!empty($imagePath)) {
                    $stmt = $conn->prepare("UPDATE tblClothes SET itemName=?, description=?, price=?, image=? WHERE itemID=? AND sellerID=?");
                    if (!$stmt) {
                        $error = 'Unable to prepare update query: ' . $conn->error;
                    } else {
                        $stmt->bind_param("ssdsii", $itemName, $description, $price, $imagePath, $itemID, $sellerID);
                    }
                } else {
                    $stmt = $conn->prepare("UPDATE tblClothes SET itemName=?, description=?, price=? WHERE itemID=? AND sellerID=?");
                    if (!$stmt) {
                        $error = 'Unable to prepare update query: ' . $conn->error;
                    } else {
                        $stmt->bind_param("ssdii", $itemName, $description, $price, $itemID, $sellerID);
                    }
                }
                if (empty($error)) {
                    $stmt->execute();
                    $stmt->close();
                    $message  = 'Listing updated successfully.';
                    $editItem = null;
                }
            } else {
                $stmt = $conn->prepare("INSERT INTO tblClothes (sellerID, itemName, description, price, image) VALUES (?, ?, ?, ?, ?)");
                if (!$stmt) {
                    $error = 'Unable to prepare insert query: ' . $conn->error;
                } else {
                    $stmt->bind_param("issds", $sellerID, $itemName, $description, $price, $imagePath);
                    $stmt->execute();
                    $stmt->close();
                    $message = 'Item added successfully.';
                }
            }
        }
    }
}

// FETCH items for THIS SELLER ONLY
$items  = [];
$stmt = $conn->prepare("SELECT * FROM tblClothes WHERE sellerID = ? ORDER BY itemID DESC");
if (!$stmt) {
  die('Unable to prepare listing query: ' . $conn->error);
}
$stmt->bind_param("i", $sellerID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();

$totalItems = count($items);
$totalValue = array_sum(array_column($items, 'price'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seller Dashboard — Pastimes</title>
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
 
  <style>
    .dash-hero{background:linear-gradient(135deg,var(--foreground) 0%,rgba(0,0,0,.95) 100%);color:var(--white);padding:4rem 2rem 3.5rem;box-shadow:0 4px 16px rgba(0,0,0,.1)}
    .dash-hero__inner{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:1.5rem}
    .dash-hero__title{font-family:'Playfair Display',serif;font-size:clamp(2rem,4.5vw,3.2rem);font-weight:600;margin:0 0 .5rem;color:var(--white);letter-spacing:-.02em}
    .dash-hero__sub{color:rgba(255,255,255,.8);font-size:1rem;margin:0;font-weight:400;line-height:1.5}
    .dash-hero__actions{display:flex;gap:1rem;flex-wrap:wrap}
    .stats-strip{background:linear-gradient(to right,var(--secondary),rgba(var(--secondary-rgb),.7));border-bottom:2px solid var(--accent);padding:2.5rem 0;box-shadow:inset 0 1px 2px rgba(0,0,0,.02)}
    .stats-grid{display:grid;grid-template-columns:repeat(3,1fr)}
    .stat-item{text-align:center;padding:0 1rem;border-right:1px solid var(--border)}
    .stat-item:last-child{border-right:none}
    .stat-item__value{font-family:'Playfair Display',serif;font-size:2.5rem;font-weight:600;line-height:1;margin-bottom:.5rem;color:var(--foreground)}
    .stat-item__label{font-size:.75rem;color:var(--muted);letter-spacing:.08em;text-transform:uppercase;font-weight:600}
    .dash-msg{padding:.85rem 1.25rem;border-radius:var(--radius);font-size:.88rem;margin-bottom:1.5rem}
    .dash-msg--ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
    .dash-msg--err{background:#fdf0f0;border:1px solid #f5c6c6;color:#a33}
    .form-card{background:var(--white);border:1px solid var(--border);border-radius:var(--radius);padding:2.5rem;margin-bottom:3.5rem;box-shadow:0 2px 12px rgba(0,0,0,.05);transition:box-shadow .3s ease;border-top:4px solid var(--accent)}
    .form-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08)}
    .form-card__title{font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:600;margin:0 0 2rem;color:var(--foreground);letter-spacing:-.01em}
    .form-card__grid{display:grid;grid-template-columns:1fr;gap:1.25rem}
    .form-card label{display:block;font-size:.75rem;color:var(--muted);margin-bottom:.5rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
    .form-card input[type=text],.form-card input[type=number],.form-card input[type=file],.form-card textarea{width:100%;padding:.9rem 1rem;border:1.5px solid var(--border);border-radius:var(--radius);background:var(--white);color:var(--foreground);font-family:'Inter',sans-serif;font-size:.95rem;transition:all .2s}
    .form-card input[type=file]{padding:.5rem .9rem;background:var(--secondary)}
    .form-card input:focus,.form-card textarea:focus{outline:none;border-color:var(--foreground);box-shadow:0 0 0 3px rgba(0,0,0,.05)}
    .form-card textarea{min-height:5.5rem;resize:vertical;font-family:'Inter',sans-serif}
    .form-card__footer{display:flex;gap:1rem;flex-wrap:wrap;margin-top:2rem;padding-top:2rem;border-top:1px solid var(--border)}
    .req{color:#ef4444;font-weight:600}
    #imgPreview{display:none;margin-top:.75rem;width:7rem;aspect-ratio:3/4;object-fit:cover;border:2px solid var(--border);border-radius:var(--radius);box-shadow:0 2px 8px rgba(0,0,0,.1)}
    .listings-header{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:2px solid var(--border)}
    .listings-header h2{font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:600;margin:0;letter-spacing:-.01em}
    .listings-count{font-size:.85rem;color:var(--muted);background:var(--accent);padding:.4rem .8rem;border-radius:999px;font-weight:600}
    .listings-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1.75rem}
    .listing-card{display:flex;flex-direction:column;background:var(--white);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;transition:all .3s cubic-bezier(.25,.46,.45,.94);box-shadow:0 2px 8px rgba(0,0,0,.05)}
    .listing-card:hover{box-shadow:0 12px 32px rgba(0,0,0,.12);transform:translateY(-6px)}
    .listing-card__img{aspect-ratio:3/4;background:linear-gradient(135deg,#f5f3f0 0%,#e9e5dd 100%);overflow:hidden;position:relative}
    .listing-card__img img{width:100%;height:100%;object-fit:cover;transition:transform .5s cubic-bezier(.25,.46,.45,.94)}
    .listing-card:hover .listing-card__img img{transform:scale(1.08)}
    .listing-card__placeholder{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:rgba(0,0,0,.15)}
    .listing-card__body{padding:1.25rem;flex:1;display:flex;flex-direction:column;gap:.5rem}
    .listing-card__name{margin:0;font-size:1rem;font-weight:600;color:var(--foreground);line-height:1.3}
    .listing-card__desc{margin:0;font-size:.85rem;color:var(--muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    .listing-card__price{margin-top:auto;padding-top:.75rem;font-size:1.1rem;font-weight:700;color:var(--accent)}
    .listing-card__actions{display:flex;gap:.75rem;padding:1.25rem;border-top:1px solid var(--border);background:var(--secondary);flex-wrap:wrap}\n    .listing-card__actions .button{flex:1;min-width:80px;font-size:.85rem}
    .listings-empty{text-align:center;padding:5rem 2rem}
    .listings-empty__icon{display:inline-flex;align-items:center;justify-content:center;width:5rem;height:5rem;border-radius:999px;background:var(--accent);margin:0 auto 1.5rem;opacity:.9}
    .listings-empty h3{font-family:'Playfair Display',serif;font-size:1.5rem;font-weight:600;margin:0 0 .75rem;letter-spacing:-.01em}
    .listings-empty p{font-size:.95rem;color:var(--muted);margin:0 0 1.5rem;line-height:1.6}
    @media(max-width:768px){.dash-hero__inner{flex-direction:column;align-items:flex-start;gap:1.5rem}.dash-hero__actions{width:100%;flex-direction:column}.dash-hero__actions .button{width:100%}.stats-grid{grid-template-columns:1fr}.stat-item{border-right:none;padding:1.5rem 0;border-bottom:1px solid rgba(0,0,0,.08)}.stat-item:last-child{border-bottom:none}}
    @media(min-width:640px){.form-card__grid{grid-template-columns:repeat(2,1fr)}.form-card__grid .span-2{grid-column:span 2}.listings-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @media(min-width:1024px){.listings-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
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
        <a href="sellerDashboard.php" class="site-nav__link is-active">My Listings</a>
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
        <a href="sellerDashboard.php" class="site-nav__link is-active">My Listings</a>
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
          <h1 class="dash-hero__title">Welcome back, <?= htmlspecialchars($firstName) ?></h1>
          <p class="dash-hero__sub">Add items to the store and manage your listings below.</p>
        </div>
        <div class="dash-hero__actions">
          <a href="#add-item" class="button button--light">
            <svg class="icon icon--small" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Item
          </a>
          <a href="Shop.php" class="button button--ghost-light">View Shop</a>
        </div>
      </div>
    </div>
  </div>

  <div class="stats-strip">
    <div class="container">
      <div class="stats-grid">
        <div class="stat-item">
          <div class="stat-item__value"><?= $totalItems ?></div>
          <div class="stat-item__label">Items Listed</div>
        </div>
        <div class="stat-item">
          <div class="stat-item__value">R<?= number_format($totalValue, 0) ?></div>
          <div class="stat-item__label">Total Value</div>
        </div>
        <div class="stat-item">
          <div class="stat-item__value"><?= htmlspecialchars($sellerName) ?></div>
          <div class="stat-item__label">Seller</div>
        </div>
      </div>
    </div>
  </div>

  <section class="section section--bordered">
    <div class="container">
      <div class="three-up">
        <article class="value-card">
          <div class="value-card__icon">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
          </div>
          <h3>List in Minutes</h3>
          <p>Add a name, description, price and photo — your item goes live in the store instantly.</p>
        </article>
        <article class="value-card">
          <div class="value-card__icon">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </div>
          <h3>Edit Any Time</h3>
          <p>Update your item details or remove a listing whenever you need to.</p>
        </article>
        <article class="value-card">
          <div class="value-card__icon">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
          </div>
          <h3>Give Clothes New Life</h3>
          <p>Every item you list helps reduce fashion waste and finds someone who will love it.</p>
        </article>
      </div>
    </div>
  </section>

  <section class="section" id="add-item">
    <div class="container">

      <?php if (!empty($message)): ?>
        <div class="dash-msg dash-msg--ok"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
        <div class="dash-msg dash-msg--err"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="form-card">
        <h2 class="form-card__title"><?= $editItem ? 'Edit Item' : 'Add New Item' ?></h2>

        <form method="POST" action="sellerDashboard.php#add-item" enctype="multipart/form-data">
          <?php if ($editItem): ?>
            <input type="hidden" name="itemID" value="<?= (int)$editItem['itemID'] ?>">
          <?php endif; ?>

          <div class="form-card__grid">

            <div class="span-2">
              <label for="itemName">Item Name <span class="req">*</span></label>
              <input type="text" id="itemName" name="itemName"
                placeholder="e.g. Vintage Levi's Denim Jacket"
                value="<?= htmlspecialchars($editItem['itemName'] ?? $_POST['itemName'] ?? '') ?>"
                required>
            </div>

            <div>
              <label for="price">Price (R) <span class="req">*</span></label>
              <input type="number" id="price" name="price" step="0.01" min="1"
                placeholder="0.00"
                value="<?= htmlspecialchars($editItem['price'] ?? $_POST['price'] ?? '') ?>"
                required>
            </div>

            <div>
              <label for="image">Item Photo <?= $editItem ? '(leave blank to keep current)' : '' ?></label>
              <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
              <?php if ($editItem && !empty($editItem['image'])): ?>
                <img src="<?= htmlspecialchars($editItem['image']) ?>" id="imgPreview" style="display:block;" alt="Current">
              <?php else: ?>
                <img id="imgPreview" alt="Preview">
              <?php endif; ?>
            </div>

            <div class="span-2">
              <label for="description">Description</label>
              <textarea id="description" name="description"
                placeholder="Describe the item — brand, material, size, condition..."><?= htmlspecialchars($editItem['description'] ?? $_POST['description'] ?? '') ?></textarea>
            </div>

          </div>

          <div class="form-card__footer">
            <button type="submit" class="button"><?= $editItem ? 'Save Changes' : 'Add to Store' ?></button>
            <?php if ($editItem): ?>
              <a href="sellerDashboard.php" class="button button--outline">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>

      <div class="listings-header">
        <h2>Items in Store</h2>
        <span class="listings-count"><?= $totalItems ?> item<?= $totalItems !== 1 ? 's' : '' ?></span>
      </div>

      <?php if (empty($items)): ?>
        <div class="listings-empty">
          <div class="listings-empty__icon">
            <svg class="icon icon--large" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"><path d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 3H8a2 2 0 00-2 2v2h12V5a2 2 0 00-2-2z"/></svg>
          </div>
          <h3>No items yet</h3>
          <p>Add your first item above and it will appear here.</p>
          <a href="#add-item" class="button">Add Your First Item</a>
        </div>
      <?php else: ?>
        <div class="listings-grid">
          <?php foreach ($items as $item): ?>
            <div class="listing-card">
              <div class="listing-card__img">
                <?php if (!empty($item['image']) && file_exists(__DIR__ . '/' . $item['image'])): ?>
                  <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['itemName']) ?>">
                <?php else: ?>
                  <div class="listing-card__placeholder">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  </div>
                <?php endif; ?>
              </div>
              <div class="listing-card__body">
                <h3 class="listing-card__name"><?= htmlspecialchars($item['itemName']) ?></h3>
                <?php if (!empty($item['description'])): ?>
                  <p class="listing-card__desc"><?= htmlspecialchars($item['description']) ?></p>
                <?php endif; ?>
                <p class="listing-card__price">R<?= number_format((float)$item['price'], 2) ?></p>
              </div>
              <div class="listing-card__actions">
                <a href="sellerDashboard.php?edit=<?= (int)$item['itemID'] ?>#add-item"
                   class="button button--outline button--small">Edit</a>
                <a href="sellerDashboard.php?delete=<?= (int)$item['itemID'] ?>"
                   class="button button--small"
                   style="background:transparent;color:var(--muted);border:1px solid var(--border);"
                   onclick="return confirm('Remove this item from the store?')">Remove</a>
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
      <p>Share yours with the Pastimes community of conscious fashion lovers.</p>
      <a class="button button--light" href="Shop.php">Browse the Shop</a>
    </div>
  </section>

  <section class="section section--accent">
    <div class="container container--narrow center-text">
      <h2>Stay in the Loop</h2>
      <p>Get tips on pricing, styling, and sustainable fashion delivered to your inbox.</p>
      <form class="newsletter-form" onsubmit="event.preventDefault();this.innerHTML='<p style=\'font-weight:500;\'>Thanks for subscribing!</p>'">
        <input class="field-input" type="email" placeholder="Enter your email" aria-label="Email" required>
        <button class="button" type="submit">Subscribe</button>
      </form>
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
        <p class="site-footer__heading">Sell</p>
        <ul class="site-footer__list">
          <li><a href="sellerDashboard.php">My Listings</a></li>
          <li><a href="sellerDashboard.php#add-item">Add an Item</a></li>
          <li><a href="logout.php">Sign out</a></li>
        </ul>
      </div>
      <div>
        <p class="site-footer__heading">About</p>
        <ul class="site-footer__list">
          <li><a href="About.php">Our story</a></li>
          <li><a href="Contact.php">Contact us</a></li>
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

  document.getElementById('image').addEventListener('change', function () {
    const preview = document.getElementById('imgPreview');
    if (this.files && this.files[0]) {
      const reader = new FileReader();
      reader.onload = e => { preview.src = e.target.result; preview.style.display = 'block'; };
      reader.readAsDataURL(this.files[0]);
    }
  });

  document.querySelectorAll('a[href="#add-item"]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      document.getElementById('add-item').scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
</script>
</body>
</html>
