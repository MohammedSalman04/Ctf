<?php
require_once 'auth.php';
require_login();

// Handle feedback if flag was wrong
$feedback = $_SESSION['feedback'] ?? "";
unset($_SESSION['feedback']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IDS CTF Portal - Sniff The Truth</title>
    <style>
        body { margin:0; font-family:"Segoe UI",Tahoma,sans-serif; background-color:#0c0f14; color:#e4e6eb; }
        header { background:#1c1f26; padding:20px; font-size:22px; font-weight:bold; text-align:center;
                 border-bottom:2px solid #2d323b; color:#4cc9f0; letter-spacing:1px; }
        .container { max-width:1000px; margin:40px auto; background:#1a1d24; padding:30px; border-radius:14px; box-shadow:0 0 30px rgba(0,0,0,0.7); }
        h1 { color:#4cc9f0; text-align:center; font-size:26px; margin-bottom:20px; }
        p { text-align:center; font-size:15px; line-height:1.5; color:#aeb6c1; }
        .file-box { background:#11151c; border:1px solid #2d323b; padding:20px; margin:30px 0; border-radius:10px; text-align:center; }
        a.download-btn { display:inline-block; padding:12px 20px; background:linear-gradient(135deg,#007acc,#005b99);
                         color:#fff; text-decoration:none; border-radius:8px; font-weight:bold; transition:background .3s; }
        a.download-btn:hover { background:linear-gradient(135deg,#0096ff,#007acc); }
        form { text-align:center; margin-top:25px; }
        input[type="text"] { padding:12px; width:60%; border:1px solid #2d323b; border-radius:8px;
                             background:#0c0f14; color:#fff; font-size:14px; box-shadow:inset 0 0 6px rgba(0,0,0,0.4); }
        button { padding:12px 22px; margin-left:10px; background:linear-gradient(135deg,#198754,#146c43);
                 border:none; border-radius:8px; color:white; font-weight:bold; cursor:pointer; font-size:14px;
                 transition:background .3s; }
        button:hover { background:linear-gradient(135deg,#25a168,#1e814f); }
        .error { color:#ff6b6b; margin-top:15px; text-align:center; font-weight:bold; }
        footer { margin-top:40px; text-align:center; font-size:12px; color:#555d6d; }
        .feedback { margin-top:15px; text-align:center; font-weight:bold; color:#4cc9f0; }
    </style>
</head>
<body>
<header>🚨 IDS Challenge Portal</header>
<div class="container">
    <h1>Sniff_The_Truth</h1>
    <p>We captured suspicious IDS traffic logs from an enterprise network.<br>Your task: Analyze the logs and extract the hidden flag.</p>

    <div class="file-box">
        <p><strong>Download IDS Logs:</strong></p>
        <a class="download-btn" href="traffic_capture.log" download>📥 Download traffic_capture.log</a>
    </div>

    <form method="POST" action="score.php">
        <input type="text" name="flag" placeholder="Enter your flag" required>
        <button type="submit">Submit Flag</button>
    </form>

    <?php if ($feedback): ?>
        <div class="feedback"><?= htmlspecialchars($feedback) ?></div>
    <?php endif; ?>
</div>
<footer>IDS CTF Platform © 2025</footer>
</body>
</html>
