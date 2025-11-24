<?php
require_once 'config.php';
require_login();

$challenge = "Bypass_Master";
$points = 150;
$message = "";

// ✅ Flag starts with "echo"
$flag_ctf = "echo CTF{bypass_the_master_key}";
$db_flag  = "CTF{bypass_the_master_key}"; // what exists in DB

// Double Base64 with reversed intermediate
$first_encode = base64_encode($flag_ctf);
$reversed_first = strrev($first_encode);
$double_encoded = base64_encode($reversed_first);

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_flag = trim($_POST['flag']);

    if ($submitted_flag === $flag_ctf) {
        if (!isset($_SESSION['flags'][$challenge])) {
            $_SESSION['flags'][$challenge] = true;
            $_SESSION['score'] = ($_SESSION['score'] ?? 0) + $points;

            // ✅ Save to DB with correct foreign key (db_flag)
            $stmt = $pdo->prepare("INSERT INTO submissions (user_id, challenge, flag_code) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['uid'], $challenge, $db_flag]);

            $stmt = $pdo->prepare("UPDATE users SET score = score + ? WHERE id = ?");
            $stmt->execute([$points, $_SESSION['uid']]);

            $message = "✅ Correct! You earned {$points} pts for <b>{$challenge}</b>.";
        } else {
            $message = "⚠ You already submitted this flag for <b>{$challenge}</b>.";
        }
        header("Refresh:1; url=dashboard.php");
        exit;
    } else {
        $message = "❌ Invalid flag. Try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= $challenge ?> Challenge</title>
<style>
body { background:#121212; color:#f5f5f5; font-family:Arial,sans-serif; margin:0; padding:0; }
.container { max-width:720px; margin:80px auto; padding:40px; background:#1e1e1e; border-radius:12px; box-shadow:0 0 30px rgba(0,0,0,0.8); text-align:center; }
h1 { color:#00ffd5; }
.hint {
    background:#2a2a2a; padding:24px; border-radius:12px; margin:30px 0; font-family:monospace; font-size:14px; color:#ffd700; line-height:1.8; position:relative; overflow:hidden;
}
.hint code { display:inline-block; word-break:break-all; position:relative; line-height:1.5; color:#00ffff; font-weight:bold; }
.hint::after {
    content:"⚡✦✧★☄☯☢☣❂";
    position:absolute; top:0; right:0; font-size:18px; opacity:0.15;
    animation: float 6s infinite alternate;
}
@keyframes float { 0%{transform:translateY(0px) rotate(0deg);} 100%{transform:translateY(-12px) rotate(5deg);} }
input[type="text"] { width:80%; padding:12px; margin:15px 0; border:none; border-radius:8px; outline:none; font-size:15px; }
.btn { padding:10px 18px; border-radius:8px; border:none; background:#00ffd5; color:#121212; font-weight:bold; cursor:pointer; transition:0.3s; }
.btn:hover { background:#00e6c0; }
.message { margin-top:15px; font-weight:bold; color:#ff5555; }
</style>
</head>
<body>
<div class="container">
<h1><?= $challenge ?> (<?= $points ?> pts)</h1>
<p class="hint">
💡 The labyrinth whispers:  

Your key begins with a command.  
It is not just a flag... it is an <b>invocation</b>.  

Here is your sigil:  
<code><?= $double_encoded ?></code>  

Decode → reverse → decode again,  
and remember to <b>echo</b> the truth.  
</p>

<form method="POST">
<input type="text" name="flag" placeholder="Enter your flag here" required><br>
<button type="submit" class="btn">Submit</button>
</form>

<?php if (!empty($message)): ?>
<p class="message"><?= $message ?></p>
<?php endif; ?>
</div>
</body>
</html>
