<?php
/* =========================================================
   ADMIN DASHBOARD - DEMO VERSION (FRONTEND ONLY)
   ========================================================= */

/* =========================
   FUNCTION A: MAINTENANCE REQUEST
   ========================= */
function maintenanceRequest() {
    $requests = [
        ["request_id"=>"MR-001", "tenant_name"=>"Juan Dela Cruz", "room"=>"Deluxe Room", "description"=>"Leaking faucet", "date"=>"2026-04-20", "status"=>"Pending"],
        ["request_id"=>"MR-002", "tenant_name"=>"Maria Santos", "room"=>"Cozy Studio", "description"=>"Wifi is not working", "date"=>"2026-03-18", "status"=>"Completed"],
        ["request_id"=>"MR-003", "tenant_name"=>"Pedro Reyes", "room"=>"Budget Room", "description"=>"Flickering lights in bathroom area", "date"=>"2026-06-22", "status"=>"Pending"]
    ];
?>
<div class="card">
    <div class="card-header">
        <h2>Maintenance Requests</h2>
    </div>
    <table>
        <tr>
            <th>Request ID</th>
            <th>Tenant Name</th>
            <th>Room</th>
            <th>Description</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($requests as $req): ?>
        <tr>
            <td><?= $req['request_id']; ?></td>
            <td><?= $req['tenant_name']; ?></td>
            <td><?= $req['room']; ?></td>
            <td><?= $req['description']; ?></td>
            <td><?= $req['date']; ?></td>
            <td>
                <span class="<?= strtolower($req['status']); ?>"><?= $req['status']; ?></span>
            </td>
            <td>
                <?php if($req['status']=="Pending"): ?>
                    <button class="btn approve">Mark as Completed</button>
                <?php else: ?>
                    <span class="text-success">Completed</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php
}

/* =========================
   FUNCTION B: MAINTENANCE STATUS REPORT
   ========================= */
function maintenanceStatusReport() {
    $reports = [
        ["report_id"=>"MSR-001", "tenant_name"=>"Juan Dela Cruz", "room"=>"Deluxe Room", "issue"=>"Leaking faucet", "resolved_date"=>"", "status"=>"Pending"],
        ["report_id"=>"MSR-002", "tenant_name"=>"Maria Santos", "room"=>"Cozy Studio", "issue"=>"Wifi is not working", "resolved_date"=>"2026-03-19", "status"=>"Completed"],
        ["report_id"=>"MSR-003", "tenant_name"=>"Pedro Reyes", "room"=>"Budget Room", "issue"=>"Flickering lights in the bathroom area", "resolved_date"=>"", "status"=>"Pending"]
    ];

    // Calculate summary
    $totalRequests = count($reports);
    $completedRequests = count(array_filter($reports, fn($r)=>$r['status']=="Completed"));
?>
<div class="card">
    <div class="card-header">
        <h2>Maintenance Status Report</h2>
    </div>

    <div style="margin-bottom:15px;">
        <strong>Total Requests:</strong> <?= $totalRequests; ?> |
        <strong>Completed:</strong> <?= $completedRequests; ?> |
        <strong>Pending:</strong> <?= $totalRequests - $completedRequests; ?>
    </div>

    <table>
        <tr>
            <th>Report ID</th>
            <th>Tenant Name</th>
            <th>Room</th>
            <th>Issue</th>
            <th>Date Resolved</th>
            <th>Status Summary</th>
        </tr>
        <?php foreach($reports as $r): ?>
        <tr>
            <td><?= $r['report_id']; ?></td>
            <td><?= $r['tenant_name']; ?></td>
            <td><?= $r['room']; ?></td>
            <td><?= $r['issue']; ?></td>
            <td><?= $r['resolved_date'] ?: "N/A"; ?></td>
            <td>
                <span class="<?= strtolower($r['status']); ?>"><?= $r['status']; ?></span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Maintenance Demo</title>

<style>
* { box-sizing: border-box; font-family:"Segoe UI", Arial, sans-serif; margin:0; padding:0; }
body { background:#FFF8E7; }

/* NAVBAR */
nav { display:flex; justify-content:space-between; padding:15px 40px; background:#00796B; color:white; }
.nav-right a { color:white; margin-left:20px; text-decoration:none; }

/* CONTAINER */
.container { width:90%; margin:40px auto; }

/* CARD */
.card { background:#FFF8E7; padding:20px; border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.08); margin-bottom:20px; }
.card-header { margin-bottom:15px; }

/* TABLE */
table { width:100%; border-collapse: collapse; }
th, td { padding:16px; text-align:center; }
th { background:#00796B; color:white; }
tr:nth-child(even) { background:#fdf2d5; }

/* STATUS BADGES */
.pending { background:orange; color:white; padding:5px 10px; border-radius:20px; }
.completed { background:#10B981; color:white; padding:5px 10px; border-radius:20px; }

/* BUTTONS */
.btn { cursor:pointer; border-radius:6px; padding:8px 12px; }
.approve { background:#10B981; color:white; }
</style>
</head>

<body>

<nav>
    <h2>Admin Dashboard</h2>
    <div class="nav-right">
        <a href="#">Maintenance Requests</a>
        <a href="#">Status Reports</a>
    </div>
</nav>

<div class="container">

<?php
// DISPLAY DEMO SECTIONS
maintenanceRequest();
maintenanceStatusReport();
?>

</div>

<!-- =========================================================
     ALL JAVASCRIPT COMMENTED OUT (FOR DEMO PURPOSES)
     ========================================================= -->

<!--
<script>
function markCompleted(requestId) {
    console.log("Mark request completed:", requestId);
}
function showReportDetails(reportId) {
    console.log("Show report details:", reportId);
}
</script>
-->

</body>
</html>