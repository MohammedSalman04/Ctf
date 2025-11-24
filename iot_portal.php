<?php
require_once 'auth.php'; // includes config.php + functions
require_login();

// Pull & clear any feedback set by score.php (success or invalid)
$flash = $_SESSION['feedback'] ?? '';
unset($_SESSION['feedback']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>IoT Telemetry Console – Sensor_Snitch</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <style>
    :root{
      --bg:#0e141b;
      --card:#161e29;
      --ink:#e9eef6;
      --muted:#98a6b9;
      --accent:#4f7cff;
      --accent-2:#3ddc97;
      --warn:#e9b949;
      --bad:#ff6b6b;
      --ok:#22c55e;
      --hair:#223043;
      --shadow:0 10px 28px rgba(0,0,0,.35);
    }
    *{box-sizing:border-box}
    body{
      margin:0;
      font-family: ui-sans-serif, system-ui, Segoe UI, Roboto, Helvetica, Arial;
      color:var(--ink);
      background:
        radial-gradient(1000px 600px at 120% -10%, rgba(77,124,255,.10), transparent 60%),
        radial-gradient(900px 700px at -10% 20%, rgba(61,220,151,.08), transparent 60%),
        linear-gradient(180deg,#0b1118, var(--bg));
      min-height:100vh;
    }
    header{
      position:sticky; top:0; z-index:10;
      backdrop-filter: blur(10px);
      background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
      border-bottom:1px solid var(--hair);
      padding:18px 16px;
      text-align:center;
      font-weight:800;
      letter-spacing:.2px;
    }
    .wrap{
      max-width:1100px; margin:26px auto; padding:0 18px;
      display:grid; grid-template-columns: 1fr 420px; gap:22px;
    }
    @media (max-width: 980px){ .wrap{ grid-template-columns: 1fr; } }

    .panel{
      background:var(--card);
      border:1px solid var(--hair);
      border-radius:14px;
      box-shadow:var(--shadow);
    }
    .panel h2{
      margin:0; padding:16px 18px;
      font-size:16px; letter-spacing:.06em; text-transform:uppercase;
      color:var(--muted);
      border-bottom:1px solid var(--hair);
    }
    .panel .body{ padding:16px 18px; }

    .feed{
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
      font-size:13px;
      background:#0b1017;
      border:1px solid #111a27;
      border-radius:10px;
      padding:12px;
      height:360px; overflow:auto; white-space:pre;
    }

    .hint{
      margin-top:12px; color:var(--muted); font-size:14px;
    }

    .formcard{
      background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
      border:1px solid var(--hair);
      border-radius:12px;
      padding:16px;
    }
    label{ display:block; font-size:13px; color:var(--muted); margin-bottom:6px; }
    input[type="text"]{
      width:100%; padding:12px 12px; border-radius:10px; border:1px solid #2a3a50;
      background:#0b1017; color:var(--ink); outline:none;
    }
    .row{ display:flex; gap:10px; margin-top:12px; }
    .btn{
      appearance:none; border:none; cursor:pointer;
      padding:11px 16px; border-radius:10px; font-weight:800;
      background:linear-gradient(180deg, #e6ebf4, #d7dfe8); color:#1f2a37;
      box-shadow:0 8px 18px rgba(18,24,38,.25);
      transition:transform .12s ease, box-shadow .12s ease;
      text-decoration:none; display:inline-flex; align-items:center; gap:8px;
    }
    .btn:hover{ transform: translateY(-1px); box-shadow:0 12px 24px rgba(18,24,38,.32); }
    .btn:active{ transform: translateY(0); }

    .flash{
      margin-bottom:12px;
      padding:10px 12px; border-radius:10px; border:1px solid var(--hair);
      background:rgba(255,255,255,.03);
      font-weight:700;
    }
    .flash.bad{ color:var(--bad); border-color:rgba(255,107,107,.28); background:rgba(255,107,107,.08);}
    .flash.ok{ color:var(--ok); border-color:rgba(34,197,94,.25); background:rgba(34,197,94,.08);}
    .muted-note{ color:var(--muted); font-size:12px; }

    .small{
      font-size:12px; color:var(--muted); margin-top:10px;
    }
  </style>
</head>
<body>
  <header>IoT Telemetry Console · <strong>Sensor_Snitch</strong></header>

  <div class="wrap">
    <!-- LEFT: Live-ish feed + context -->
    <section class="panel">
      <h2>Live Sensor Feed</h2>
      <div class="body">
        <div id="feed" class="feed" aria-label="Sensor output stream"></div>
        <div class="hint">
          Operators reported spurious <em>firmware</em> chatter embedded in UI builds.  
          Real devices shouldn’t log secrets… right?
        </div>
      </div>
    </section>

    <!-- RIGHT: Submit -->
    <aside class="panel">
      <h2>Submit Flag</h2>
      <div class="body">
        <?php if($flash): ?>
          <?php
            $ok = stripos($flash, '✅') !== false || stripos($flash, 'Correct') !== false;
            $cls = $ok ? 'ok' : 'bad';
          ?>
          <div class="flash <?= $cls ?>"><?= $flash ?></div>
        <?php endif; ?>

        <div class="formcard">
          <form method="post" action="score.php" autocomplete="off">
            <label for="flag">Flag</label>
            <input id="flag" name="flag" type="text" placeholder="CTF{...}" required />
            <div class="row">
              <button type="submit" class="btn">Submit</button>
              <a class="btn" href="dashboard.php" title="Back to Dashboard">Back</a>
            </div>
          </form>
          <div class="small">If incorrect, you’ll remain here with an error. If correct, you’ll be redirected to your Dashboard with +120 pts.</div>
        </div>

        <p class="small">
          Field Note: QA embedded a diagnostic “firmware view” into the UI build.
          If you were a mischievous engineer, where would you hide it?
        </p>
      </div>
    </aside>
  </div>

  <script>
    // Simulated live telemetry
    const feed = document.getElementById('feed');
    const lines = [
      '[OK] temp=24.1°C hum=41% batt=93%',
      '[OK] temp=24.1°C hum=41% batt=92%',
      '[WARN] spike in ADC on channel 3 (noise?)',
      '[OK] temp=24.2°C hum=40% batt=92%',
      '[OK] temp=24.3°C hum=40% batt=92%',
      '[NOTE] watchdog=healthy, wifi=-61 dBm',
      '[OK] temp=24.3°C hum=40% batt=91%',
      '[WARN] debug UART chatter detected (masked)',
      '[OK] temp=24.2°C hum=40% batt=91%',
    ];
    let i = 0;
    function tick(){
      if(i < lines.length){
        feed.textContent += lines[i] + '\n';
        feed.scrollTop = feed.scrollHeight;
        i++;
        setTimeout(tick, 500);
      } else {
        // Idle trickle
        feed.textContent += '[OK] temp=24.2°C hum=40% batt=91%\n';
        feed.scrollTop = feed.scrollHeight;
        setTimeout(tick, 1800);
      }
    }
    tick();
  </script>

  <!--
    ────────────────────────────────────────────────────────────────────────────
      FIRMWARE DIAGNOSTIC SNAPSHOT (v1.3.7-qa) — Build embed for repro ONLY
      NOTE: noisy capture; multiple corrupt regions; two valid segments exist.
      Be careful: certain glyphs look ASCII but aren’t 😉
    ────────────────────────────────────────────────────────────────────────────

    Block A  [offset 0x001A0]
    0001A0: 3F 45 12 98 2B C0 7E 10  3A 00 11 09 7C 23 6F 62  ..garbage..
    0001B0: 43 54 46 7B 6C D0 B0 6B  79 5F 73 D0 B5 6E 73 6F  |CTF{l а k y _ s е n s o|
    0001C0: 72 5F 66 69 72 6D 77 61  72 65 7D 9A 1F 00 3E 10  |r_firmware}...........|
    0001D0: 99 00 00 00 22 5C 00 11  86 1A 00 2B 00 00 00 00  ..more garbage....

    Block B  [offset 0x00400]   (corrupted – ignore)
    000400: 00 00 00 FF EE 10 9A 55  2D 7A 33 10 9C 00 31 AA  |~~garbage~~|
    000410: 7F 00 00 00 00 10 10 10  10 10 10 10 10 10 10 10  |~~~~~~~~~~~|

    Block C  [offset 0x00730]   (decoy text, ignore human-readable strings)
    000730: 5B 4E 4F 54 45 5D 20 22  66 69 72 6D 77 61 72 65   [NOTE] "firmware
    000740: 20 64 75 6D 70 22 20 6D  61 73 6B 65 64 00 00 00    dump" masked...

    Block D  [offset 0x00C80]   (garbage)
    000C80: 13 37 00 00 7F 45 4C 46  02 01 01 00 00 00 00 00  |ELF noise...|
    000C90: BE EF FA CE DE AD BE EF  00 00 00 00 00 00 00 00  |..........|

    Block E  [offset 0x01010]  ← VALID (second half is actually here)
    001010: 0F 00 00 00 00 00 00 00  C3 A9 00 00 00 00 00 00  |........|
    001020: 5B 44 45 42 55 47 5D 20  55 41 52 54 20 43 48 41  [DEBUG] UART CHA
    001030: 4E 4E 45 4C 20 45 43 48  4F 20 45 4E 41 42 4C 45  NNEL ECHO ENABLE
    001040: 21 00 00 00 43 54 46 7B  6C D0 B5 61 6B D1 83 5F  !..CTF{l е a k у _
    001050: 73 D0 B5 6E 73 D0 BE 72  5F 66 69 72 6D 77 61 72   s е n s о r _firmwar
    001060: 65 7D 00 00 00 00 00 00  00 00 00 00 00 00 00 00   e}..................

    Human-readable reconstruction (merge A + E, ignore B/C/D):
      CTF{leаkу_sеnsоr_firmwаrе}

    Heads-up:
      • Some letters are Cyrillic look-alikes (e.g., а/е/о/у). They look right, bytewise they’re not.
      • Combine the two good segments; partial decodes won’t produce anything useful.
      • Garbage blocks B/C/D are distractions; ASCII strings there are red herrings.

    ────────────────────────────────────────────────────────────────────────────
    Engineer’s sticky (removed in prod… probably):
      “If it looks right but won’t verify, check your alphabet.”
    ────────────────────────────────────────────────────────────────────────────
  -->
</body>
</html>
