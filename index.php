<?php
include 'config.php';
include 'oop.php';
$oop = new oopPHP();

// POST: Handle room applications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_for_room'])) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "You must be logged in"]);
        exit();
    }
    if ($_SESSION['role'] !== 'tenant') {
        echo json_encode(["status" => "error", "message" => "Only tenants can apply"]);
        exit();
    }
    $result = $oop->apply_for_room($_SESSION['user_id'], $_POST['room_id']);
    echo json_encode($result);
    exit();
}

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
<style>
:root{--ink:#0a0a0f;--ink2:#3d3d4a;--ink3:#8e8ea0;--bg:#f4f3ef;--surface:#fff;--surface-soft:#faf9f6;--amber:#e8a020;--green:#059669;--red:#dc2626;--border:rgba(0,0,0,.08);--border-strong:rgba(0,0,0,.12);--r:16px;--sh:0 2px 12px rgba(0,0,0,.06);--shadow-md:0 10px 24px rgba(0,0,0,.06);--transition-base:180ms ease;}
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

.why{background:var(--ink);padding:72px 0;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw;width:100vw;}
.why-inner{max-width:1200px;margin:0 auto;padding:0 24px;box-sizing:border-box;}
.why h2{font-family:'Syne',sans-serif;font-size:28px;font-weight:800;color:#fff;text-align:center;margin-bottom:36px;}
.why-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px;}
.why-card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:var(--r);padding:24px;transition:.2s;}
.why-card:hover{background:rgba(255,255,255,.07);transform:translateY(-4px);}
.why-icon{font-size:22px;color:#fbbf24;margin-bottom:12px;}
.why-card h3{font-family:'Syne',sans-serif;font-size:15px;font-weight:700;color:#fff;margin-bottom:6px;}
.why-card p{font-size:13px;color:rgba(255,255,255,.45);line-height:1.6;}

/* FOOTER */
footer{background:var(--ink);border-top:1px solid rgba(255,255,255,.06);padding:24px 60px;display:flex;justify-content:space-between;align-items:center;position:relative;left:50%;right:50%;margin-left:-50vw;margin-right:-50vw;width:100vw;box-sizing:border-box;}
.foot-logo{font-family:'Syne',sans-serif;font-size:15px;font-weight:800;color:#fff;letter-spacing:2px;}
.foot-logo span{color:var(--amber);}
footer p{font-size:12px;color:rgba(255,255,255,.3);}

@keyframes fadeUp{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:translateY(0);}}
.card:nth-child(1){animation:fadeUp .5s .05s both;}.card:nth-child(2){animation:fadeUp .5s .1s both;}.card:nth-child(3){animation:fadeUp .5s .15s both;}.card:nth-child(4){animation:fadeUp .5s .2s both;}.card:nth-child(5){animation:fadeUp .5s .25s both;}.card:nth-child(6){animation:fadeUp .5s .3s both;}

/* ═══ PRICING SECTION ═══ */
.pricing-container{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;margin-top:40px;}
.pricing-card{position:relative;background:#fff;border-radius:var(--r);padding:40px 28px;border:2px solid var(--border);transition:.3s ease;box-shadow:var(--sh);display:flex;flex-direction:column;}
.pricing-card:hover{transform:translateY(-8px);border-color:var(--amber);box-shadow:0 16px 40px rgba(232,160,32,.15);}
.pricing-card.featured-card{border-color:var(--amber);background:linear-gradient(135deg,#fff9f0 0%,#fff 100%);}
.pricing-card.featured-card:hover{box-shadow:0 20px 50px rgba(232,160,32,.25);}
.pricing-badge{position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:var(--amber);color:#fff;padding:6px 14px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
.pricing-header{margin-bottom:20px;}
.pricing-header h3{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;color:var(--ink);margin-bottom:6px;}
.pricing-desc{font-size:13px;color:var(--ink3);}
.pricing-price{display:flex;align-items:baseline;gap:4px;margin:28px 0;padding:20px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);}
.price-currency{font-size:18px;color:var(--amber);font-weight:700;}
.price-amount{font-family:'Syne',sans-serif;font-size:42px;font-weight:800;color:var(--ink);}
.price-period{font-size:13px;color:var(--ink3);margin-left:auto;}
.pricing-features{flex:1;margin-bottom:24px;}
.feature{display:flex;align-items:center;gap:10px;margin-bottom:14px;font-size:13px;color:var(--ink2);}
.feature i{font-size:18px;color:var(--green);flex-shrink:0;}
.feature.disabled{opacity:.5;color:var(--ink3);}
.feature.disabled i{color:var(--red);}
.trial-btn{width:100%;padding:12px 16px;border:2px solid var(--ink);background:transparent;color:var(--ink);font-family:'DM Sans',sans-serif;font-size:14px;font-weight:600;border-radius:10px;cursor:pointer;transition:.25s;letter-spacing:.3px;text-transform:uppercase;}
.trial-btn:hover{background:var(--ink);color:#fff;}
.trial-btn.featured{border-color:var(--amber);color:var(--amber);}
.trial-btn.featured:hover{background:var(--amber);color:#fff;}

@media(max-width:768px){nav{padding:13px 20px;}.hero h1{font-size:38px;letter-spacing:-1px;}.section{padding:50px 0;}.why-inner{width:96%;}.pricing-card{padding:28px 20px;}.pricing-price{flex-wrap:wrap;}.price-period{margin-left:0;width:100%;margin-top:8px;}footer{flex-direction:column;gap:6px;padding:20px;}}

/* ═══ PLANS ═══ */
.billing-toggle{display:inline-flex;gap:4px;background:rgba(0,0,0,.06);border-radius:9999px;padding:4px;margin-bottom:32px;box-shadow:inset 0 0 0 1px rgba(0,0,0,.04);}
.tgl{font-size:13px;font-weight:600;padding:10px 18px;border-radius:9999px;border:none;background:transparent;color:var(--ink2);cursor:pointer;transition:var(--transition-base);}
.tgl.active{background:#fff;color:var(--ink);box-shadow:0 4px 14px rgba(0,0,0,.08);}
.save-tag{font-size:10px;background:#fef3c7;color:#92400e;padding:2px 7px;border-radius:999px;margin-left:6px;}
.plans-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;align-items:stretch;}
.plan-card{background:var(--surface);border:1px solid var(--border-strong);border-radius:22px;padding:28px;position:relative;display:flex;flex-direction:column;transition:transform var(--transition-base),box-shadow var(--transition-base),border-color var(--transition-base);box-shadow:var(--shadow-md);}
.plan-card:hover{transform:translateY(-4px);box-shadow:0 18px 36px rgba(0,0,0,.08);border-color:rgba(0,0,0,.16);}
.plan-card.featured-plan{border-color:rgba(232,160,32,.35);background:linear-gradient(180deg,rgba(255,245,224,.95) 0%,var(--surface) 100%);}
.plan-card-top{display:flex;align-items:center;gap:14px;margin-bottom:18px;}
.plan-icon-wrap{width:44px;height:44px;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:20px;}
.plan-icon-wrap.starter{background:#ecfdf5;color:#047857;}
.plan-icon-wrap.pro{background:#fffbeb;color:#b45309;}
.plan-icon-wrap.ent{background:#eff6ff;color:#1d4ed8;}
.plan-name{font-family:'Syne',sans-serif;font-size:18px;font-weight:800;margin-bottom:4px;}
.plan-desc{font-size:13px;color:var(--ink3);margin-bottom:20px;line-height:1.6;}
.plan-price-row{display:flex;align-items:flex-end;gap:4px;margin-bottom:8px;}
.plan-curr{font-size:16px;color:var(--ink3);}
.plan-amt{font-family:'Syne',sans-serif;font-size:40px;font-weight:800;color:var(--ink);}
.plan-per{font-size:12px;color:var(--ink3);margin-bottom:2px;}
.plan-orig{font-size:11px;color:var(--ink3);min-height:18px;margin-bottom:18px;}
.plan-divider{border:none;border-top:1px solid var(--border-strong);margin:18px 0;}
.plan-feats{list-style:none;display:flex;flex-direction:column;gap:12px;margin-bottom:22px;padding:0;}
.pf{display:flex;align-items:flex-start;gap:10px;font-size:13px;color:var(--ink2);line-height:1.6;}
.pf i{font-size:16px;flex-shrink:0;margin-top:2px;color:var(--green);}
.pf.off{color:var(--ink3);}
.pf.off i{color:var(--ink3);}
.pf.featured-feat i{color:var(--amber);}
.feat-tag{font-size:9px;padding:1px 7px;border-radius:999px;font-weight:600;margin-left:4px;background:#fff7ed;color:#c2410c;}
.plan-btn{width:100%;padding:14px;border-radius:12px;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:700;cursor:pointer;background:transparent;color:var(--ink);border:1.5px solid rgba(0,0,0,.16);transition:var(--transition-base);}
.plan-btn:hover{background:rgba(0,0,0,.04);}
.plan-btn.amber-btn{background:var(--amber);color:#fff;border-color:var(--amber);}
.plan-btn.amber-btn:hover{background:#d08a14;}
.plan-note{font-size:14px;color:var(--ink3);max-width:560px;margin-top:6px;}
.plan-hero{display:flex;justify-content:space-between;flex-wrap:wrap;gap:22px;align-items:center;margin-bottom:32px;}
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
                <div style="display:flex;gap:6px;">
                    <a href="room_detail.php?id=<?= $r['room_id'] ?>" class="view-btn">View <i class="bx bx-right-arrow-alt"></i></a>
                    <button onclick="applyForRoom(<?= $r['room_id'] ?>, '<?= addslashes(htmlspecialchars($r['room_name'])) ?>')" class="view-btn" style="background:var(--amber);border:none;cursor:pointer;">Apply</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
    </div>

<!-- ═══ SUBSCRIPTION PLANS SECTION ═══ -->
<div class="section" id="plans">
    <div class="plan-hero">
        <div>
            <div class="sec-label">For Landlords</div>
            <div class="sec-title" style="margin-bottom:6px;">Simple, transparent pricing</div>
            <p class="plan-note">List your rooms, manage tenants, and grow your boarding house business.</p>
        </div>
        <div class="billing-toggle" role="tablist" aria-label="Billing period">
            <button type="button" class="tgl active" id="btn-m" onclick="setBilling('monthly')">Monthly</button>
            <button type="button" class="tgl" id="btn-a" onclick="setBilling('annual')">Annual <span class="save-tag">Save 20%</span></button>
        </div>
    </div>

    <div class="plans-grid">
        <div class="plan-card plan-starter">
            <div class="plan-card-top">
                <div class="plan-icon-wrap starter"><i class='bx bx-leaf'></i></div>
                <div>
                    <h3 class="plan-name">Starter</h3>
                    <p class="plan-desc">A lean option for first-time landlords.</p>
                </div>
            </div>
            <div class="plan-price-row">
                <span class="plan-curr">₱</span>
                <span class="plan-amt" id="p-s">0</span>
                <span class="plan-per">/mo</span>
            </div>
            <div class="plan-orig" id="o-s">&nbsp;</div>
            <hr class="plan-divider">
            <ul class="plan-feats">
                <li class="pf"><i class='bx bx-check'></i><span><strong>1 room listing</strong></span></li>
                <li class="pf"><i class='bx bx-check'></i><span>Basic tenant management</span></li>
                <li class="pf off"><i class='bx bx-minus'></i><span>Payment tracking</span></li>
                <li class="pf off"><i class='bx bx-minus'></i><span>Email support</span></li>
                <li class="pf off"><i class='bx bx-minus'></i><span>Featured listings</span></li>
            </ul>
            <button class="plan-btn" onclick="selectPlan('Starter')">Choose Starter</button>
        </div>

        <div class="plan-card featured-plan">
            <div class="plan-card-top">
                <div class="plan-icon-wrap pro"><i class='bx bx-store'></i></div>
                <div>
                    <h3 class="plan-name">Professional</h3>
                    <p class="plan-desc">For growing portfolios and daily operations.</p>
                </div>
            </div>
            <div class="plan-price-row">
                <span class="plan-curr">₱</span>
                <span class="plan-amt" id="p-p">399</span>
                <span class="plan-per">/mo</span>
            </div>
            <div class="plan-orig" id="o-p">&nbsp;</div>
            <hr class="plan-divider">
            <ul class="plan-feats">
                <li class="pf"><i class='bx bx-check'></i><span>Up to <strong>10 room listings</strong></span></li>
                <li class="pf"><i class='bx bx-check'></i><span>Advanced tenant management</span></li>
                <li class="pf"><i class='bx bx-check'></i><span>Transaction records & reports</span></li>
                <li class="pf"><i class='bx bx-check'></i><span>Priority email & chat support</span></li>
                <li class="pf featured-feat"><i class='bx bx-star'></i><span><strong>3 featured listings</strong>/month <span class="feat-tag hot">Hot</span></span></li>
            </ul>
            <button class="plan-btn amber-btn" onclick="selectPlan('Professional')">Choose Professional</button>
        </div>

        <div class="plan-card">
            <div class="plan-card-top">
                <div class="plan-icon-wrap ent"><i class='bx bxs-crown'></i></div>
                <div>
                    <h3 class="plan-name">Enterprise</h3>
                    <p class="plan-desc">For premium landlords with large portfolios.</p>
                </div>
            </div>
            <div class="plan-price-row">
                <span class="plan-curr">₱</span>
                <span class="plan-amt" id="p-e">699</span>
                <span class="plan-per">/mo</span>
            </div>
            <div class="plan-orig" id="o-e">&nbsp;</div>
            <hr class="plan-divider">
            <ul class="plan-feats">
                <li class="pf"><i class='bx bx-check'></i><span><strong>Unlimited</strong> room listings</span></li>
                <li class="pf"><i class='bx bx-check'></i><span>Full tenant management suite</span></li>
                <li class="pf"><i class='bx bx-check'></i><span>Advanced analytics & reports</span></li>
                <li class="pf"><i class='bx bx-check'></i><span>24/7 phone & priority support</span></li>
                <li class="pf featured-feat"><i class='bx bx-star'></i><span><strong>All listings always featured</strong></span></li>
                <li class="pf"><i class='bx bx-check'></i><span>Custom branding on listings</span></li>
            </ul>
            <button class="plan-btn" onclick="selectPlan('Enterprise')">Choose Enterprise</button>
        </div>
    </div>
    <p style="text-align:center;font-size:12px;color:var(--ink3);margin-top:20px;">All plans include a 14-day free trial. No credit card required.</p>
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

function selectPlan(plan){
  window.location.href = 'registration.php?role=landlord&plan=' + encodeURIComponent(plan);
}

function applyForRoom(roomId, roomName){
  // Check if user is logged in
  if(!<?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>){
    if(confirm('You need to log in to apply for a room.\n\nWould you like to log in or register?')){
      window.location.href = 'login.php';
    }
    return;
  }
  
  if(<?= isset($_SESSION['user_id']) && $_SESSION['role'] === 'tenant' ? 'false' : 'true' ?>){
    alert('Only tenants can apply for rooms.');
    return;
  }

  if(confirm('Apply for ' + roomName + '?')){
    fetch('', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: 'apply_for_room=1&room_id=' + roomId
    })
    .then(r => r.json())
    .then(data => {
      if(data.status === 'success'){
        alert('Application submitted successfully! You will be notified once the landlord reviews your application.');
        window.location.href = 'tenant_dashboard.php';
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(err => alert('An error occurred. Please try again.'));
  }
}

const mPrices={s:0,p:299,e:699};
const aPrices={s:0,p:239,e:559};
function setBilling(mode){
  document.getElementById('btn-m').classList.toggle('active',mode==='monthly');
  document.getElementById('btn-a').classList.toggle('active',mode==='annual');
  const d = mode==='annual'?aPrices:mPrices;
  document.getElementById('p-s').textContent = d.s===0 ? '0' : d.s.toLocaleString();
  document.getElementById('p-p').textContent = d.p.toLocaleString();
  document.getElementById('p-e').textContent = d.e.toLocaleString();
  document.getElementById('o-p').innerHTML = mode==='annual' ? '₱' + mPrices.p.toLocaleString() + '/mo billed monthly' : '&nbsp;';
  document.getElementById('o-e').innerHTML = mode==='annual' ? '₱' + mPrices.e.toLocaleString() + '/mo billed monthly' : '&nbsp;';
}
</script>
</body>
</html>
