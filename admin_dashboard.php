<?php
/* =========================================================
   ADMIN DASHBOARD - DEMO VERSION (FRONTEND ONLY)
   ========================================================= */

// Demo data for financial summary
$totalPayments = 125000; // Demo total payments
$monthlyEarnings = 15000; // Demo monthly earnings

// Demo data for monthly earnings pie chart
$monthlySummary = [
    ["month" => "January", "amount" => 12000],
    ["month" => "February", "amount" => 15000],
    ["month" => "March", "amount" => 18000],
    ["month" => "April", "amount" => 14000],
    ["month" => "May", "amount" => 16000]
];

/* ================= FINANCIAL SUMMARY ================= */
function financialSummary() {
    global $totalPayments, $monthlyEarnings, $monthlySummary;
?>
<div class="card">
    <div class="card-header">
        <h2>Financial Summary</h2>
    </div>
    <div class="financial-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;padding:20px;">
        <div class="total-card" style="background:#fff;border-radius:12px;padding:20px;border:1px solid var(--border);box-shadow:var(--sh);">
            <h3 style="font-size:14px;color:var(--ink3);margin-bottom:8px;">Total Payments</h3>
            <p style="font-size:24px;font-weight:700;color:var(--ink);">₱<?= number_format($totalPayments) ?></p>
        </div>
        <div class="total-card" style="background:#fff;border-radius:12px;padding:20px;border:1px solid var(--border);box-shadow:var(--sh);">
            <h3 style="font-size:14px;color:var(--ink3);margin-bottom:8px;">Monthly Earnings</h3>
            <p style="font-size:24px;font-weight:700;color:var(--green);">₱<?= number_format($monthlyEarnings) ?></p>
        </div>
    </div>
    <div style="padding:20px;">
        <h3 style="font-family:'Syne',sans-serif;font-size:16px;font-weight:800;margin-bottom:16px;">Monthly Earnings Breakdown</h3>
        <div style="max-width: 300px; margin: 0 auto;">
            <canvas id="monthlyEarningsChart" width="80" height="40"></canvas>
        </div>
    </div>
</div>
<?php }

/* ================= VERIFY USERS ================= */
function verifyUsers() {
    $users = [
        ["name" => "Biboy Del Rosario", "email" => "biboy@gmail.com", "status" => "pending"],
        ["name" => "Mark Santos", "email" => "marksantos@gmail.com", "status" => "pending"],
        ["name" => "Lee Yu", "email" => "leeyu@gmail.com", "status" => "approved"]
    ];
?>
<div class="card">
    <div class="card-header">
        <h2>Verify Users</h2>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <?php if ($user['status'] == "pending"): ?>
                <tr>
                    <td><?= $user['name']; ?></td>
                    <td><?= $user['email']; ?></td>
                    <td><span class="badge badge-pending">Pending</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn approve">Approve</button>
                            <button class="btn delete">Reject</button>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php }

/* ================= VERIFY PAYMENTS ================= */
function verifyPayments() {
    $payments = [
        ["tenant" => "Anna Cruz", "amount" => 5000, "status" => "pending"],
        ["tenant" => "Mark Lee", "amount" => 4500, "status" => "verified"],
        ["tenant" => "John Doe", "amount" => 6000, "status" => "pending"]
    ];
?>
<div class="card">
    <div class="card-header">
        <h2>Verify Payments</h2>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Tenant</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= $p['tenant']; ?></td>
                <td>₱<?= number_format($p['amount']); ?></td>
                <td><span class="badge <?= $p['status'] == 'verified' ? 'badge-verified' : 'badge-pending' ?>"><?= ucfirst($p['status']); ?></span></td>
                <td>
                    <?php if ($p['status'] == "pending"): ?>
                        <button class="btn approve">Verify</button>
                    <?php else: ?>
                        <span>Completed</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php }

/* ================= TRANSACTIONS ================= */
function monitorTransactions() {
    $transactions = [
        ["id"=>"202603-RM03","user"=>"Mark Sy","date"=>"2026-03-01","amount"=>5000,"status"=>"Completed"],
        ["id"=>"202603-RM03","user"=>"Vain Lee","date"=>"2026-03-05","amount"=>4500,"status"=>"Pending"],
        ["id"=>"202603-RM05","user"=>"Steven White","date"=>"2026-03-10","amount"=>6000,"status"=>"Completed"]
    ];
?>
<div class="card">
    <div class="card-header">
        <h2>Transaction Monitoring</h2>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($transactions as $t): ?>
            <tr>
                <td><?= $t['id']; ?></td>
                <td><?= $t['user']; ?></td>
                <td><?= $t['date']; ?></td>
                <td>₱<?= number_format($t['amount']); ?></td>
                <td><span class="badge <?= strtolower($t['status']) == 'completed' ? 'badge-verified' : 'badge-pending' ?>"><?= $t['status']; ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard · RSYNC</title>
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
nav a{font-size:13px;color:rgba(255,255,255,.75);border:1px solid rgba(255,255,255,.2);padding:8px 12px;border-radius:8px;transition:.18s;}
nav a:hover{color:#fff;border-color:rgba(255,255,255,.45);}
.wrap{width:92%;max-width:1320px;margin:26px auto;flex:1 0 auto;}
.top-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;gap:12px;flex-wrap:wrap;}
.top-bar h1{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;}
.btn{display:inline-flex;align-items:center;gap:5px;padding:8px 14px;border:none;border-radius:9px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:.15s;}
.btn-dark{background:var(--ink);color:#fff;}.btn-dark:hover{background:#1e1e2e;}
.btn-ghost{background:transparent;border:1px solid var(--border);color:var(--ink);}.btn-ghost:hover{background:rgba(0,0,0,.04);}
.card{background:#fff;border-radius:var(--r);box-shadow:var(--sh);overflow:hidden;border:1px solid var(--border);margin-bottom:24px;}
.card-header{padding:22px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:20px;}
.card-header h2{font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:var(--ink);margin:0;}
.tbl-wrap{overflow:auto;}
table{width:100%;border-collapse:collapse;min-width:720px;}
thead th{background:var(--ink);color:#fff;padding:14px 16px;font-size:12px;font-weight:600;text-align:left;letter-spacing:.3px;}
tbody td{padding:14px 16px;border-bottom:1px solid var(--border);vertical-align:middle;}
tbody tr:last-child td{border-bottom:none;}
.badge{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700;}
.badge-pending{background:rgba(245,158,11,.12);color:#92400e;}
.badge-verified{background:rgba(5,150,105,.12);color:var(--green);}
.action-buttons{display:flex;flex-wrap:wrap;gap:8px;}
.action-buttons .btn{padding:7px 12px;font-size:12px;}
</style>
</head>
<body>
<nav>
    <span>RS<span>Y</span>NC</span>
    <div>
        <a href="#" class="btn btn-ghost">Users</a>
        <a href="#" class="btn btn-ghost">Payments</a>
        <a href="#" class="btn btn-ghost">Transactions</a>
    </div>
</nav>
<div class="wrap">
    <div class="top-bar">
        <h1>Admin Dashboard</h1>
        <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
            <a href="login.php" class="btn btn-dark">Sign out</a>
        </div>
    </div>

    <?php
    financialSummary();
    verifyUsers();
    verifyPayments();
    monitorTransactions();
    ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('monthlyEarningsChart').getContext('2d');
    const data = {
        labels: <?php echo json_encode(array_column($monthlySummary, 'month')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($monthlySummary, 'amount')); ?>,
            backgroundColor: ['#10B981','#3B82F6','#F59E0B','#EF4444','#8B5CF6','#F472B6'],
            borderWidth: 1
        }]
    };
    const monthlyEarningsChart = new Chart(ctx, {
        type: 'pie',
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: { enabled: true }
            }
        }
    });
});
</script>
</body>
</html>