<?php
include("data/DBConn.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = isset($_POST['role']) && in_array($_POST['role'], ['buyer', 'seller']) ? $_POST['role'] : 'buyer';

    $stmt = $conn->prepare(" 
      INSERT INTO tblUser (username, fullName, email, password, status, role)
      VALUES (?, ?, ?, ?, 'pending', ?)
    ");

    if (!$stmt) {
      $columnCheck = $conn->query("SHOW COLUMNS FROM tblUser LIKE 'role'");
      if ($columnCheck && $columnCheck->num_rows === 0) {
        $conn->query("ALTER TABLE tblUser ADD COLUMN role ENUM('buyer','seller') DEFAULT 'buyer'");
        $stmt = $conn->prepare(" 
          INSERT INTO tblUser (username, fullName, email, password, status, role)
          VALUES (?, ?, ?, ?, 'pending', ?)
        ");
      }
    }

    if (!$stmt) {
      die("Registration is unavailable until the database schema is updated: " . $conn->error);
    }

    $stmt->bind_param("sssss", $username, $fullname, $email, $password, $role);

    if ($stmt->execute()) {
      $message = "Thanks for registering. Your account is waiting for admin approval.";
    } else {
      die("Registration failed: " . $stmt->error);
    }
}
?>

 <link rel="stylesheet" href="assets/css/styles.css">


<div class="auth-page">
  <div class="auth-card">

    <div class="auth-back"><a href="Home.php">← Back to Home</a></div>

    <div class="auth-brand">Pastimes</div>
    <div class="auth-title">Create Account</div>
    <div class="auth-sub">Join the sustainable fashion movement</div>

    <?php if (isset($message)) echo "<p class='pending-note'><strong>Success:</strong> $message</p>"; ?>

    <form method="POST">

      <div class="form-group">
        <label class="form-label">Username</label>
        <input class="form-input" name="username" required>
      </div>

      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input class="form-input" name="fullname" required>
      </div>

      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" name="email" type="email" required>
      </div>

      <div class="form-group password-wrap">
        <label class="form-label">Password</label>
        <input class="form-input" name="password" type="password" required>
      </div>

      <div class="form-group">
        <label class="form-label">Account Type</label>
        <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: normal;">
            <input type="radio" name="role" value="buyer" checked required style="width: auto; margin: 0;">
            <span>Buyer</span>
          </label>
          <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-weight: normal;">
            <input type="radio" name="role" value="seller" required style="width: auto; margin: 0;">
            <span>Seller</span>
          </label>
        </div>
      </div>

      <button class="auth-btn" type="submit">Register</button>

    </form>

    <div class="auth-footer-note">
      Already have an account? <a href="login.php">Login</a>
    </div>

  </div>
</div>