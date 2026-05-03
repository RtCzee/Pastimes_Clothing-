

<?php
session_start();
include("data/DBConn.php");

if (!isset($_SESSION['adminID'])) {
    header("Location: adminLogin.php");
    exit;
}

/* APPROVE/DECLINE */
if (isset($_GET['action']) && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    if ($_GET['action'] == "approve") {
        mysqli_query($conn, "UPDATE tblUser SET status='active' WHERE userID=$id");
    }

    if ($_GET['action'] == "decline") {
        mysqli_query($conn, "UPDATE tblUser SET status='declined' WHERE userID=$id");
    }

    header("Location: adminDashboard.php");
    exit;
}

/* GET USERS */
$pending = mysqli_query($conn, "SELECT * FROM tblUser WHERE status='pending'");
$active  = mysqli_query($conn, "SELECT * FROM tblUser WHERE status='active'");
$pendingCount = mysqli_num_rows($pending);
$activeCount = mysqli_num_rows($active);
?>

<link rel="stylesheet" href="assets/css/styles.css">

<div class="auth-page">
    <div class="auth-card admin-dashboard-card" style="max-width:980px;">

        <div class="auth-back"><a href="Home.php">← Back to Home</a></div>

        <div class="admin-dashboard-header">
            <div>
                <div class="admin-badge">ADMIN DASHBOARD</div>
                <h2 class="auth-title" style="text-align:left; margin-top:0.9rem;">User Management</h2>
                <div class="auth-sub" style="text-align:left; margin-bottom:0;">Review registrations and keep the member list in shape.</div>
            </div>
            <div class="admin-dashboard-actions">
                <a href="logout.php" class="admin-secondary-link">Logout Admin</a>
            </div>
        </div>

        <div class="admin-stats-grid">
            <div class="admin-stat-card">
                <span class="admin-stat-label">Pending</span>
                <strong><?= $pendingCount ?></strong>
                <small>Waiting for approval</small>
            </div>
            <div class="admin-stat-card">
                <span class="admin-stat-label">Active</span>
                <strong><?= $activeCount ?></strong>
                <small>Approved users</small>
            </div>
        </div>

        <div class="admin-section">
            <div class="admin-section-head">
                <h3 class="admin-section-title">Pending Users</h3>
                <span class="admin-section-count"><?= $pendingCount ?></span>
            </div>

            <?php if ($pendingCount == 0): ?>
                <p class="pending-note">No pending users</p>
            <?php else: ?>
                <div class="admin-user-list">
                    <?php while ($u = mysqli_fetch_assoc($pending)): ?>
                        <div class="admin-user-card admin-user-card--pending">
                            <div>
                                <div class="admin-user-name"><?= htmlspecialchars($u['username']) ?></div>
                                <div class="admin-user-email"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                            <div class="admin-user-actions">
                                <a href="adminDashboard.php?action=approve&id=<?= $u['userID'] ?>" class="admin-action-link admin-action-link--approve">Approve</a>
                                <a href="adminDashboard.php?action=decline&id=<?= $u['userID'] ?>" class="admin-action-link admin-action-link--decline">Decline</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-section">
            <div class="admin-section-head">
                <h3 class="admin-section-title">Active Users</h3>
                <span class="admin-section-count"><?= $activeCount ?></span>
            </div>

            <?php if ($activeCount == 0): ?>
                <p class="pending-note">No active users</p>
            <?php else: ?>
                <div class="admin-user-list">
                    <?php while ($u = mysqli_fetch_assoc($active)): ?>
                        <div class="admin-user-card">
                            <div>
                                <div class="admin-user-name"><?= htmlspecialchars($u['username']) ?></div>
                                <div class="admin-user-email"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                            <span class="admin-status-chip">Active</span>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>