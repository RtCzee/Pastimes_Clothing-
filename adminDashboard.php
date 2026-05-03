

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
?>

<link rel="stylesheet" href="assets/css/styles.css">

<div class="auth-page">
  <div class="auth-card" style="max-width:900px;">

        <div class="auth-back"><a href="Home.php">← Back to Home</a></div>

        <div class="admin-badge">ADMIN DASHBOARD</div>
    <h2 class="auth-title">User Management</h2>

    <!-- PENDING USERS -->
    <h3 class="auth-sub">Pending Users</h3>

    <?php if (mysqli_num_rows($pending) == 0): ?>
        <p class="pending-note">No pending users</p>
    <?php endif; ?>

    <?php while ($u = mysqli_fetch_assoc($pending)): ?>
        <div style="padding:10px;border:1px solid var(--border);margin-bottom:10px;border-radius:var(--radius);">
            <strong><?= $u['username'] ?></strong> - <?= $u['email'] ?>

            <a href="adminDashboard.php?action=approve&id=<?= $u['userID'] ?>"
   style="color:green;font-weight:600;">
   Approve
</a>

<a href="adminDashboard.php?action=decline&id=<?= $u['userID'] ?>"
   style="color:red;font-weight:600;margin-left:10px;">
   Decline
</a>
        </div>
    <?php endwhile; ?>

    <hr style="margin:2rem 0;">

    <!-- ACTIVE USERS -->
    <h3 class="auth-sub">Active Users</h3>

    <?php while ($u = mysqli_fetch_assoc($active)): ?>
        <div style="padding:10px;border:1px solid var(--border);margin-bottom:10px;border-radius:var(--radius);">
            <strong><?= $u['username'] ?></strong> - <?= $u['email'] ?>
            <span style="float:right;color:var(--muted);">Active</span>
        </div>
    <?php endwhile; ?>

    <div class="auth-footer-note">
        <a href="logout.php">Logout Admin</a>
    </div>

  </div>
</div>