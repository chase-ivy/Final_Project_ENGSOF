<?php
require_once 'config.php';
require_once 'oop.php';

$oop = new oopPHP();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $res = $oop->submit_rating($_POST['room_id'], $_POST['rating'], $_POST['feedback']);
    echo "<script>alert('".$res['message']."');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave a Review · RSYNC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --bg:#f5f5f0;
    --card:#fff;
    --ink:#111118;
    --muted:#6b7280;
    --border:#e5e7eb;
    --radius:12px;
    --shadow:0 2px 12px rgba(0,0,0,.06);
    --gold:#fbbf24;
}

*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Inter',sans-serif;
    background:var(--bg);
    color:var(--ink);
}

/* NAV */
nav{
    height:56px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 60px;
    background:#fff;
    border-bottom:1px solid var(--border);
}
.logo{font-weight:800;font-size:16px;}
.nav-links{display:flex;gap:20px;}
.nav-links a{text-decoration:none;color:var(--muted);font-size:14px;}
.nav-links a:hover{color:var(--ink);}

/* CONTAINER */
.wrap{
    width:90%;
    max-width:520px;
    margin:60px auto;
}

/* CARD */
.card{
    background:var(--card);
    border-radius:var(--radius);
    border:1px solid var(--border);
    box-shadow:var(--shadow);
    padding:24px;
}

.card h2{
    font-size:20px;
    font-weight:800;
    margin-bottom:16px;
}

/* FORM */
.form-group{
    margin-bottom:18px;
}

label{
    font-size:13px;
    color:var(--muted);
    display:block;
    margin-bottom:6px;
}

textarea{
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid var(--border);
    font-family:'Inter',sans-serif;
    font-size:14px;
    outline:none;
    min-height:100px;
    resize:vertical;
}

textarea:focus{
    border-color:var(--ink);
}

/* ⭐ STAR RATING */
.star-wrap{
    display:flex;
    gap:6px;
    font-size:28px;
    cursor:pointer;
}

.star{
    color:#d1d5db;
    transition:.2s;
}

.star.active{
    color:var(--gold);
}

.star:hover{
    transform:scale(1.15);
}

/* BUTTON */
.btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:var(--ink);
    color:#fff;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
}
.btn:hover{
    background:#2d2d3a;
}

/* BACK */
.back{
    display:inline-block;
    margin-bottom:16px;
    font-size:13px;
    color:var(--muted);
    text-decoration:none;
}
.back:hover{color:var(--ink);}
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

    <a href="room_detail.php?id=<?= $_GET['room_id'] ?>" class="back">← Back to Room</a>

    <div class="card">
        <h2>Leave a Review</h2>

        <form method="POST">
            <input type="hidden" name="room_id" value="<?= $_GET['room_id'] ?>">

            <!-- ⭐ STAR RATING -->
            <div class="form-group">
                <label>Rating</label>
                <div class="star-wrap" id="starWrap">
                    <span class="star" data-value="1">★</span>
                    <span class="star" data-value="2">★</span>
                    <span class="star" data-value="3">★</span>
                    <span class="star" data-value="4">★</span>
                    <span class="star" data-value="5">★</span>
                </div>
                <input type="hidden" name="rating" id="ratingInput" required>
            </div>

            <!-- FEEDBACK -->
            <div class="form-group">
                <label>Your Feedback</label>
                <textarea name="feedback" placeholder="Share your experience..." required></textarea>
            </div>

            <button type="submit" class="btn">Submit Review</button>
        </form>
    </div>

</div>

<script>
// ⭐ STAR LOGIC
const stars = document.querySelectorAll('.star');
const ratingInput = document.getElementById('ratingInput');

stars.forEach((star, index) => {

    star.addEventListener('mouseover', () => {
        highlight(index);
    });

    star.addEventListener('click', () => {
        ratingInput.value = star.dataset.value;
    });
});

document.getElementById('starWrap').addEventListener('mouseleave', () => {
    highlight((ratingInput.value || 0) - 1);
});

function highlight(index){
    stars.forEach((s,i)=>{
        s.classList.toggle('active', i <= index);
    });
}

// VALIDATION
document.querySelector('form').addEventListener('submit', function(e){
    if (!ratingInput.value) {
        alert("Please select a rating.");
        e.preventDefault();
    }
});
</script>

</body>
</html>