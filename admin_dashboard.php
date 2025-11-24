<?php
session_start();
if (!isset($_SESSION['username']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
</head>
<body style="background:#1f1f1f; color:#fff; font-family:Arial; text-align:center; padding-top:50px;">
  <h1>Welcome, Admin</h1>
  <p>Here you can manage challenges, users, and flags.</p>
  <a href="logout.php" style="color:#00ffd5;">Logout</a>
</body>
</html>
