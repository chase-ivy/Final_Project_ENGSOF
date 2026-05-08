<?php
session_start();
require_once "config.php";
require_once "oop.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}

$oop = new oopPHP();
$user_id = $_SESSION['user_id'];

/* =========================
   VERIFICATION STATUS
========================= */
$stmt = $connect->prepare("
    SELECT email, email_verified, phone_verified 
    FROM users WHERE user_id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$emailVerified = $user['email_verified'] ?? 0;
$phoneVerified = $user['phone_verified'] ?? 0;

/* =========================
   APPLICATIONS
========================= */
$applications = $oop->get_tenant_applications($user_id);

/* =========================
   BOOKINGS
========================= */
$stmt = $connect->prepare("
    SELECT t.tenant_id, t.room_id, t.start_date, t.end_date, t.status,
           r.room_name, r.price, r.description, r.max_occupants,
           (SELECT filename FROM room_images WHERE room_id=r.room_id ORDER BY sort_order ASC LIMIT 1) AS cover_image,
           COUNT(DISTINCT t2.tenant_id) AS current_occupants
    FROM tenants t
    JOIN rooms r ON t.room_id = r.room_id
    LEFT JOIN tenants t2 ON t2.room_id = r.room_id AND t2.status = 'Active'
    WHERE t.user_id = :uid AND t.status = 'Active'
    GROUP BY t.tenant_id
");
$stmt->execute([":uid" => $user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   MESSAGE
========================= */
$msg = $_SESSION['msg'] ?? null;
unset($_SESSION['msg']);
$isErr = $msg && str_starts_with($msg, 'err:');
if ($isErr) $msg = substr($msg, 4);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tenant Dashboard · RSYNC</title>

<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">

<style>
:root{
--ink:#0a0a0f;--ink3:#8e8ea0;--bg:#f4f3ef;
--amber:#e8a020;--green:#059669;--red:#dc2626;
--border:rgba(0,0,0,.08);--r:14px;--sh:0 2px 12px rgba(0,0,0,.06);
}

*{margin:0;padding:0;box-sizing:border-box;}

body{
font-family:'DM Sans',sans-serif;
background:var(--bg);
color:var(--ink);
min-height:100vh;
display:flex;
flex-direction:column;
}

a{text-decoration:none;}

nav{
background:var(--ink);
display:flex;
justify-content:space-between;
align-items:center;
padding:0 32px;
height:56px;
}

nav span{
font-family:'Syne';
color:#fff;
font-weight:800;
letter-spacing:3px;
}

nav span em{color:var(--amber);font-style:normal;}

nav a{
color:rgba(255,255,255,.65);
padding:6px 14px;
border:1px solid rgba(255,255,255,.2);
border-radius:8px;
font-size:13px;
}

nav a:hover{color:#fff;}

.wrap{width:92%;max-width:1200px;margin:25px auto;flex:1;}

.section-title{
font-family:'Syne';
font-size:16px;
font-weight:800;
margin:20px 0 12px;
}

.toast{
background:#fefce8;
border:1px solid #fde68a;
color:#92400e;
padding:10px 14px;
border-radius:9px;
margin-bottom:15px;
font-size:13px;
}

.toast.err{
background:#fef2f2;
border-color:#fecaca;
color:var(--red);
}

/* TABLE */
.tbl-wrap{
background:#fff;
border-radius:var(--r);
box-shadow:var(--sh);
border:1px solid var(--border);
overflow:auto;
margin-bottom:20px;
}

table{width:100%;border-collapse:collapse;min-width:800px;}

thead th{
background:var(--ink);
color:#fff;
font-size:12px;
padding:10px;
text-align:left;
}

tbody td{
padding:10px;
border-bottom:1px solid var(--border);
}

/* CARDS */
.room-card{
background:#fff;
border-radius:var(--r);
box-shadow:var(--sh);
border:1px solid var(--border);
padding:14px;
margin-bottom:12px;
}

/* VERIFICATION */
.verify-box{
background:#fff;
border-radius:var(--r);
box-shadow:var(--sh);
padding:14px;
border:1px solid var(--border);
margin-bottom:18px;
}

.badge{
padding:3px 8px;
border-radius:999px;
font-size:11px;
font-weight:600;
}

.ok{background:rgba(5,150,105,.15);color:var(--green);}
.no{background:rgba(220,38,38,.1);color:var(--red);}

/* BUTTON */
.btn{
padding:7px 12px;
border:none;
border-radius:8px;
cursor:pointer;
font-size:12px;
}

.btn-dark{background:var(--ink);color:#fff;}
.btn-ghost{background:transparent;border:1px solid var(--border);}

/* MODAL */
.modal{
display:none;
position:fixed;
inset:0;
background:rgba(0,0,0,.4);
justify-content:center;
align-items:center;
}

.modal.show{display:flex;}

.modal-box{
background:#fff;
padding:20px;
border-radius:12px;
width:320px;
}

footer{
background:var(--ink);
color:#fff;
padding:18px;
text-align:center;
margin-top:auto;
}
</style>
</head>

<body>

<nav>
    <span>RS<em>Y</em>NC</span>
    <div>
        <a href="rooms.php">Browse Rooms</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="wrap">

<?php if ($msg): ?>
<div class="toast <?= $isErr?'err':'' ?>">
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<!-- ================= VERIFICATION ================= -->
<div class="verify-box">
    <div class="section-title">Identity Verification</div>

    <p>Email:
        <span class="badge <?= $emailVerified ? 'ok' : 'no' ?>">
            <?= $emailVerified ? 'Verified' : 'Not Verified' ?>
        </span>
        <?php if(!$emailVerified): ?>
            <button class="btn btn-dark" onclick="openOTP('email')">Verify</button>
        <?php endif; ?>
    </p>

    <br>

    <p>Phone:
        <span class="badge <?= $phoneVerified ? 'ok' : 'no' ?>">
            <?= $phoneVerified ? 'Verified' : 'Not Verified' ?>
        </span>
        <?php if(!$phoneVerified): ?>
            <button class="btn btn-dark" onclick="openOTP('phone')">Verify</button>
        <?php endif; ?>
    </p>
</div>

<!-- ================= BOOKINGS ================= -->
<div class="section-title">Active Bookings</div>

<div class="tbl-wrap">
<table>
<thead>
<tr><th>Room</th><th>Price</th><th>Status</th></tr>
</thead>
<tbody>
<?php foreach ($bookings as $b): ?>
<tr>
<td><?= htmlspecialchars($b['room_name']) ?></td>
<td>₱<?= number_format($b['price']) ?></td>
<td>Active</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- ================= APPLICATIONS ================= -->
<div class="section-title">Applications</div>

<?php foreach ($applications as $app): ?>
<div class="room-card">
    <strong><?= htmlspecialchars($app['room_name']) ?></strong><br>
    Status: <?= $app['status'] ?>
</div>
<?php endforeach; ?>

</div>

<!-- ================= OTP MODAL ================= -->
<div class="modal" id="otpModal">
<div class="modal-box">
    <h3>Enter OTP</h3>
    <input type="text" id="otp" placeholder="6-digit code" style="width:100%;padding:8px;margin:10px 0;">
    <button class="btn btn-dark" onclick="verifyOTP()">Verify</button>
    <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
</div>
</div>

<footer>
RSYNC © 2026
</footer>

<script>
let type = "";

function openOTP(t){
    type = t;
    document.getElementById('otpModal').classList.add('show');
}

function closeModal(){
    document.getElementById('otpModal').classList.remove('show');
}

function verifyOTP(){
    let code = document.getElementById('otp').value;

    fetch("",{
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"verify_otp=1&type="+type+"&code="+code
    })
    .then(r=>r.json())
    .then(res=>{
        alert(res.message || "Done");
        location.reload();
    });
}
</script>

</body>
</html>