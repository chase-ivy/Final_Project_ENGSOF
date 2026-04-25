<?php
require_once 'config.php';
require_once 'oop.php';

$oop = new oopPHP();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: login.php");
    exit();
}//remove this if you want to view the UI only

$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tenant Dashboard · RSYNC</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
:root{
    --bg:#f5f5f0;
    --card:#ffffff;
    --ink:#111118;
    --muted:#6b7280;
    --border:#e5e7eb;
    --radius:14px;
    --shadow:0 2px 12px rgba(0,0,0,.06);
    --accent:#111118;
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
    background:#fff;
    border-bottom:1px solid var(--border);
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:0 60px;
}

.logo{
    font-weight:800;
    letter-spacing:.5px;
}

.nav-links a{
    text-decoration:none;
    color:var(--muted);
    margin-left:20px;
    font-size:14px;
}
.nav-links a:hover{color:var(--ink);}

/* WRAPPER */
.wrap{
    width:90%;
    max-width:1100px;
    margin:40px auto;
}

/* HERO */
.hero{
    margin-bottom:30px;
}
.hero h1{
    font-size:26px;
    font-weight:800;
}
.hero p{
    color:var(--muted);
    font-size:14px;
    margin-top:4px;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:16px;
    margin-top:20px;
}

/* CARD */
.card{
    background:var(--card);
    border:1px solid var(--border);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    padding:18px;
    transition:.2s;
}
.card:hover{
    transform:translateY(-3px);
}

.card h3{
    font-size:15px;
    font-weight:700;
    margin-bottom:6px;
}

.card p{
    font-size:13px;
    color:var(--muted);
}

/* BIG STATS */
.stat{
    font-size:28px;
    font-weight:800;
    margin-top:10px;
}

/* BUTTON */
.btn{
    display:inline-block;
    margin-top:12px;
    padding:10px 14px;
    background:var(--accent);
    color:#fff;
    border-radius:10px;
    font-size:13px;
    text-decoration:none;
}
.btn:hover{
    background:#2d2d3a;
}

/* TABLE CARD */
.table-card{
    margin-top:20px;
    background:#fff;
    border:1px solid var(--border);
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    padding:18px;
}

table{
    width:100%;
    border-collapse:collapse;
    font-size:13px;
}

th, td{
    padding:10px;
    border-bottom:1px solid var(--border);
    text-align:left;
}

th{
    color:var(--muted);
    font-weight:600;
}
</style>
</head>

<body>

<nav>
    <div class="logo">RSYNC</div>
    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="rooms.php">Rooms</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="wrap">

    <div class="hero">
        <h1>Welcome, <?= htmlspecialchars($name) ?> </h1>
        <p>Your tenant dashboard overview</p>
    </div>

    <!-- STATS -->
    <div class="grid">

        <div class="card">
            <h3>Current Room</h3>
            <p>Your assigned boarding room</p>
            <div class="stat">—</div>
        </div>

        <div class="card">
            <h3>Rent Status</h3>
            <p>Payment status overview</p>
            <div class="stat">Active</div>
        </div>

        <div class="card">
            <h3>Stay Duration</h3>
            <p>Your current tenancy period</p>
            <div class="stat">—</div>
        </div>

        <div class="card">
            <h3>Support</h3>
            <p>Need help? Contact landlord</p>
            <a href="#" class="btn">Contact</a>
        </div>

    </div>

    <!-- SECTION -->
    <div class="table-card">
        <h3 style="margin-bottom:10px;">Recent Activity</h3>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Action</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>—</td>
                    <td>No activity yet</td>
                    <td>—</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>