<?php
session_start();
require_once 'data/DBConn.php';
require_once 'includes/schema.php';

if (!isset($_SESSION['adminID'])) {
    header('Location: adminLogin.php');
    exit;
}

$message = '';
$error   = '';

ensure_pastimes_schema($conn);

// DELETE any item
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delID = (int) $_GET['delete'];
    $stmt  = $conn->prepare('DELETE FROM tblClothes WHERE itemID = ?');
    if ($stmt) {
        $stmt->bind_param('i', $delID);
        $stmt->execute();
        $stmt->close();
        $message = 'Item deleted successfully.';
    }
}

// LOAD item for editing
$editItem = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editID = (int) $_GET['edit'];
    $stmt   = $conn->prepare('SELECT * FROM tblClothes WHERE itemID = ?');
    if ($stmt) {
        $stmt->bind_param('i', $editID);
        $stmt->execute();
        $editItem = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// ADD / EDIT submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemID      = isset($_POST['itemID']) && is_numeric($_POST['itemID']) ? (int) $_POST['itemID'] : null;
    $sellerID    = isset($_POST['sellerID']) && is_numeric($_POST['sellerID']) ? (int) $_POST['sellerID'] : 0;
    $itemName    = trim($_POST['itemName'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = trim($_POST['price'] ?? '');
    $category    = validate_listing_category(trim($_POST['category'] ?? 'Tops'));
    $size        = validate_listing_size(trim($_POST['size'] ?? 'M'));
    $condition   = validate_listing_condition(trim($_POST['condition'] ?? 'Good'));
    $imagePath   = '';

    if ($sellerID <= 0) {
        $error = 'Please select a seller for this item.';
    } elseif (empty($itemName) || empty($price)) {
        $error = 'Item name and price are required.';
    } elseif (!is_numeric($price) || (float) $price <= 0) {
        $error = 'Please enter a valid price.';
    } else {
        $sellerCheck = $conn->prepare("SELECT userID FROM tblUser WHERE userID = ? AND role = 'seller' AND status = 'active'");
        if ($sellerCheck) {
            $sellerCheck->bind_param('i', $sellerID);
            $sellerCheck->execute();
            $sellerResult = $sellerCheck->get_result()->fetch_assoc();
            $sellerCheck->close();
            if (!$sellerResult) {
                $error = 'Selected seller is not active.';
            }
        }

        if (empty($error) && !empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/assets/images/clothes/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($ext, $allowed, true)) {
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
                    $stmt = $conn->prepare('UPDATE tblClothes SET sellerID=?, itemName=?, description=?, price=?, category=?, size=?, `condition`=?, image=? WHERE itemID=?');
                    if ($stmt) {
                        $stmt->bind_param('issdssssi', $sellerID, $itemName, $description, $price, $category, $size, $condition, $imagePath, $itemID);
                    }
                } else {
                    $stmt = $conn->prepare('UPDATE tblClothes SET sellerID=?, itemName=?, description=?, price=?, category=?, size=?, `condition`=? WHERE itemID=?');
                    if ($stmt) {
                        $stmt->bind_param('issdsssi', $sellerID, $itemName, $description, $price, $category, $size, $condition, $itemID);
                    }
                }
                if (!empty($stmt)) {
                    $stmt->execute();
                    $stmt->close();
                    $message  = 'Item updated successfully.';
                    $editItem = null;
                } else {
                    $error = 'Unable to prepare update query.';
                }
            } else {
                $stmt = $conn->prepare('INSERT INTO tblClothes (sellerID, itemName, description, price, category, size, `condition`, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                if ($stmt) {
                    $stmt->bind_param('issdssss', $sellerID, $itemName, $description, $price, $category, $size, $condition, $imagePath);
                    $stmt->execute();
                    $stmt->close();
                    $message = 'Item added successfully.';
                } else {
                    $error = 'Unable to prepare insert query.';
                }
            }
        }
    }
}

$sellers = [];
$sellerResult = $conn->query("SELECT userID, username, fullName FROM tblUser WHERE role = 'seller' AND status = 'active' ORDER BY username ASC");
if ($sellerResult) {
    while ($row = $sellerResult->fetch_assoc()) {
        $sellers[] = $row;
    }
}

$items = [];
$itemResult = $conn->query('SELECT c.*, u.username AS sellerName FROM tblClothes c LEFT JOIN tblUser u ON c.sellerID = u.userID ORDER BY c.itemID DESC');
if ($itemResult) {
    while ($row = $itemResult->fetch_assoc()) {
        $items[] = $row;
    }
}

$totalItems = count($items);
$selectedSeller = $editItem['sellerID'] ?? $_POST['sellerID'] ?? '';
?>
<link rel="stylesheet" href="assets/css/styles.css">

<div class="auth-page">
    <div class="auth-card admin-dashboard-card" style="max-width:980px;">

        <div class="auth-back"><a href="adminDashboard.php">&larr; Back to Admin Dashboard</a></div>

        <div class="admin-dashboard-header">
            <div>
                <div class="admin-badge">ADMIN DASHBOARD</div>
                <h2 class="auth-title" style="text-align:left; margin-top:0.9rem;">Manage Store Items</h2>
                <div class="auth-sub" style="text-align:left; margin-bottom:0;">Add, edit, or remove seller listings across the store.</div>
            </div>
            <div class="admin-dashboard-actions">
                <a href="adminDashboard.php" class="admin-secondary-link">User Management</a>
                <a href="logout.php" class="admin-secondary-link">Logout Admin</a>
            </div>
        </div>

        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <span class="admin-stat-label">Total Items</span>
                <strong><?= $totalItems ?></strong>
                <small>All seller listings</small>
            </div>
            <div class="admin-stat-card">
                <span class="admin-stat-label">Active Sellers</span>
                <strong><?= count($sellers) ?></strong>
                <small>Available for new items</small>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="dash-msg dash-msg--ok" style="margin-bottom:1rem;"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="dash-msg dash-msg--err" style="margin-bottom:1rem;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="admin-section" id="add-item">
            <div class="admin-section-head">
                <h3 class="admin-section-title"><?= $editItem ? 'Edit Item' : 'Add New Item' ?></h3>
            </div>

            <?php if (empty($sellers)): ?>
                <p class="pending-note">No active sellers available. Approve a seller account first.</p>
            <?php else: ?>
                <form method="POST" action="adminItems.php#add-item" enctype="multipart/form-data" class="admin-item-form">
                    <?php if ($editItem): ?>
                        <input type="hidden" name="itemID" value="<?= (int) $editItem['itemID'] ?>">
                    <?php endif; ?>

                    <label class="admin-form-label" for="sellerID">Seller <span class="req">*</span></label>
                    <select class="admin-form-input" id="sellerID" name="sellerID" required>
                        <option value="">Select seller</option>
                        <?php foreach ($sellers as $seller): ?>
                            <option value="<?= (int) $seller['userID'] ?>"<?= (string) $selectedSeller === (string) $seller['userID'] ? ' selected' : '' ?>>
                                <?= htmlspecialchars($seller['username']) ?> (<?= htmlspecialchars($seller['fullName']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label class="admin-form-label" for="itemName">Item Name <span class="req">*</span></label>
                    <input class="admin-form-input" type="text" id="itemName" name="itemName" required
                        value="<?= htmlspecialchars($editItem['itemName'] ?? $_POST['itemName'] ?? '') ?>">

                    <div class="admin-form-row">
                        <div>
                            <label class="admin-form-label" for="price">Price (R) <span class="req">*</span></label>
                            <input class="admin-form-input" type="number" id="price" name="price" step="0.01" min="1" required
                                value="<?= htmlspecialchars($editItem['price'] ?? $_POST['price'] ?? '') ?>">
                        </div>
                        <div>
                            <label class="admin-form-label" for="category">Category</label>
                            <select class="admin-form-input" id="category" name="category">
                                <?php
                                $selectedCategory = $editItem['category'] ?? $_POST['category'] ?? 'Tops';
                                foreach (listing_category_options() as $option):
                                ?>
                                    <option value="<?= htmlspecialchars($option) ?>"<?= $selectedCategory === $option ? ' selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="admin-form-row">
                        <div>
                            <label class="admin-form-label" for="size">Size</label>
                            <select class="admin-form-input" id="size" name="size">
                                <?php
                                $selectedSize = $editItem['size'] ?? $_POST['size'] ?? 'M';
                                foreach (listing_size_options() as $option):
                                ?>
                                    <option value="<?= htmlspecialchars($option) ?>"<?= $selectedSize === $option ? ' selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="admin-form-label" for="condition">Condition</label>
                            <select class="admin-form-input" id="condition" name="condition">
                                <?php
                                $selectedCondition = $editItem['condition'] ?? $_POST['condition'] ?? 'Good';
                                foreach (listing_condition_options() as $option):
                                ?>
                                    <option value="<?= htmlspecialchars($option) ?>"<?= $selectedCondition === $option ? ' selected' : '' ?>><?= htmlspecialchars($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <label class="admin-form-label" for="image">Item Photo <?= $editItem ? '(leave blank to keep current)' : '' ?></label>
                    <input class="admin-form-input" type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp">
                    <?php if ($editItem && !empty($editItem['image'])): ?>
                        <img src="<?= htmlspecialchars($editItem['image']) ?>" alt="Current item" style="width:5rem;aspect-ratio:3/4;object-fit:cover;border-radius:var(--radius);margin-top:0.5rem;">
                    <?php endif; ?>

                    <label class="admin-form-label" for="description">Description</label>
                    <textarea class="admin-form-input" id="description" name="description" rows="4"><?= htmlspecialchars($editItem['description'] ?? $_POST['description'] ?? '') ?></textarea>

                    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:1rem;">
                        <button type="submit" class="button"><?= $editItem ? 'Save Changes' : 'Add Item' ?></button>
                        <?php if ($editItem): ?>
                            <a href="adminItems.php" class="button button--outline">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="admin-section">
            <div class="admin-section-head">
                <h3 class="admin-section-title">All Store Items</h3>
                <span class="admin-section-count"><?= $totalItems ?></span>
            </div>

            <?php if (empty($items)): ?>
                <p class="pending-note">No items in the store yet.</p>
            <?php else: ?>
                <div class="admin-user-list">
                    <?php foreach ($items as $item): ?>
                        <div class="admin-user-card">
                            <div style="display:flex;gap:1rem;align-items:flex-start;">
                                <?php if (!empty($item['image']) && file_exists(__DIR__ . '/' . $item['image'])): ?>
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="" style="width:4rem;aspect-ratio:3/4;object-fit:cover;border-radius:var(--radius);">
                                <?php endif; ?>
                                <div>
                                    <div class="admin-user-name"><?= htmlspecialchars($item['itemName']) ?></div>
                                    <div class="admin-user-email">R<?= number_format((float) $item['price'], 2) ?> &middot; Seller: <?= htmlspecialchars($item['sellerName'] ?? 'Unassigned') ?></div>
                                    <div style="font-size:0.8rem;color:var(--muted);margin-top:0.3rem;">
                                        <?= htmlspecialchars(validate_listing_category($item['category'] ?? 'Tops')) ?>
                                        &middot; Size <?= htmlspecialchars(validate_listing_size($item['size'] ?? 'M')) ?>
                                        &middot; <?= htmlspecialchars(validate_listing_condition($item['condition'] ?? 'Good')) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="admin-user-actions">
                                <a href="adminItems.php?edit=<?= (int) $item['itemID'] ?>#add-item" class="admin-action-link admin-action-link--approve">Edit</a>
                                <a href="adminItems.php?delete=<?= (int) $item['itemID'] ?>" class="admin-action-link admin-action-link--delete" onclick="return confirm('Delete this item?')">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<style>
    .admin-item-form { display: grid; gap: 0.75rem; }
    .admin-form-label { display: block; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted); margin-bottom: 0.35rem; }
    .admin-form-input { width: 100%; padding: 0.75rem 0.9rem; border: 1.5px solid var(--border); border-radius: var(--radius); font-family: inherit; font-size: 0.95rem; }
    .admin-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
    .req { color: #ef4444; }
    .dash-msg--ok { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 0.85rem 1rem; border-radius: var(--radius); }
    .dash-msg--err { background: #fdf0f0; border: 1px solid #f5c6c6; color: #a33; padding: 0.85rem 1rem; border-radius: var(--radius); }
    @media (max-width: 640px) { .admin-form-row { grid-template-columns: 1fr; } }
</style>
