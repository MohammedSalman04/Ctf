<?php
require_once 'auth.php'; // auth.php includes config.php and defines require_login()
require_login();

// Initialize countdown only once
if (!isset($_SESSION['countdown_end'])) {
    $_SESSION['countdown_end'] = time() + 3600; // 1 hour
}
$remaining = $_SESSION['countdown_end'] - time();
if ($remaining < 0) $remaining = 0;

$user_id = $_SESSION['uid'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// 🔎 Load all challenges dynamically from flags table
$stmt = $pdo->query("SELECT code, category, points FROM flags");
$map = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Map flag codes to challenge display names + PHP file names
    $challengeMap = [
        "CTF{sniff_the_truth_updated}"   => ["name" => "Sniff_The_Truth",    "file" => "sniff_the_truth.php"],
        "CTF{bypass_the_master_key}"     => ["name" => "Bypass_Master",      "file" => "flag_Bypass_Master.php"],
        "CTF{leaky_sensor_firmware}"     => ["name" => "Sensor_Snitch",      "file" => "iot_portal.php"],
        "CTF{factory_data_breach_2025}"  => ["name" => "Industrial_Insight", "file" => "flag_Industrial_Insight.php"],
    ];

    if (isset($challengeMap[$row['code']])) {
        $info = $challengeMap[$row['code']];
        $map[$info['name']] = [
            "flag"   => $row['code'],
            "points" => $row['points'],
            "file"   => $info['file'],
            "cat"    => $row['category'],
        ];
    }
}

// ✅ Load submitted challenges
$stmt = $pdo->prepare("SELECT challenge FROM submissions WHERE user_id = ?");
$stmt->execute([$user_id]);
$submitted_flags = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $submitted_flags[$row['challenge']] = true;
}

// ✅ Always calculate score fresh from DB (sum of real flag points)
$stmt = $pdo->prepare("
    SELECT COALESCE(SUM(f.points), 0)
    FROM submissions s
    JOIN flags f ON s.flag_code = f.code
    WHERE s.user_id = ?
");
$stmt->execute([$user_id]);
$score = (int) $stmt->fetchColumn();

$_SESSION['flags'] = $submitted_flags;
$_SESSION['score'] = $score;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CTF Dashboard</title>
    <style>
        :root{
            --bg-start:#0b1220; --bg-end:#101826;
            --card:rgba(255,255,255,0.04); --stroke:rgba(255,255,255,0.08);
            --text:#e7edf6; --muted:#a9b6c8; --accent:#3b82f6; --accent-hover:#2563eb;
            --good:#22c55e; --warning:#f59e0b;
            --shadow:0 10px 30px rgba(0,0,0,0.35);
        }
        *{box-sizing:border-box} html,body{height:100%}
        body{margin:0;font-family:ui-sans-serif,system-ui,Segoe UI,Roboto,Helvetica,Arial,Apple Color Emoji,Segoe UI Emoji;
            color:var(--text);
            background:radial-gradient(1200px 800px at 10% -10%, rgba(59,130,246,0.08), transparent 60%),
                       radial-gradient(800px 600px at 110% 20%, rgba(16,185,129,0.07), transparent 60%),
                       linear-gradient(180deg, var(--bg-start), var(--bg-end));
            display:flex;flex-direction:column;}
        header{padding:22px 20px;text-align:center;position:sticky;top:0;backdrop-filter:blur(10px);
            background:linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
            border-bottom:1px solid var(--stroke);z-index:10;}
        header .title{font-size:20px;font-weight:700;letter-spacing:.3px;}
        .countdown{max-width:1000px;margin:16px auto 0;padding:10px 14px;border:1px solid var(--stroke);
            background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.015));
            border-radius:12px;text-align:center;font-weight:600;color:var(--warning);box-shadow:var(--shadow);}
        .container{max-width:1000px;width:92%;margin:24px auto 40px;padding:24px;border-radius:16px;background:var(--card);
            border:1px solid var(--stroke);backdrop-filter:blur(12px);box-shadow:var(--shadow);}
        h1{margin:0 0 14px;text-align:center;font-size:24px;font-weight:800;letter-spacing:.2px;color:#dfe7f5;}
        .subtle{text-align:center;color:var(--muted);margin-bottom:18px;font-size:14px;}
        table{width:100%;border-collapse:separate;border-spacing:0 10px;}
        thead th{text-align:left;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);padding:0 14px 4px;}
        tbody tr{background:linear-gradient(180deg, rgba(255,255,255,0.045), rgba(255,255,255,0.025));
            border:1px solid var(--stroke);border-radius:12px;overflow:hidden;box-shadow:0 6px 20px rgba(0,0,0,0.25);
            transition:transform .15s ease, box-shadow .15s ease;}
        tbody tr:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(0,0,0,0.3);}
        td{padding:16px 14px;} .challenge a{color:var(--accent);text-decoration:none;font-weight:700;}
        .challenge a:hover{color:var(--accent-hover);text-decoration:underline;}
        .pts{font-weight:700;} .status{display:flex;align-items:center;gap:8px;justify-content:flex-start;}
        .pill{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;font-size:12px;
            border:1px solid var(--stroke);background:rgba(255,255,255,0.04);}
        .pill.ok{color:var(--good);border-color:rgba(34,197,94,0.25);background:rgba(34,197,94,0.08);}
        .pill.pending{color:#cbd5e1;}
        .actions{margin-top:22px;display:flex;justify-content:center;}
        .btn{appearance:none;border:none;padding:12px 18px;border-radius:12px;
            background:linear-gradient(180deg,#e5eaf1,#d7dfe8);color:#1f2a37;font-weight:800;cursor:pointer;
            box-shadow:0 8px 18px rgba(18,24,38,0.25);transition:transform .12s ease, box-shadow .12s ease;text-decoration:none;}
        .btn:hover{transform:translateY(-1px);box-shadow:0 12px 24px rgba(18,24,38,0.32);}
        .btn:active{transform:translateY(0);}
        @media(max-width:720px){.container{padding:16px}td,thead th{padding-left:12px;padding-right:12px}}
    </style>
    <script>
        let remaining = <?= $remaining ?>;
        function updateCountdown(){
            const el=document.getElementById("countdown");
            if(!el) return;
            if(remaining<=0){el.textContent="⏳ Time's up!";return;}
            const h=Math.floor(remaining/3600),m=Math.floor((remaining%3600)/60),s=remaining%60;
            el.textContent=`⏳ Countdown: ${h}h ${m}m ${s}s`;remaining--;setTimeout(updateCountdown,1000);
        }
        window.addEventListener('DOMContentLoaded',updateCountdown);
    </script>
</head>
<body>
    <header>
        <div class="title">
            Welcome <?= htmlspecialchars($_SESSION['uname']) ?> &middot; Score: <?= $score ?> pts
        </div>
    </header>

    <div class="countdown" id="countdown">⏳ Countdown: —</div>

    <div class="container">
        <h1>Challenge Dashboard</h1>
        <div class="subtle">Track your progress and dive into each task. Good luck!</div>

        <table aria-label="Challenges">
            <thead>
                <tr><th>Challenge</th><th>Category</th><th>Points</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($map as $challenge => $info): ?>
                <tr onclick="window.location='<?= $info['file'] ?>'" style="cursor:pointer;">
                    <td class="challenge"><a href="<?= $info['file'] ?>"><?= htmlspecialchars($challenge) ?></a></td>
                    <td><?= htmlspecialchars($info['cat']) ?></td>
                    <td class="pts"><?= $info['points'] ?></td>
                    <td class="status">
                        <?= !empty($submitted_flags[$challenge]) 
                            ? '<span class="pill ok">✔ Submitted</span>' 
                            : '<span class="pill pending">Not Submitted</span>' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="actions">
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>
</body>
</html>
