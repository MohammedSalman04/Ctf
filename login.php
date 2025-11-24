<?php
require 'config.php'; // includes $pdo

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "⚠️ Username and password required.";
    } else {
        $stmt = $pdo->prepare("SELECT id, username, passhash, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['passhash'])) {
            $_SESSION['uid']   = $user['id'];
            $_SESSION['uname'] = $user['username'];
            $_SESSION['urole'] = $user['role'];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "❌ Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <!-- KEEPING YOUR ORIGINAL STYLING -->
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #1e1e2f, #252538);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 350px;
            padding: 30px;
            background: #2e2e42;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
            text-align: center;
        }
        .container h1 {
            margin-bottom: 20px;
            color: #00d9ff;
            font-size: 26px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            background: #1b1b2f;
            color: #fff;
        }
        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #00d9ff;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #009ec3;
        }
        .error {
            margin-top: 15px;
            color: #ff6b6b;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
    </div>
</body>
</html>
