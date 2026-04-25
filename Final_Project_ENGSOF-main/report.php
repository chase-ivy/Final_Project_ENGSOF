<?php
/* =========================================================
   ADMIN DASHBOARD - DEMO VERSION (FRONTEND ONLY)
   ========================================================= */

/* =========================
   FUNCTION A: CALCULATE TOTAL AMOUNT
   ========================= */
function calculateTotalAmount() {
    $tenants = [
        ["tenant_name"=>"Juan Dela Cruz","room"=>"Deluxe Room","monthly_rent"=>6500,"security_deposit"=>5000,"other_charges"=>500],
        ["tenant_name"=>"Maria Santos","room"=>"Cozy Studio","monthly_rent"=>4500,"security_deposit"=>4500,"other_charges"=>300],
        ["tenant_name"=>"Pedro Reyes","room"=>"Budget Room","monthly_rent"=>2500,"security_deposit"=>2500,"other_charges"=>200]
    ];

    foreach($tenants as &$t) {
        $t['total_amount'] = $t['monthly_rent'] + $t['security_deposit'] + $t['other_charges'];
    }

    // Mock summary per month
    $monthlySummary = [
        "January"=>18000,
        "February"=>22000,
        "March"=>15000
    ];
?>
<div class="card">
    <div class="card-header">
        <h2>Tenant Total Amount</h2>
    </div>
    <table>
        <tr>
            <th>Tenant Name</th>
            <th>Room</th>
            <th>Monthly Rent</th>
            <th>Security Deposit</th>
            <th>Other Charges</th>
            <th>Total Amount</th>
        </tr>
        <?php foreach($tenants as $t): ?>
        <tr>
            <td><?= $t['tenant_name']; ?></td>
            <td><?= $t['room']; ?></td>
            <td>₱<?= number_format($t['monthly_rent']); ?></td>
            <td>₱<?= number_format($t['security_deposit']); ?></td>
            <td>₱<?= number_format($t['other_charges']); ?></td>
            <td>₱<?= number_format($t['total_amount']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3 style="margin-top:20px;">Monthly Income Summary</h3>
    <table>
        <tr>
            <th>Month</th>
            <th>Total Income</th>
        </tr>
        <?php foreach($monthlySummary as $month=>$amount): ?>
        <tr>
            <td><?= $month; ?></td>
            <td>₱<?= number_format($amount); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3 style="margin-top:20px;">Income Distribution (Pie Chart)</h3>
    <canvas id="incomePieChart" style="max-width:400px; max-height:300px;"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// =========================
// PIE CHART FOR INCOME
// =========================
const ctx = document.getElementById('incomePieChart').getContext('2d');
const data = {
    labels: <?php echo json_encode(array_keys($monthlySummary)); ?>,
    datasets: [{
        label: 'Monthly Income',
        data: <?php echo json_encode(array_values($monthlySummary)); ?>,
        backgroundColor: ['#10B981','#3B82F6','#F59E0B','#EF4444','#8B5CF6','#F472B6'],
        borderWidth: 1
    }]
};
const incomePieChart = new Chart(ctx, {
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
</script>
<?php
}

/* =========================
   FUNCTION B: RECORD TRANSACTION
   ========================= */
function recordTransaction() {
    $transactions = [
        ["txn_id"=>"TXN001","tenant_name"=>"Juan Dela Cruz","room"=>"Deluxe Room","payment_type"=>"Rent","amount"=>6500,"date_paid"=>"2026-03-01","status"=>"Pending"],
        ["txn_id"=>"TXN002","tenant_name"=>"Maria Santos","room"=>"Cozy Studio","payment_type"=>"Deposit","amount"=>4500,"date_paid"=>"2026-03-02","status"=>"Completed"],
        ["txn_id"=>"TXN003","tenant_name"=>"Pedro Reyes","room"=>"Budget Room","payment_type"=>"Partial Payment","amount"=>1200,"date_paid"=>"2026-03-05","status"=>"Pending"]
    ];
?>
<div class="card">
    <div class="card-header">
        <h2>Tenant Transactions</h2>
    </div>
    <table>
        <tr>
            <th>Transaction ID</th>
            <th>Tenant Name</th>
            <th>Room</th>
            <th>Payment Type</th>
            <th>Amount Paid</th>
            <th>Date Paid</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach($transactions as $txn): ?>
        <tr>
            <td><?= $txn['txn_id']; ?></td>
            <td><?= $txn['tenant_name']; ?></td>
            <td><?= $txn['room']; ?></td>
            <td><?= $txn['payment_type']; ?></td>
            <td>₱<?= number_format($txn['amount']); ?></td>
            <td><?= $txn['date_paid']; ?></td>
            <td>
                <span class="<?= strtolower($txn['status']); ?>"><?= $txn['status']; ?></span>
            </td>
            <td>
                <?php if($txn['status']=="Pending"): ?>
                    <button class="btn approve">Mark as Paid</button>
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Finance Demo</title>

<style>
* { box-sizing:border-box; font-family:"Segoe UI", Arial, sans-serif; margin:0; padding:0; }
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
        <a href="#">Finance Overview</a>
        <a href="#">Transactions</a>
    </div>
</nav>

<div class="container">

<?php
// DISPLAY DEMO SECTIONS
calculateTotalAmount();
recordTransaction();
?>

</div>

<!-- =========================================================
     ALL OTHER JAVASCRIPT COMMENTED OUT (FOR DEMO PURPOSES)
     ========================================================= -->

<!--
<script>
// Placeholder JS commented for demo
function markPaid(txnId) {
    console.log("Mark transaction as paid:", txnId);
}
</script>
-->

</body>
</html>