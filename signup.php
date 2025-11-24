<?php
session_start();
require_once 'config.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['u'] ?? '');
    $p = $_POST['p'] ?? '';

    if (strlen($u) < 3 || strlen($p) < 6) {
        $err = 'Username ≥3 & Password ≥6 chars';
    } else {
        try {
            // check if username exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$u]);

            if ($stmt->fetch()) {
                $err = 'Username already exists';
            } else {
                // insert new user
                $hash = password_hash($p, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, passhash, role, created_at) VALUES (?, ?, 'player', NOW())");
                if ($stmt->execute([$u, $hash])) {
                    $_SESSION['username'] = $u;
                    $_SESSION['is_admin'] = false;
                    header('Location: dashboard.php');
                    exit;
                } else {
                    $err = 'Error creating user.';
                }
            }
        } catch (Exception $e) {
            $err = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sign Up - CTF Portal</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #1f1f1f;
      color: #f1f1f1;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .container {
      background: #2c2c2c;
      padding: 30px 40px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.6);
      width: 300px;
    }
    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #00ffd5;
    }
    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 10px 0 20px 0;
      border: none;
      border-radius: 4px;
      background: #444;
      color: #fff;
    }
    input[type="submit"] {
      width: 100%;
      background-color: #00ffd5;
      color: #000;
      padding: 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
    }
    input[type="submit"]:hover {
      background-color: #00c5a0;
    }
    .error {
      color: #ff4c4c;
      margin-bottom: 15px;
      text-align: center;
    }
    .footer {
      text-align: center;
      margin-top: 15px;
    }
    .footer a {
      color: #00ffd5;
      text-decoration: none;
    }
    .footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>CTF Sign Up</h2>
    <?php if($err): ?>
      <div class="error"><?= htmlspecialchars($err) ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="text" name="u" placeholder="Username" required>
      <input type="password" name="p" placeholder="Password" required>
      <input type="submit" value="Sign Up">
    </form>
    <div class="footer">
      Already have an account? <a href="login.php">Login</a>
    </div>
  </div>
</body>
</html>
