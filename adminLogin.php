<?php
session_start();
include("data/DBConn.php");

if (isset($_SESSION['adminID'])) {
    header("Location: adminDashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM tblAdmin WHERE username=? ");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {

        $_SESSION['adminID'] = $admin['adminID'];

        header("Location: adminDashboard.php");
        exit;

    } else {
        $error = "Invalid admin login";
    }
}
?>

<link rel="stylesheet" href="assets/css/styles.css">

<div class="auth-page">
  <div class="auth-card">

    <div class="auth-back"><a href="Home.php">← Back to Home</a></div>

    <div class="admin-badge">ADMIN ACCESS</div>
    <div class="auth-title">Admin Login</div>

    <?php if (isset($error)) echo "<p class='pending-note'>$error</p>"; ?>

    <form method="POST">

      <div class="form-group">
        <label class="form-label">Username</label>
        <input class="form-input" name="username" required>
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input class="form-input" name="password" type="password" required>
      </div>

      <button class="auth-btn" type="submit">Login</button>

    </form>

  </div>
</div>