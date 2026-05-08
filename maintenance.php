<?php
session_start();
require_once "config.php";
require_once "oop.php";
$oop = new oopPHP();

// Authentication temporarily disabled for development access.

$maintenanceRequests = $oop->get_maintenance_requests();
$maintenanceStatus = $oop->get_maintenance_status();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Maintenance · RSYNC</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
<style>
:root{--ink:#0a0a0f;--ink2:#3d3d4a;--ink3:#8e8ea0;--bg:#f4f3ef;--amber:#e8a020;--green:#059669;--red:#dc2626;--border:rgba(0,0,0,.08);--r:14px;--sh:0 2px 12px rgba(0,0,0,.06);}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;}
body{display:flex;flex-direction:column;min-height:100vh;font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);font-size:14px;}
a{text-decoration:none;}
nav{background:var(--ink);display:flex;align-items:center;justify-content:space-between;padding:0 32px;height:56px;}
nav span{font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:#fff;letter-spacing:3px;}
nav span em{color:var(--amber);font-style:normal;}
nav .nav-actions{display:flex;align-items:center;gap:10px;}
nav a{font-size:13px;color:rgba(255,255,255,.65);border:1px solid rgba(255,255,255,.2);padding:6px 14px;border-radius:8px;transition:.18s;}
nav a:hover{color:#fff;border-color:rgba(255,255,255,.5);}
.wrap{flex:1;width:92%;max-width:1320px;margin:26px auto;}
.top-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;flex-wrap:wrap;gap:12px;}
.top-bar h1{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;}
.card{background:#fff;border-radius:var(--r);box-shadow:var(--sh);overflow:hidden;border:1px solid var(--border);margin-bottom:24px;}
.card-header{padding:22px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:20px;}
.card-header h2{font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:var(--ink);margin:0;}
.tbl-wrap{background:#fff;border-radius:var(--r);box-shadow:var(--sh);overflow:auto;border:1px solid var(--border);}
table{width:100%;border-collapse:collapse;min-width:720px;}
thead th{background:var(--ink);color:#fff;padding:14px 16px;font-size:12px;font-weight:600;text-align:left;letter-spacing:.3px;}
tbody td{padding:14px 16px;border-bottom:1px solid var(--border);vertical-align:middle;}
tbody tr:last-child td{border-bottom:none;}
tbody tr:hover{background:#fafaf8;}
.status-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;padding:20px;}
.status-card{background:#fff;border-radius:var(--r);border:1px solid var(--border);padding:18px;box-shadow:var(--sh);text-align:center;}
.status-card p{margin:0;}
.status-card .count{font-size:28px;font-weight:700;color:var(--ink);margin-bottom:8px;}
.status-card .label{font-size:13px;color:var(--ink3);}
.badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700;}
.badge-pending{background:rgba(245,158,11,.12);color:#92400e;}
.badge-completed{background:rgba(16,185,129,.14);color:#047857;}
.badge-inprogress{background:rgba(59,130,246,.12);color:#1d4ed8;}
footer{background:var(--ink);border-top:1px solid rgba(255,255,255,.06);padding:22px 32px;display:flex;justify-content:space-between;align-items:center;margin-top:auto;}
.foot-logo{font-family:'Syne',sans-serif;font-size:15px;font-weight:800;color:#fff;letter-spacing:2px;}
.foot-logo em{color:var(--amber);font-style:normal;}
footer p{font-size:12px;color:rgba(255,255,255,.3);}
@media(max-width:860px){.top-bar{flex-direction:column;align-items:flex-start;}.status-grid{grid-template-columns:1fr;}} 
@media(max-width:640px){nav{padding:0 16px;}.wrap{width:96%;}.card-header{flex-direction:column;align-items:flex-start;gap:12px;}footer{flex-direction:column;gap:10px;padding:18px;}}
</style>
</head>
<body>
<nav>
    <span>RS<em>Y</em>NC</span>
    <div class="nav-actions">
        <a href="landlord_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>
<div class="wrap">
    <div class="top-bar">
        <h1>Maintenance Requests</h1>
        <p style="font-size:14px;color:var(--ink3);max-width:560px;">Click on the Maintenance link from the dashboard to see tenant-submitted requests and the overall status report below.</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Tenant Maintenance Requests</h2>
        </div>
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($maintenanceRequests)): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--ink3);padding:24px;">No maintenance requests found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($maintenanceRequests as $req): ?>
                        <tr>
                            <td><?= htmlspecialchars($req['request_id'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($req['description'] ?? 'No description') ?></td>
                            <td><span class="badge <?= strtolower(str_replace(' ', '', $req['status'] ?? 'pending')) ?>"><?= htmlspecialchars($req['status'] ?? 'Pending') ?></span></td>
                            <td><?= htmlspecialchars($req['submitted_at'] ?? $req['request_date'] ?? 'Unknown') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>Maintenance Status Report</h2>
        </div>
        <div class="status-grid">
            <?php if (empty($maintenanceStatus)): ?>
                <div class="status-card"><p class="count">0</p><p class="label">No status data available</p></div>
            <?php else: ?>
                <?php foreach ($maintenanceStatus as $status): ?>
                <div class="status-card">
                    <p class="count"><?= (int)($status['count'] ?? 0) ?></p>
                    <p class="label"><?= htmlspecialchars($status['status'] ?? 'Unknown') ?></p>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<footer>
    <div class="foot-logo">RS<em>Y</em>NC</div>
    <p>© 2026 RSYNC. All rights reserved.</p>
</footer>
</body>
</html>