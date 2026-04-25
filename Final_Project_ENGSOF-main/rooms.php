<?php
require_once 'config.php';
require_once "oop.php";
$oop = new oopPHP();
$search = isset($_GET['name']) ? trim($_GET['name']) : "";
$rooms  = $search ? $oop->get_room($search) : $oop->get_rooms();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rooms · RSYNC</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
<style>
:root{--ink:#0a0a0f;--ink2:#3d3d4a;--ink3:#8e8ea0;--bg:#f4f3ef;--amber:#e8a020;--red:#dc2626;--border:rgba(0,0,0,.08);--r:16px;--sh:0 2px 12px rgba(0,0,0,.06);}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);}
a{text-decoration:none;}
nav{position:sticky;top:0;z-index:99;display:flex;justify-content:space-between;align-items:center;padding:16px 60px;background:rgba(244,243,239,.9);backdrop-filter:blur(14px);border-bottom:1px solid var(--border);}
.nav-logo{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:var(--ink);letter-spacing:3px;}
.nav-logo span{color:var(--amber);}
.nav-links{display:flex;gap:6px;}
.nav-links a{font-size:14px;font-weight:500;color:var(--ink2);padding:7px 14px;border-radius:9px;transition:.18s;}
.nav-links a:hover{background:rgba(0,0,0,.05);color:var(--ink);}
.nav-links .cta{background:var(--ink);color:#fff;}
.nav-links .cta:hover{background:#1e1e2e;color:#fff;}

.page-hero{
    padding:52px 60px 36px;
    border-bottom:1px solid var(--border);
    background:var(--bg);

    text-align:center;        /* centers text */
    display:flex;             /* centers all content cleanly */
    flex-direction:column;
    align-items:center;
}
.page-hero h1{font-family:'Syne',sans-serif;font-size:32px;font-weight:800;letter-spacing:-.5px;}
.page-hero p{color:var(--ink3);font-size:14px;margin-top:4px;}
.search-bar{
    margin:18px auto 0;   /* centers horizontally */
    position:relative;
    max-width:440px;
    width:100%;           /* ensures proper centering */
}.search-bar input{width:100%;padding:11px 14px 11px 38px;border:1.5px solid var(--border);border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;background:#fff;outline:none;transition:.2s;}
.search-bar input:focus{border-color:var(--amber);}
.search-bar i{position:absolute;left:11px;top:50%;transform:translateY(-50%);font-size:17px;color:var(--ink3);pointer-events:none;}

main{width:90%;max-width:1280px;margin:36px auto 64px;}
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
.c-desc{font-size:12px;color:var(--ink3);line-height:1.5;margin-bottom:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.occ{font-size:11px;color:var(--ink3);margin-bottom:10px;}
.occ strong{color:var(--ink);}
.occ-bar{height:3px;border-radius:999px;background:rgba(0,0,0,.08);overflow:hidden;margin-top:4px;}
.occ-fill{height:100%;border-radius:999px;background:var(--ink);}
.occ-fill.full{background:var(--red);}
.c-foot{display:flex;align-items:center;justify-content:space-between;}
.c-price{font-family:'Syne',sans-serif;font-size:18px;font-weight:800;}
.c-price span{font-size:12px;font-weight:400;color:var(--ink3);}
.view-btn{display:inline-flex;align-items:center;gap:5px;padding:8px 14px;background:var(--ink);color:#fff;border-radius:9px;font-size:12px;font-weight:500;transition:.18s;}
.view-btn:hover{background:var(--amber);}
.empty{grid-column:1/-1;text-align:center;padding:56px 20px;color:var(--ink3);font-size:14px;}

footer{background:var(--ink);border-top:1px solid rgba(255,255,255,.06);padding:22px 60px;display:flex;justify-content:space-between;align-items:center;}
.foot-logo{font-family:'Syne',sans-serif;font-size:15px;font-weight:800;color:#fff;letter-spacing:2px;}
.foot-logo span{color:var(--amber);}
footer p{font-size:12px;color:rgba(255,255,255,.3);}
@media(max-width:768px){nav{padding:13px 20px;}.page-hero{padding:32px 20px 24px;}main{width:96%;}footer{flex-direction:column;gap:6px;padding:18px;}}
</style>
</head>
<body>
<nav>
    <a class="nav-logo" href="index.php">RS<span>Y</span>NC</a>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms</a>
        <a href="login.php" class="cta">Login</a>
    </div>
</nav>

<div class="page-hero">
    <h1>All Rooms</h1>
    <p>Boarding houses available in Baguio City</p>
    <div class="search-bar">
        <i class="bx bx-search"></i>
        <input type="text" id="q" placeholder="Search rooms…" value="<?= htmlspecialchars($search) ?>">
    </div>
</div>

<main>
<div class="grid">
<?php if (!empty($rooms)): foreach ($rooms as $row):
    $max = (int)($row['max_occupants'] ?? 1);
    $cur = (int)($row['current_occupants'] ?? 0);
    $pct = $max > 0 ? round($cur/$max*100) : 0;
    $cover = !empty($row['cover_image']) ? 'images/'.htmlspecialchars($row['cover_image']) : null;
?>
<div class="card" data-n="<?= strtolower(htmlspecialchars($row['room_name'])) ?>" data-s="<?= strtolower($row['status']) ?>" data-p="<?= $row['price'] ?>">
    <div class="c-img">
        <?php if ($cover): ?><img src="<?= $cover ?>" alt="<?= htmlspecialchars($row['room_name']) ?>" onerror="this.parentElement.innerHTML='<div class=\'no-img\'>No image</div>'"><?php else: ?><div class="no-img">No image</div><?php endif; ?>
        <span class="chip <?= strtolower($row['status']) ?>"><?= $row['status'] ?></span>
    </div>
    <div class="c-body">
        <div class="c-name"><?= htmlspecialchars($row['room_name']) ?></div>
        <div class="occ">Occupants: <strong><?= $cur ?>/<?= $max ?></strong>
            <div class="occ-bar"><div class="occ-fill <?= $cur>=$max?'full':'' ?>" style="width:<?= $pct ?>%"></div></div>
        </div>
        <p class="c-desc"><?= htmlspecialchars($row['description']) ?></p>
        <div class="c-foot">
            <div class="c-price">₱<?= number_format($row['price']) ?><span>/mo</span></div>
            <a href="room_detail.php?id=<?= $row['room_id'] ?>" class="view-btn">View <i class="bx bx-right-arrow-alt"></i></a>
        </div>
    </div>
</div>
<?php endforeach; else: ?><div class="empty">No rooms found.</div><?php endif; ?>
</div>
</main>

<footer>
    <div class="foot-logo">RS<span>Y</span>NC</div>
    <p>© 2026 RSYNC. All rights reserved.</p>
</footer>

<script>
document.getElementById('q').addEventListener('input',function(){
    const q=this.value.toLowerCase();
    document.querySelectorAll('.card').forEach(c=>{
        c.style.display=(c.dataset.n+c.dataset.s+c.dataset.p).includes(q)?'':'none';
    });
});
</script>
</body>
</html>
