<?php
require_once 'config.php';
require_once 'oop.php';
$oop = new oopPHP();

// Handle application submission via POST
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

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$room = $oop->get_room_by_id($id);

if (!$room) {
    header("Location: rooms.php"); exit();
}

$images  = $oop->get_room_images($id);
$max     = (int)($room['max_occupants'] ?? 1);
$current = (int)($room['current_occupants'] ?? 0);
$pct     = $max > 0 ? round(($current / $max) * 100) : 0;
$full    = $current >= $max;
$cover   = !empty($images) ? 'images/' . htmlspecialchars($images[0]['filename']) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($room['room_name']) ?> · RSYNC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
:root {
    --bg:#f5f5f0; --card:#fff; --ink:#111118; --muted:#6b7280; --border:#e5e7eb;
    --green:#16a34a; --red:#dc2626; --radius:12px; --shadow:0 2px 12px rgba(0,0,0,.06);
}
body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--ink); }

nav { position:sticky; top:0; z-index:99; display:flex; align-items:center; justify-content:space-between; padding:0 60px; height:56px; background:#fff; border-bottom:1px solid var(--border); }
.logo { font-weight:800; font-size:17px; color:var(--ink); letter-spacing:.5px; }
.nav-links { display:flex; gap:24px; }
.nav-links a { text-decoration:none; color:var(--muted); font-size:14px; font-weight:500; }
.nav-links a:hover { color:var(--ink); }

.wrap { width:90%; max-width:1100px; margin:36px auto 60px; }

.back { display:inline-flex; align-items:center; gap:6px; font-size:13px; color:var(--muted); text-decoration:none; margin-bottom:24px; }
.back:hover { color:var(--ink); }

.layout { display:grid; grid-template-columns:1fr 360px; gap:32px; }

/* GALLERY */
.gallery-main { border-radius:var(--radius); overflow:hidden; background:#f1f0eb; aspect-ratio:16/9; }
.gallery-main img { width:100%; height:100%; object-fit:cover; display:block; cursor:pointer; }
.gallery-main .no-img { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--muted); font-size:14px; }

.gallery-thumbs { display:flex; gap:8px; margin-top:10px; flex-wrap:wrap; }
.gallery-thumbs img { width:72px; height:56px; object-fit:cover; border-radius:8px; cursor:pointer; border:2px solid transparent; opacity:.7; transition:.15s; }
.gallery-thumbs img.active, .gallery-thumbs img:hover { border-color:var(--ink); opacity:1; }

/* SIDEBAR INFO */
.info-card { background:var(--card); border-radius:var(--radius); border:1px solid var(--border); box-shadow:var(--shadow); padding:24px; }
.room-title { font-size:22px; font-weight:800; margin-bottom:6px; }
.room-price { font-size:28px; font-weight:800; color:var(--ink); }
.room-price span { font-size:14px; font-weight:400; color:var(--muted); }

.divider { height:1px; background:var(--border); margin:18px 0; }

.info-row { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; font-size:13px; }
.info-row .label { color:var(--muted); }
.info-row .val { font-weight:600; }

.badge { display:inline-block; padding:4px 12px; border-radius:999px; font-size:12px; font-weight:600; }
.badge-av { background:#f0fdf4; color:var(--green); border:1px solid #bbf7d0; }
.badge-oc { background:#fff1f2; color:var(--red);   border:1px solid #fecdd3; }

.slots-block { margin:16px 0; }
.slots-label { font-size:12px; color:var(--muted); margin-bottom:6px; }
.slots-label strong { color:var(--ink); font-size:14px; }
.slots-bar { height:6px; border-radius:999px; background:var(--border); overflow:hidden; }
.slots-fill { height:100%; border-radius:999px; background:var(--ink); }
.slots-fill.full { background:var(--red); }

.desc-block { margin-top:16px; }
.desc-block h4 { font-size:13px; font-weight:600; color:var(--muted); margin-bottom:8px; text-transform:uppercase; letter-spacing:.3px; }
.desc-block p { font-size:14px; line-height:1.7; color:var(--ink); }

.cta-btn { display:block; width:100%; margin-top:20px; padding:13px; text-align:center; background:var(--ink); color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; text-decoration:none; transition:.2s; }
.cta-btn:hover { background:#2d2d3a; }

/* LIGHTBOX */
.lightbox { display:none; position:fixed; inset:0; background:rgba(0,0,0,.88); z-index:9999; justify-content:center; align-items:center; }
.lightbox.show { display:flex; }
.lightbox img { max-width:92vw; max-height:88vh; border-radius:10px; object-fit:contain; }
.lightbox-close { position:absolute; top:20px; right:24px; font-size:28px; color:#fff; cursor:pointer; background:none; border:none; line-height:1; }
.lightbox-prev, .lightbox-next { position:absolute; top:50%; transform:translateY(-50%); font-size:28px; color:#fff; background:rgba(255,255,255,.1); border:none; cursor:pointer; padding:10px 14px; border-radius:10px; }
.lightbox-prev { left:20px; }
.lightbox-next { right:20px; }

footer { text-align:center; padding:24px; color:var(--muted); font-size:12px; border-top:1px solid var(--border); }
@media(max-width:768px) {
    nav{padding:0 20px;} .wrap{width:96%;}
    .layout{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<nav>
    <div class="logo">RSYNC</div>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms</a>
        <a href="login.php">Login</a>
    </div>
</nav>

<div class="wrap">
    <a href="rooms.php" class="back">← Back to Rooms</a>

    <div class="layout">

        <!-- LEFT: GALLERY -->
        <div>
            <div class="gallery-main" id="mainImg">
                <?php if ($cover): ?>
                    <img src="<?= $cover ?>" id="mainImgEl" alt="<?= htmlspecialchars($room['room_name']) ?>" onclick="openLightbox(0)" onerror="this.parentElement.innerHTML='<div class=\'no-img\'>No image available</div>'">
                <?php else: ?>
                    <div class="no-img">No images uploaded</div>
                <?php endif; ?>
            </div>

            <?php if (count($images) > 1): ?>
            <div class="gallery-thumbs" id="thumbs">
                <?php foreach ($images as $i => $img): ?>
                    <img src="images/<?= htmlspecialchars($img['filename']) ?>"
                         class="<?= $i===0?'active':'' ?>"
                         onclick="switchImg(this,'images/<?= htmlspecialchars($img['filename']) ?>',<?= $i ?>)"
                         onerror="this.style.display='none'">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: INFO -->
        <div>
            <div class="info-card">
                <div class="room-title"><?= htmlspecialchars($room['room_name']) ?></div>
                <div class="room-price">₱<?= number_format($room['price']) ?><span> / month</span></div>

                <div class="divider"></div>

                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="badge <?= $room['status']==='Available'?'badge-av':'badge-oc' ?>"><?= htmlspecialchars($room['status']) ?></span>
                </div>

                <div class="slots-block">
                    <div class="slots-label">Occupants: <strong><?= $current ?> / <?= $max ?></strong></div>
                    <div class="slots-bar">
                        <div class="slots-fill <?= $full?'full':'' ?>" style="width:<?= $pct ?>%"></div>
                    </div>
                </div>

                <div class="info-row">
                    <span class="label">Max occupants</span>
                    <span class="val"><?= $max ?> person<?= $max>1?'s':'' ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Slots available</span>
                    <span class="val" style="color:<?= $full?'var(--red)':'var(--green)' ?>"><?= max(0,$max-$current) ?> open</span>
                </div>

                <?php if (!empty($room['description'])): ?>
                <div class="desc-block">
                    <h4>About this room</h4>
                    <p><?= nl2br(htmlspecialchars($room['description'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'tenant'): ?>
                    <?php if (!$full): ?>
                        <button class="cta-btn" onclick="applyForRoom(<?= $id ?>, '<?= addslashes(htmlspecialchars($room['room_name'])) ?>')">Apply Now</button>
                    <?php else: ?>
                        <button class="cta-btn" style="background:#9ca3af;cursor:not-allowed" disabled>Room Full</button>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="login.php" class="cta-btn">Login to Apply</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    <button class="lightbox-prev"  onclick="lbNav(-1)">&#8249;</button>
    <img id="lbImg" src="" alt="">
    <button class="lightbox-next"  onclick="lbNav(1)">&#8250;</button>
</div>

<footer>© 2026 RSYNC · Baguio City</footer>

<script>
const allImages = [<?php foreach ($images as $img) echo '"images/' . htmlspecialchars($img['filename'], ENT_QUOTES) . '",'; ?>];
let lbIndex = 0;

function switchImg(thumb, src, idx) {
    document.getElementById('mainImgEl').src = src;
    document.querySelectorAll('.gallery-thumbs img').forEach(i => i.classList.remove('active'));
    thumb.classList.add('active');
    lbIndex = idx;
}

function openLightbox(idx) {
    lbIndex = idx;
    document.getElementById('lbImg').src = allImages[lbIndex];
    document.getElementById('lightbox').classList.add('show');
}

function closeLightbox() { document.getElementById('lightbox').classList.remove('show'); }

function lbNav(dir) {
    lbIndex = (lbIndex + dir + allImages.length) % allImages.length;
    document.getElementById('lbImg').src = allImages[lbIndex];
}

document.getElementById('lightbox').addEventListener('click', function(e) {
    if (e.target === this) closeLightbox();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft')  lbNav(-1);
    if (e.key === 'ArrowRight') lbNav(1);
});

// Application submission
function applyForRoom(roomId, roomName){
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
</script>
</body>
</html>
    