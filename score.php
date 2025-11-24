<?php
require_once 'config.php';
require_login();

// 🔒 Single source of truth — flags & challenge names
$map = [
    "Sniff_The_Truth"    => ["flag" => "CTF{sniff_the_truth_updated}",   "points" => 100],
    "Bypass_Master"      => ["flag" => "CTF{bypass_the_master_key}",     "points" => 150],
    "Sensor_Snitch"      => ["flag" => "CTF{leaky_sensor_firmware}",     "points" => 120],
    "Industrial_Insight" => ["flag" => "CTF{factory_data_breach_2025}",  "points" => 130],
];

// Mapping from flag code → dashboard challenge name
$challengeNames = [];
foreach ($map as $challenge => $info) {
    $challengeNames[$info['flag']] = $challenge;
}

// Get logged in user
$user_id = $_SESSION['uid'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Sanitize input
$submitted_flag = trim($_POST['flag'] ?? "");

// Default feedback
$_SESSION['feedback'] = "❌ Invalid flag. Try again.";

// ✅ Check submitted flag against the map
if (isset($challengeNames[$submitted_flag])) {
    $challenge = $challengeNames[$submitted_flag];
    $points    = $map[$challenge]['points'];

    // Check if already submitted
    $stmt = $pdo->prepare("SELECT 1 FROM submissions WHERE user_id = ? AND challenge = ?");
    $stmt->execute([$user_id, $challenge]);

    if ($stmt->fetch()) {
        $_SESSION['feedback'] = "⚠ You already submitted this flag for <b>{$challenge}</b>.";
    } else {
        // Insert into submissions
        $stmt = $pdo->prepare("INSERT INTO submissions (user_id, challenge, flag_code) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $challenge, $submitted_flag]);

        // Update score in users table
        $stmt = $pdo->prepare("UPDATE users SET score = score + ? WHERE id = ?");
        $stmt->execute([$points, $user_id]);

        $_SESSION['feedback'] = "✅ Correct! You earned {$points} pts for <b>{$challenge}</b>.";
    }
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>
