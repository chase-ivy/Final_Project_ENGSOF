<?php
include 'config.php';
include 'oop.php';
$oop = new oopPHP();

//Ajax
if (isset($_GET['search'])) {
    header('Content-Type: application/json');
    $kw = '%' . trim($_GET['search']) . '%';
    $stmt = $connect->prepare("
        SELECT r.room_id, r.room_name, r.price, r.status,
               COUNT(DISTINCT t.tenant_id) AS current_occupants,
               r.max_occupants,
               (SELECT filename FROM room_images ri WHERE ri.room_id=r.room_id ORDER BY ri.sort_order ASC LIMIT 1) AS cover_image
        FROM rooms r
        LEFT JOIN tenants t ON t.room_id=r.room_id AND t.status='Active'
        WHERE r.room_name LIKE :kw OR r.description LIKE :kw OR r.price LIKE :kw
        GROUP BY r.room_id ORDER BY r.status ASC LIMIT 10
    ");
    $stmt->execute([':kw' => $kw]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit();
}

$all       = $oop->get_rooms();
$total     = count($all);
$available = count(array_filter($all, fn($r) => $r['status'] === 'Available'));
$occupied  = $total - $available;
$featured  = array_slice(array_values(array_merge(
    array_filter($all, fn($r) => $r['status'] === 'Available'),
    array_filter($all, fn($r) => $r['status'] !== 'Available')
)), 0, 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>RSYNC — Find Your Room</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">

</head>
<body>

<nav>
    <a class="nav-logo" href="index.php">RS<span>Y</span>NC</a>
    <div class="nav-links"><?php
require_once 'config.php';
require_once 'oop.php';
$oop = new oopPHP();


if (isset($_GET['search'])) {
    header('Content-Type: application/json');
    $kw = '%' . trim($_GET['search']) . '%';
    $stmt = $connect->prepare("
        SELECT r.room_id, r.room_name, r.price, r.status,
               COUNT(DISTINCT t.tenant_id) AS current_occupants,
               r.max_occupants,
               (SELECT filename FROM room_images ri WHERE ri.room_id = r.room_id ORDER BY ri.sort_order ASC LIMIT 1) AS cover_image
        FROM rooms r
        LEFT JOIN tenants t ON t.room_id = r.room_id AND t.status = 'Active'
        WHERE r.room_name LIKE :kw OR r.description LIKE :kw OR r.price LIKE :kw
        GROUP BY r.room_id ORDER BY r.status ASC LIMIT 10
    ");
    $stmt->execute([':kw' => $kw]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit();
}

$all       = $oop->get_rooms();
$total     = count($all);
$available = count(array_filter($all, fn($r) => $r['status'] === 'Available'));
$occupied  = $total - $available;
$featured  = array_slice(array_values(array_merge(
    array_filter($all, fn($r) => $r['status'] === 'Available'),
    array_filter($all, fn($r) => $r['status'] !== 'Available')
)), 0, 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>RSYNC — Find Your Room</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
<style>
:root{--ink:#0a0a0f;--ink2:#3d3d4a;--ink3:#8e8ea0;--bg:#f4f3ef;--amber:#e8a020;--green:#059669;--red:#dc2626;--border:rgba(0,0,0,.08);--r:16px;--sh:0 2px 12px rgba(0,0,0,.06);}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);overflow-x:hidden;}
a{text-decoration:none;}

nav{position:fixed;inset:0 0 auto;z-index:200;display:flex;justify-content:space-between;align-items:center;padding:16px 60px;background:rgba(244,243,239,.88);backdrop-filter:blur(14px);border-bottom:1px solid var(--border);}
.nav-logo{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:var(--ink);letter-spacing:3px;}
.nav-logo span{color:var(--amber);}
.nav-links{display:flex;gap:6px;}
.nav-links a{font-size:14px;font-weight:500;color:var(--ink2);padding:7px 14px;border-radius:9px;transition:.18s;}
.nav-links a:hover{background:rgba(0,0,0,.05);color:var(--ink);}
.nav-links .cta{background:var(--ink);color:#fff;}
.nav-links .cta:hover{background:#1e1e2e;color:#fff;}

.hero{min-height:100vh;display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;padding:120px 20px 80px;position:relative;overflow:hidden;}
.hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(232,160,32,.12),transparent),radial-gradient(ellipse 60% 50% at 80% 80%,rgba(5,150,105,.08),transparent);pointer-events:none;}
.hero-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(0,0,0,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(0,0,0,.025) 1px,transparent 1px);background-size:60px 60px;mask-image:radial-gradient(ellipse 80% 70% at 50% 50%,black 20%,transparent 80%);pointer-events:none;}
.hero h1{font-family:'Syne',sans-serif;font-size:clamp(40px,7vw,84px);font-weight:800;line-height:1.0;letter-spacing:-2px;margin-bottom:18px;animation:fadeUp .6s .1s both;}
.hero h1 em{font-style:normal;color:var(--amber);position:relative;}
.hero h1 em::after{content:'';position:absolute;bottom:4px;left:0;right:0;height:3px;background:var(--amber);border-radius:2px;opacity:.3;}
.hero-sub{font-size:16px;color:var(--ink3);max-width:420px;line-height:1.6;margin-bottom:36px;animation:fadeUp .6s .2s both;}

.search-wrap{width:100%;max-width:540px;position:relative;animation:fadeUp .6s .3s both;}
.search-wrap input{width:100%;padding:17px 52px;font-family:'DM Sans',sans-serif;font-size:15px;border:1.5px solid var(--border);border-radius:14px;background:#fff;outline:none;box-shadow:0 4px 24px rgba(0,0,0,.06);transition:.2s;}
.search-wrap input:focus{border-color:var(--amber);box-shadow:0 0 0 4px rgba(232,160,32,.15);}
.search-wrap .s-icon{position:absolute;left:17px;top:50%;transform:translateY(-50%);font-size:19px;color:var(--ink3);pointer-events:none;}
.search-wrap .s-clear{position:absolute;right:14px;top:50%;transform:translateY(-50%);display:none;background:none;border:none;font-size:18px;color:var(--ink3);cursor:pointer;}
#sr{position:absolute;top:calc(100% + 8px);left:0;right:0;background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:0 20px 48px rgba(0,0,0,.11);display:none;z-index:300;max-height:400px;overflow-y:auto;}
#sr.show{display:block;}
.sr-item{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border);}
.sr-item:last-child{border-bottom:none;}
.sr-item:hover{background:var(--bg);}
.sr-img{width:48px;height:40px;border-radius:8px;object-fit:cover;background:var(--bg);flex-shrink:0;}
.sr-name{font-family:'Syne',sans-serif;font-size:13px;font-weight:700;color:var(--ink);}
.sr-price{font-size:12px;color:var(--ink3);}
.sr-badge{margin-left:auto;font-size:10px;font-weight:600;padding:3px 9px;border-radius:999px;flex-shrink:0;}
.sr-badge.available{background:#d1fae5;color:#065f46;}
.sr-badge.occupied{background:#fee2e2;color:#991b1b;}
.sr-msg{padding:18px;text-align:center;color:var(--ink3);font-size:13px;}

.hero-stats{display:flex;gap:28px;margin-top:44px;animation:fadeUp .6s .4s both;}
.stat{text-align:center;}
.stat-num{font-family:'Syne',sans-serif;font-size:24px;font-weight:800;}
.stat-label{font-size:11px;color:var(--ink3);margin-top:2px;letter-spacing:.4px;}
.stat-divider{width:1px;background:var(--border);align-self:stretch;}


.section{width:90%;max-width:1200px;margin:0 auto;padding:72px 0;}
.sec-head{display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:32px;}
.sec-label{font-size:10px;font-weight:600;letter-spacing:2px;color:var(--amber);text-transform:uppercase;margin-bottom:5px;}
.sec-title{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;letter-spacing:-.5px;}
.sec-link{font-size:13px;color:var(--ink2);}
.sec-link:hover{color:var(--amber);}


.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;}
.card{background:#fff;border:1px solid var(--border);border-radius:var(--r);overflow:hidden;box-shadow:var(--sh);transition:.25s cubic-bezier(.34,1.56,.64,1);}
.card:hover{transform:translateY(-7px);box-shadow:0 18px 44px rgba(0,0,0,.11);}
.c-img{position:relative;height:185px;overflow:hidden;background:#e8e7e3;}
.c-img img{width:100%;height:100%;object-fit:cover;transition:.45s;}
.card:hover .c-img img{transform:scale(1.05);}
.c-img .no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--ink3);font-size:13px;}
.chip{position:absolute;top:10px;right:10px;font-size:10px;font-weight:700;padding:3px 10px;border-radius:999px;backdrop-filter:blur(6px);}
.chip.available{background:rgba(209,250,229,.92);color:#065f46;}
.chip.occupied{background:rgba(254,226,226,.92);color:#991b1b;}
.c-body{padding:16px 18px 18px;}
.c-name{font-family:'Syne',sans-serif;font-size:16px;font-weight:700;margin-bottom:3px;}
.c-desc{font-size:12px;color:var(--ink3);line-height:1.5;margin-bottom:12px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.c-foot{display:flex;align-items:center;justify-content:space-between;margin-top:4px;}
.c-price{font-family:'Syne',sans-serif;font-size:19px;font-weight:800;}
.c-price span{font-size:12px;font-weight:400;color:var(--ink3);}
.occ{font-size:11px;color:var(--ink3);margin-bottom:8px;}
.occ strong{color:var(--ink);}
.occ-bar{height:3px;border-radius:999px;background:rgba(0,0,0,.08);overflow:hidden;margin-top:4px;}
.occ-fill{height:100%;border-radius:999px;background:var(--ink);}
.occ-fill.full{background:var(--red);}
.view-btn{display:inline-flex;align-items:center;gap:5px;padding:8px 14px;background:var(--ink);color:#fff;border-radius:9px;font-size:12px;font-weight:500;transition:.18s;}
.view-btn:hover{background:var(--amber);}

.why{background:var(--ink);padding:72px 0;}
.why-inner{width:90%;max-width:1200px;margin:0 auto;}
.why h2{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:#fff;text-align:center;margin-bottom:36px;}
.why-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px;}
.why-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:var(--r);padding:24px;transition:.2s;}
.why-card:hover{background:rgba(255,255,255,.07);transform:translateY(-4px);}
.why-icon{font-size:22px;color:#fbbf24;margin-bottom:12px;}
.why-card h3{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;color:#fff;margin-bottom:6px;}
.why-card p{font-size:13px;color:rgba(255,255,255,.45);line-height:1.6;}

/* FOOTER */
footer{background:var(--ink);border-top:1px solid rgba(255,255,255,.06);padding:24px 60px;display:flex;justify-content:space-between;align-items:center;}
.foot-logo{font-family:'Syne',sans-serif;font-size:15px;font-weight:800;color:#fff;letter-spacing:2px;}
.foot-logo span{color:var(--amber);}
footer p{font-size:12px;color:rgba(255,255,255,.3);}

@keyframes fadeUp{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:translateY(0);}}
.card:nth-child(1){animation:fadeUp .5s .05s both;}.card:nth-child(2){animation:fadeUp .5s .1s both;}.card:nth-child(3){animation:fadeUp .5s .15s both;}.card:nth-child(4){animation:fadeUp .5s .2s both;}.card:nth-child(5){animation:fadeUp .5s .25s both;}.card:nth-child(6){animation:fadeUp .5s .3s both;}
@media(max-width:768px){nav{padding:13px 20px;}.hero h1{font-size:38px;letter-spacing:-1px;}.section{padding:50px 0;}.why-inner{width:96%;}footer{flex-direction:column;gap:6px;padding:20px;}}
</style>
</head>
<body>

        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms</a>
        <a href="login.php" class="cta">Login</a>
    </div>
</nav>

<div class="hero">
    <div class="hero-grid"></div>
    <h1>Find a room that feels<br>like <em>home</em></h1>
    <p class="hero-sub">Simple, safe, and student-focused boarding house system in Baguio City.</p>

    <div class="search-wrap">
        <i class="bx bx-search s-icon"></i>
        <input type="text" id="si" placeholder="Search by room name, price, description..." autocomplete="off">
        <button class="s-clear" id="sc"><i class="bx bx-x"></i></button>
        <div id="sr"></div>
    </div>

    <div class="hero-stats">
        <div class="stat"><div class="stat-num"><?= $total ?></div><div class="stat-label">Total Rooms</div></div>
        <div class="stat-divider"></div>
        <div class="stat"><div class="stat-num"><?= $available ?></div><div class="stat-label">Available</div></div>
        <div class="stat-divider"></div>
        <div class="stat"><div class="stat-num"><?= $occupied ?></div><div class="stat-label">Occupied</div></div>
    </div>
</div>

<div class="section">
    <div class="sec-head">
        <div>
            <div class="sec-label">Browse</div>
            <div class="sec-title">Featured Rooms</div>
        </div>
        <a href="rooms.php" class="sec-link">View all →</a>
    </div>
    <div class="grid">
    <?php if (empty($featured)): ?>
        <p style="color:var(--ink3);grid-column:1/-1;padding:40px 0">No rooms listed yet.</p>
    <?php else: foreach ($featured as $r):
        $max = (int)($r['max_occupants'] ?? 1);
        $cur = (int)($r['current_occupants'] ?? 0);
        $pct = $max > 0 ? round($cur/$max*100) : 0;
        $cover = !empty($r['cover_image']) ? 'images/'.htmlspecialchars($r['cover_image']) : null;
    ?>
    <div class="card">
        <div class="c-img">
            <?php if ($cover): ?>
                <img src="<?= $cover ?>" alt="<?= htmlspecialchars($r['room_name']) ?>" onerror="this.parentElement.innerHTML='<div class=\'no-img\'>No image</div>'">
            <?php else: ?><div class="no-img">No image</div><?php endif; ?>
            <span class="chip <?= strtolower($r['status']) ?>"><?= $r['status'] ?></span>
        </div>
        <div class="c-body">
            <div class="c-name"><?= htmlspecialchars($r['room_name']) ?></div>
            <div class="occ">Occupants: <strong><?= $cur ?>/<?= $max ?></strong>
                <div class="occ-bar"><div class="occ-fill <?= $cur>=$max?'full':'' ?>" style="width:<?= $pct ?>%"></div></div>
            </div>
            <p class="c-desc"><?= htmlspecialchars($r['description'] ?? '') ?></p>
            <div class="c-foot">
                <div class="c-price">₱<?= number_format($r['price']) ?><span>/mo</span></div>
                <a href="room_detail.php?id=<?= $r['room_id'] ?>" class="view-btn">View <i class="bx bx-right-arrow-alt"></i></a>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
    </div>
</div>

<div class="why">
    <div class="why-inner">
        <h2>Built for easier and convenient way to find your next boarding house!</h2>
        <div class="why-grid">
            <div class="why-card"><div class="why-icon"><i class="bx bx-check-shield"></i></div><h3>Clean Booking System</h3><p>Simple room browsing, no confusion.</p></div>
            <div class="why-card"><div class="why-icon"><i class="bx bx-badge-check"></i></div><h3>Verified Listings</h3><p>All rooms reviewed before going live.</p></div>
            <div class="why-card"><div class="why-icon"><i class="bx bx-home-smile"></i></div><h3>Student Friendly</h3><p>Built for student lifestyle and budget.</p></div>
            <div class="why-card"><div class="why-icon"><i class="bx bx-bolt-circle"></i></div><h3>Real-time Availability</h3><p>Status updates instantly on changes.</p></div>
        </div>
    </div>
</div>

<footer>
    <div class="foot-logo">RS<span>Y</span>NC</div>
    <p>© 2026 RSYNC. All rights reserved.</p>
</footer>

<script>
const si=document.getElementById('si'),sr=document.getElementById('sr'),sc=document.getElementById('sc');
let t;
si.addEventListener('input',function(){
    sc.style.display=this.value?'block':'none';
    clearTimeout(t);
    if(!this.value.trim()){sr.classList.remove('show');sr.innerHTML='';return;}
    t=setTimeout(()=>{
        sr.innerHTML='<div class="sr-msg"><i class="bx bx-loader-alt bx-spin"></i> Searching…</div>';
        sr.classList.add('show');
        fetch('index.php?search='+encodeURIComponent(si.value))
        .then(r=>r.json()).then(data=>{
            if(!data.length){sr.innerHTML='<div class="sr-msg">No rooms found.</div>';return;}
            sr.innerHTML=data.map(r=>`
                <a class="sr-item" href="room_detail.php?id=${r.room_id}">
                    ${r.cover_image?`<img class="sr-img" src="images/${e(r.cover_image)}">`:`<div class="sr-img"></div>`}
                    <div><div class="sr-name">${e(r.room_name)}</div><div class="sr-price">₱${Number(r.price).toLocaleString()} / month · ${e(r.current_occupants)}/${e(r.max_occupants)} occupied</div></div>
                    <span class="sr-badge ${r.status.toLowerCase()}">${e(r.status)}</span>
                </a>`).join('');
        }).catch(()=>{sr.innerHTML='<div class="sr-msg">Error. Try again.</div>';});
    },280);
});
sc.addEventListener('click',()=>{si.value='';sc.style.display='none';sr.classList.remove('show');sr.innerHTML='';si.focus();});
document.addEventListener('click',ev=>{if(!si.contains(ev.target)&&!sr.contains(ev.target))sr.classList.remove('show');});
si.addEventListener('focus',()=>{if(si.value.trim()&&sr.innerHTML)sr.classList.add('show');});
document.addEventListener('keydown',e=>{if(e.key==='Escape')sr.classList.remove('show');});
function e(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
</script>
</body>
</html>
