<?php
/* =========================================================
   ADMIN DASHBOARD - DEMO VERSION (FRONTEND ONLY)
   ========================================================= */

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

    <table>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php foreach ($users as $user): ?>
        <?php if ($user['status'] == "pending"): ?>
        <tr>
            <td><?= $user['name']; ?></td>
            <td><?= $user['email']; ?></td>
            <td><span class="pending">Pending</span></td>
            <td>
                <div class="action-buttons">
                    <a class="btn approve">Approve</a>
                    <a class="btn delete">Reject</a>
                </div>
            </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
    </table>
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

    <table>
        <tr>
            <th>Tenant</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php foreach ($payments as $p): ?>
        <tr>
            <td><?= $p['tenant']; ?></td>
            <td>₱<?= number_format($p['amount']); ?></td>
            <td>
                <span class="<?= $p['status']; ?>">
                    <?= ucfirst($p['status']); ?>
                </span>
            </td>
            <td>
                <?php if ($p['status'] == "pending"): ?>
                    <a class="btn approve">Verify</a>
                <?php else: ?>
                    <span>Completed</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
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

    <table>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
        </tr>

        <?php foreach ($transactions as $t): ?>
        <tr>
            <td><?= $t['id']; ?></td>
            <td><?= $t['user']; ?></td>
            <td><?= $t['date']; ?></td>
            <td>₱<?= number_format($t['amount']); ?></td>
            <td>
                <span class="<?= strtolower($t['status']); ?>">
                    <?= $t['status']; ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php }
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

<style>
* {
    font-family: "Segoe UI", Arial;
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* SAME STYLE AS YOUR LANDLORD DASHBOARD */
body {
    background: #FFF8E7;
}

/* NAVBAR */
nav {
    display: flex;
    justify-content: space-between;
    padding: 15px 40px;
    background: #00796B;
    color: white;
}

.nav-right a {
    color: white;
    margin-left: 20px;
    text-decoration: none;
}

/* CONTAINER */
.container {
    width: 90%;
    margin: 40px auto;
}

/* CARD */
.card {
    background: #FFF8E7;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    margin-bottom: 20px;
}

/* HEADER */
.card-header {
    margin-bottom: 15px;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 16px;
    text-align: center;
}

th {
    background: #00796B;
    color: white;
}

tr:nth-child(even) {
    background: #fdf2d5;
}

/* STATUS */
.pending { background: orange; color: white; padding:5px 10px; border-radius:20px;}
.verified, .completed { background: #10B981; color:white; padding:5px 10px; border-radius:20px;}

/* BUTTONS */
.btn {
    padding: 6px 10px;
    border-radius: 8px;
    color: black;
    cursor: pointer;
}

.approve { background: #10B981; }
.delete { background: #EF4444; }

.action-buttons {
    display: flex;
    justify-content: center;
    gap: 5px;
}
</style>
</head>

<body>

<nav>
    <h2>Admin Dashboard</h2>
    <div class="nav-right">
        <a href="#">Users</a>
        <a href="#">Payments</a>
        <a href="#">Transactions</a>
    </div>
</nav>

<div class="container">

<?php
verifyUsers();
verifyPayments();
monitorTransactions();
?>

</div>

<!-- JS COMMENTED -->
<!--
<script>
ALL JS DISABLED FOR DEMO
</script>
-->

</body>
</html>