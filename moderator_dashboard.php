<?php
session_start();
require_once "config.php";
require_once "oop.php";

$oop = new oopPHP();

/* ── AUTH CHECK ── */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'moderator') {
    header("Location: login.php");
    exit();
}//remove this if you want to view the UI only

/* ── DATA ── */
$rooms   = $oop->get_rooms();
$reports = $oop->get_reports();

$totalRooms   = count($rooms);
$available    = count(array_filter($rooms, fn($r) => $r['status'] === 'Available'));
$occupied     = $totalRooms - $available;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Moderator Dashboard · RSYNC</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">

<style>
:root{
    --ink:#0a0a0f;
    --ink2:#3d3d4a;
    --ink3:#8e8ea0;
    --bg:#f4f3ef;
    --card:#fff;
    --amber:#e8a020;
    --green:#059669;
    --red:#dc2626;
    --border:rgba(0,0,0,.08);
    --r:14px;
    --shadow:0 2px 14px rgba(0,0,0,.06);
}

*{margin:0;padding:0;box-sizing:border-box;}

body{
    font-family:'DM Sans',sans-serif;
    background:var(--bg);
    color:var(--ink);
}

/* ───────── NAV ───────── */
nav{
    position:sticky;
    top:0;
    z-index:100;
    background:rgba(244,243,239,.92);
    backdrop-filter:blur(12px);
    border-bottom:1px solid var(--border);
    padding:14px 60px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.logo{
    font-family:'Syne',sans-serif;
    font-weight:800;
    letter-spacing:3px;
}
.logo span{color:var(--amber);}

.nav-links a{
    text-decoration:none;
    color:var(--ink2);
    font-size:13px;
    margin-left:16px;
}
.nav-links a:hover{color:var(--ink);}

/* ───────── WRAP ───────── */
.wrap{
    width:90%;
    max-width:1200px;
    margin:40px auto;
}

/* ───────── HEADER ───────── */
.header h1{
    font-family:'Syne',sans-serif;
    font-size:28px;
    font-weight:800;
}
.header p{
    color:var(--ink3);
    font-size:13px;
    margin-top:4px;
}

/* ───────── STATS ───────── */
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:14px;
    margin:26px 0 30px;
}

.stat{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--r);
    padding:16px;
    box-shadow:var(--shadow);
}

.stat h2{
    font-family:'Syne',sans-serif;
    font-size:24px;
}

.stat p{
    font-size:12px;
    color:var(--ink3);
}

/* ───────── SECTION TITLE ───────── */
.section-title{
    font-family:'Syne',sans-serif;
    font-size:18px;
    margin:26px 0 14px;
}

/* ───────── GRID ───────── */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
    gap:14px;
}

/* ───────── CARD ───────── */
.card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--r);
    box-shadow:var(--shadow);
    overflow:hidden;
}

.card-body{
    padding:14px;
}

.title{
    font-family:'Syne',sans-serif;
    font-size:15px;
    font-weight:800;
}

.meta{
    font-size:12px;
    color:var(--ink3);
    margin-top:6px;
}

/* ───────── BADGES ───────── */
.badge{
    display:inline-block;
    margin-top:10px;
    font-size:11px;
    padding:4px 10px;
    border-radius:999px;
    font-weight:600;
}

.av{background:#dcfce7;color:#166534;}
.oc{background:#fee2e2;color:#991b1b;}
.pending{background:#fef9c3;color:#854d0e;}

/* ───────── BUTTONS ───────── */
.actions{
    display:flex;
    gap:8px;
    margin-top:12px;
}

.btn{
    flex:1;
    text-align:center;
    padding:8px;
    font-size:12px;
    border-radius:10px;
    text-decoration:none;
    font-weight:600;
    border:1px solid var(--border);
}

.primary{
    background:var(--ink);
    color:#fff;
}
.primary:hover{background:#1e1e2e;}

.warn{
    color:var(--ink2);
}
.warn:hover{
    border-color:var(--amber);
    color:var(--ink);
}

/* ───────── REPORT CARD ───────── */
.report{
    border-left:4px solid var(--amber);
}

.report p{
    font-size:13px;
    margin-top:8px;
    color:var(--ink2);
    line-height:1.5;
}

/* ───────── FOOTER ───────── */
footer{
    text-align:center;
    margin:40px 0 20px;
    font-size:12px;
    color:var(--ink3);
}

</style>
</head>
<body>

<!-- NAV -->
<nav>
    <div class="logo">RS<span>Y</span>NC · MODERATOR</div>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="wrap">

    <!-- HEADER -->
    <div class="header">
        <h1>Moderator Control Panel</h1>
        <p>Monitor activity, review reports, and manage platform safety</p>
    </div>

    <!-- STATS -->
    <div class="stats">
        <div class="stat">
            <h2><?= $totalRooms ?></h2>
            <p>Total Rooms</p>
        </div>

        <div class="stat">
            <h2><?= $available ?></h2>
            <p>Available Rooms</p>
        </div>

        <div class="stat">
            <h2><?= $occupied ?></h2>
            <p>Occupied Rooms</p>
        </div>

        <div class="stat">
            <h2><?= count($reports) ?></h2>
            <p>Pending Reports</p>
        </div>
    </div>

    <!-- PRIORITY REPORTS -->
    <div class="section-title">User Reports (Priority Queue)</div>

    <div class="grid">
        <?php if (empty($reports)): ?>
            <p style="color:#8e8ea0;">No reports available.</p>
        <?php else: ?>
            <?php foreach ($reports as $r): ?>
            <div class="card report">
                <div class="card-body">

                    <div class="title"><?= htmlspecialchars($r['subject']) ?></div>

                    <div class="meta">
                        Room: <?= htmlspecialchars($r['room_name']) ?><br>
                        User: <?= htmlspecialchars($r['name']) ?><br>
                        <?= $r['created_at'] ?>
                    </div>

                    <p><?= nl2br(htmlspecialchars($r['message'])) ?></p>

                    <span class="badge <?= $r['status']=='Pending'?'pending':'av' ?>">
                        <?= $r['status'] ?>
                    </span>

                    <div class="actions">
                        <a class="btn warn"
                           href="update_report.php?id=<?= $r['report_id'] ?>&status=Reviewed">
                           Review
                        </a>

                        <a class="btn primary"
                           href="update_report.php?id=<?= $r['report_id'] ?>&status=Resolved">
                           Resolve
                        </a>
                    </div>

                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- LISTINGS -->
    <div class="section-title">Room Listings Review</div>

    <div class="grid">
        <?php foreach ($rooms as $room): ?>
        <div class="card">
            <div class="card-body">

                <div class="title"><?= htmlspecialchars($room['room_name']) ?></div>

                <div class="meta">
                    ₱<?= number_format($room['price']) ?> / month
                </div>

                <span class="badge <?= $room['status']=='Available'?'av':'oc' ?>">
                    <?= $room['status'] ?>
                </span>

                <div class="actions">
                    <a class="btn primary" href="room_detail.php?id=<?= $room['room_id'] ?>">View</a>
                    <a class="btn warn" href="#">Flag</a>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- FUTURE: ACTIVITY MONITORING -->
    <div class="section-title">System Activity (Coming Soon)</div>
    <p style="color:#8e8ea0;font-size:13px;">
        User login tracking, actions logs, and suspicious activity detection will appear here.
    </p>

</div>

<footer>
    © 2026 RSYNC · Moderator System
</footer>

</body>
</html>