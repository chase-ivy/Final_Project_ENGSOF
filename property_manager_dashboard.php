<?php
session_start();
require_once "config.php";
require_once "oop.php";

$oop = new oopPHP();

// Auth Check: Ensure only managers (or admins) can access
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'manager' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$rooms = $oop->get_rooms();
$reports = $oop->get_reports();

// Calculate Stats
$totalRooms = count($rooms);
$available = count(array_filter($rooms, fn($r) => $r['status'] === 'Available'));
$occupied = $totalRooms - $available;
$vacancyRate = $totalRooms > 0 ? round(($available / $totalRooms) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Manager Dashboard · RSYNC</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #0a0a0f;
            --ink2: #3d3d4a;
            --bg: #f4f3ef;
            --accent: #00796B; /* Teal theme for Management */
            --white: #ffffff;
            --border: rgba(0,0,0,0.08);
            --shadow: 0 4px 12px rgba(0,0,0,0.05);
            --r: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg); color: var(--ink); }

        nav {
            background: var(--ink);
            color: var(--white);
            padding: 15px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container { width: 90%; max-width: 1200px; margin: 40px auto; }
        
        .header-flex { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; }
        .header-flex h1 { font-family: 'Syne', sans-serif; font-size: 32px; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--white); padding: 20px; border-radius: var(--r); box-shadow: var(--shadow); border: 1px solid var(--border); }
        .stat-card i { font-size: 24px; color: var(--accent); margin-bottom: 10px; }
        .stat-card h3 { font-size: 14px; color: var(--ink2); text-transform: uppercase; letter-spacing: 1px; }
        .stat-card .val { font-size: 28px; font-weight: 700; margin-top: 5px; }

        /* Tables */
        .card { background: var(--white); border-radius: var(--r); box-shadow: var(--shadow); padding: 25px; margin-bottom: 30px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; border-bottom: 2px solid var(--bg); color: var(--ink2); font-weight: 600; }
        td { padding: 14px 12px; border-bottom: 1px solid var(--bg); font-size: 15px; }

        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .available { background: #dcfce7; color: #15803d; }
        .occupied { background: #fee2e2; color: #b91c1c; }
        .pending { background: #fef9c3; color: #854d0e; }

        .btn-view { color: var(--accent); text-decoration: none; font-weight: 500; }
        .btn-view:hover { text-decoration: underline; }

    </style>
</head>
<body>

<nav>
    <div style="font-family:'Syne'; font-size:24px;">RS<span>Y</span>NC</div>
    <div>
        <span style="margin-right:20px;">Manager: <?= htmlspecialchars($_SESSION['name']) ?></span>
        <a href="logout.php" style="color:white; text-decoration:none;"><i class="bx bx-log-out"></i> Logout</a>
    </div>
</nav>

<div class="container">
    <div class="header-flex">
        <div>
            <p style="color: var(--accent); font-weight: 600;">Overview</p>
            <h1>Property Management</h1>
        </div>
        <div class="date"><?= date('F d, Y') ?></div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <i class="bx bx-home-alt"></i>
            <h3>Total Rooms</h3>
            <div class="val"><?= $totalRooms ?></div>
        </div>
        <div class="stat-card">
            <i class="bx bx-check-circle"></i>
            <h3>Available</h3>
            <div class="val"><?= $available ?></div>
        </div>
        <div class="stat-card">
            <i class="bx bx-user-voice"></i>
            <h3>Occupancy</h3>
            <div class="val"><?= $occupied ?></div>
        </div>
        <div class="stat-card">
            <i class="bx bx-trending-up"></i>
            <h3>Vacancy Rate</h3>
            <div class="val"><?= $vacancyRate ?>%</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Room Inventory Status</h2>
            <a href="rooms.php" class="btn-view">View All Rooms</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Room Name</th>
                    <th>Price</th>
                    <th>Occupancy</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach(array_slice($rooms, 0, 5) as $room): ?>
                <tr>
                    <td><?= htmlspecialchars($room['room_name']) ?></td>
                    <td>₱<?= number_format($room['price']) ?></td>
                    <td><?= $room['current_occupants'] ?? 0 ?> / <?= $room['max_occupants'] ?></td>
                    <td>
                        <span class="badge <?= strtolower($room['status']) ?>">
                            <?= $room['status'] ?>
                        </span>
                    </td>
                    <td><a href="room_detail.php?id=<?= $room['room_id'] ?>" class="btn-view">Manage</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Recent Incident Reports</h2>
            <span class="badge pending"><?= count(array_filter($reports, fn($rep) => $rep['status'] === 'Pending')) ?> New</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Tenant</th>
                    <th>Room</th>
                    <th>Subject</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr><td colspan="5" style="text-align:center;">No reports found.</td></tr>
                <?php else: ?>
                    <?php foreach(array_slice($reports, 0, 5) as $rep): ?>
                    <tr>
                        <td><?= date('M d', strtotime($rep['created_at'])) ?></td>
                        <td><?= htmlspecialchars($rep['name']) ?></td>
                        <td><?= htmlspecialchars($rep['room_name']) ?></td>
                        <td><?= htmlspecialchars($rep['subject']) ?></td>
                        <td><span class="badge <?= strtolower($rep['status']) ?>"><?= $rep['status'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>