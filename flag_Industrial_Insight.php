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
<title>Industrial OT Control Panel</title>
<style>
    /* ---- Reset & Body ---- */
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Courier New', monospace;
        background: #0d0d0d;
        color: #f0f0f0;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background-image: linear-gradient(160deg, #0f0f0f, #111122);
    }
    header {
        background: linear-gradient(90deg, #0a0a0a, #1a1a1a);
        padding: 20px 30px;
        font-size: 22px;
        font-weight: bold;
        border-bottom: 3px solid #0ff;
        color: #0ff;
        text-shadow: 0 0 8px #0ff;
        letter-spacing: 1px;
    }
    .container {
        flex: 1;
        padding: 25px 40px;
        max-width: 1100px;
        margin: auto;
    }

    h3 {
        color: #0ff;
        font-size: 20px;
        margin-bottom: 15px;
        text-shadow: 0 0 6px #0ff;
    }

    .log-box {
        background: #111;
        border: 1px solid #0ff;
        padding: 20px;
        height: 400px;
        overflow-y: scroll;
        white-space: pre-wrap;
        line-height: 1.6;
        font-size: 14px;
        box-shadow: 0 0 15px #0ff inset;
        border-radius: 8px;
    }

    /* Glowing log colors */
    .log-box span.system { color: #0ff; }
    .log-box span.sensor { color: #0f0; }
    .log-box span.alert { color: #f00; font-weight: bold; }
    .log-box span.note { color: #ff0; font-style: italic; }

    .form-box {
        margin-top: 25px;
        text-align: center;
    }
    input[type="text"] {
        padding: 12px;
        width: 60%;
        border: 1px solid #0ff;
        background: #111;
        color: #0ff;
        font-size: 16px;
        border-radius: 6px;
        outline: none;
        box-shadow: 0 0 10px #0ff inset;
        transition: 0.2s all;
    }
    input[type="text"]:focus {
        box-shadow: 0 0 15px #0ff inset, 0 0 15px #0ff;
    }
    button {
        padding: 12px 25px;
        margin-left: 10px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: bold;
        background: linear-gradient(45deg, #0ff, #00f);
        color: #111;
        cursor: pointer;
        box-shadow: 0 0 10px #0ff, 0 0 20px #00f;
        transition: 0.2s all;
        text-transform: uppercase;
    }
    button:hover {
        box-shadow: 0 0 20px #0ff, 0 0 40px #00f;
        transform: translateY(-2px);
    }
    .feedback {
        margin-top: 15px;
        font-weight: bold;
        color: #f0f;
        text-shadow: 0 0 6px #f0f;
        text-align: center;
    }

    /* Scrollbar glow */
    .log-box::-webkit-scrollbar {
        width: 8px;
    }
    .log-box::-webkit-scrollbar-track {
        background: #111;
    }
    .log-box::-webkit-scrollbar-thumb {
        background: #0ff;
        border-radius: 4px;
        box-shadow: 0 0 4px #0ff;
    }

    /* Mobile */
    @media (max-width: 768px) {
        input[type="text"] { width: 80%; }
        button { margin-top: 10px; display: block; width: 60%; }
    }
</style>
</head>
<body>
<header>🛠 Industrial OT Cyber Panel</header>
<div class="container">
    <h3>📟 PLC Diagnostic Log</h3>
    <div class="log-box">
<span class="system">[2025-08-19 10:22:13] SYSTEM: Motor_1 Start Command Accepted</span>
<span class="system">[2025-08-19 10:22:14] SYSTEM: Pump Station A Activated</span>
<span class="sensor">[2025-08-19 10:22:17] SENSOR: Flow Rate Stable @ 120 L/min</span>
<span class="alert">[2025-08-19 10:22:21] ALERT: Pressure Spike Detected → Reset Triggered</span>
<span class="system">[2025-08-19 10:22:23] SYSTEM: Motor_2 Shutdown Sequence Completed</span>
<span class="sensor">[2025-08-19 10:22:27] SENSOR: Tank Level = 82%</span>
<span class="system">[2025-08-19 10:22:33] LOG: N7:0 = 1024 | N7:1 = 2048 | N7:2 = 4096 | N7:3 = 1337</span>
<span class="system">[2025-08-19 10:22:35] LOG: N7:4 = 8192 | N7:5 = 16384</span>
<span class="note">[2025-08-19 10:22:39] ENGINEER NOTE: data audit incomplete — ref: doc_plant_rev3</span>
<span class="system">[2025-08-19 10:22:42] MAINTENANCE: Scheduled downtime initiated (line 4B)</span>
<span class="system">[2025-08-19 10:22:45] INFO: Valve_3 opened successfully</span>
<span class="alert">[2025-08-19 10:22:52] DIAG: Unexpected register mismatch — logged to report</span>
<span class="note">[2025-08-19 10:22:59] ENGINEER NOTE: (Confidential) Factory Incident Report → CTF{factory_data_breach_2025}</span>
<span class="system">[2025-08-19 10:23:02] SYSTEM: Operation cycle stable</span>
    </div>

    <div class="form-box">
        <form method="POST" action="score.php">
            <input type="text" name="flag" placeholder="Enter extracted flag here..." required>
            <button type="submit">Submit Flag</button>
        </form>
        <?php if ($feedback): ?>
            <div class="feedback"><?= htmlspecialchars($feedback) ?></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>

