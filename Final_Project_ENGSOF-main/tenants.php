<?php
/* =========================================================
   ADMIN DASHBOARD - DEMO VERSION (FRONTEND ONLY)
   ========================================================= */

/* =========================
   FUNCTION A: VIEW TENANT DETAILS
   ========================= */
function viewTenantDetails() {
    $tenants = [
        ["tenant_id"=>1, "name"=>"Juan Dela Cruz", "contact"=>"09171234567", "room"=>"Deluxe Room", "status"=>"Active"],
        ["tenant_id"=>2, "name"=>"Maria Santos", "contact"=>"09179876543", "room"=>"Cozy Studio", "status"=>"Inactive"],
        ["tenant_id"=>3, "name"=>"Pedro Reyes", "contact"=>"09172345678", "room"=>"Budget Room", "status"=>"Active"]
    ];
?>
<div class="card">
    <div class="card-header">
        <h2>Tenant Details</h2>
    </div>
    <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Contact Number</th>
            <th>Assigned Room</th>
            <th>Status</th>
        </tr>
        <?php foreach ($tenants as $tenant): ?>
        <tr>
            <td><?= $tenant['tenant_id']; ?></td>
            <td><?= $tenant['name']; ?></td>
            <td><?= $tenant['contact']; ?></td>
            <td><?= $tenant['room']; ?></td>
            <td>
                <span class="<?= strtolower($tenant['status']); ?>"><?= $tenant['status']; ?></span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php
}

/* =========================
   FUNCTION B: VIEW CONTRACT DETAILS
   ========================= */
function viewContractDetails() {
    $contracts = [
        ["contract_id"=>"C001", "tenant_name"=>"Juan Dela Cruz", "room"=>"Deluxe Room", "start_date"=>"2026-01-01", "end_date"=>"2026-12-31", "status"=>"Active"],
        ["contract_id"=>"C002", "tenant_name"=>"Maria Santos", "room"=>"Cozy Studio", "start_date"=>"2025-03-01", "end_date"=>"2025-12-31", "status"=>"Expired"],
        ["contract_id"=>"C003", "tenant_name"=>"Pedro Reyes", "room"=>"Budget Room", "start_date"=>"2026-02-01", "end_date"=>"2026-11-30", "status"=>"Active"]
    ];
?>
<div class="card">
    <div class="card-header">
        <h2>Contract Details</h2>
    </div>
    <table>
        <tr>
            <th>Contract ID</th>
            <th>Tenant Name</th>
            <th>Room</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Status</th>
        </tr>
        <?php foreach ($contracts as $c): ?>
        <tr>
            <td><?= $c['contract_id']; ?></td>
            <td><?= $c['tenant_name']; ?></td>
            <td><?= $c['room']; ?></td>
            <td><?= $c['start_date']; ?></td>
            <td><?= $c['end_date']; ?></td>
            <td>
                <span class="<?= strtolower($c['status']); ?>"><?= $c['status']; ?></span>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php
}

/* =========================
   FUNCTION C: UPDATE CONTRACT DETAILS
   ========================= */
function updateContractDetails() {
    $tenants = ["Juan Dela Cruz","Maria Santos","Pedro Reyes"];
    $rooms = ["Deluxe Room","Cozy Studio","Budget Room"];
?>
<div class="card">
    <div class="card-header">
        <h2>Update Contract Details</h2>
    </div>
    <form>
        <label>Tenant Name</label>
        <select>
            <option value="">-- Select Tenant --</option>
            <?php foreach ($tenants as $t): ?>
                <option><?= $t ?></option>
            <?php endforeach; ?>
        </select>

        <label>Room</label>
        <select>
            <option value="">-- Select Room --</option>
            <?php foreach ($rooms as $r): ?>
                <option><?= $r ?></option>
            <?php endforeach; ?>
        </select>

        <label>Start Date</label>
        <input type="date">

        <label>End Date</label>
        <input type="date">

        <button type="button" class="btn approve">Update</button>
    </form>
</div>
<?php
}

/* =========================
   FUNCTION D: UPDATE TENANT STATUS
   ========================= */
function updateTenantStatus() {
    $tenants = [
        ["tenant_id"=>1, "name"=>"Juan Dela Cruz", "room"=>"Deluxe Room", "status"=>"Registered"],
        ["tenant_id"=>2, "name"=>"Maria Santos", "room"=>"Cozy Studio", "status"=>"Paid"],
        ["tenant_id"=>3, "name"=>"Pedro Reyes", "room"=>"Budget Room", "status"=>"On-Time"]
    ];
    $statuses = ["Registered","Paid","On-Time","Delayed"];
?>
<div class="card">
    <div class="card-header">
        <h2>Update Tenant Status</h2>
    </div>
    <table>
        <tr>
            <th>ID</th>
            <th>Tenant Name</th>
            <th>Room</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($tenants as $t): ?>
        <tr>
            <td><?= $t['tenant_id']; ?></td>
            <td><?= $t['name']; ?></td>
            <td><?= $t['room']; ?></td>
            <td>
                <select>
                    <?php foreach ($statuses as $s): ?>
                        <option <?= $s==$t['status']?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <button type="button" class="btn approve">Update</button>
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
<title>Admin Dashboard - Demo</title>

<style>
* { box-sizing: border-box; font-family: "Segoe UI", Arial, sans-serif; margin:0; padding:0; }
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
tr:nth-child(even){background:#fdf2d5;}

/* STATUS BADGES */
.active, .registered, .paid, .on-time { background:#10B981; color:white; padding:5px 10px; border-radius:20px; }
.inactive, .expired, .delayed { background:#EF4444; color:white; padding:5px 10px; border-radius:20px; }

/* FORM STYLING */
form { display:flex; flex-direction:column; gap:10px; }
form label { font-weight:500; }
form input, form select { padding:8px; border-radius:6px; border:1px solid #ccc; }
form button { background:#10B981; color:white; border:none; padding:10px; border-radius:8px; cursor:pointer; }

/* BUTTONS */
.btn { cursor:pointer; border-radius:6px; padding:8px 12px; }
.approve { background:#10B981; color:white; }
.delete { background:#EF4444; color:white; }
</style>
</head>

<body>

<nav>
    <h2>Admin Dashboard</h2>
    <div class="nav-right">
        <a href="#">Tenants</a>
        <a href="#">Contracts</a>
        <a href="#">Update Contract</a>
        <a href="#">Tenant Status</a>
    </div>
</nav>

<div class="container">

<?php
// DISPLAY DEMO SECTIONS
viewTenantDetails();
viewContractDetails();
updateContractDetails();
updateTenantStatus();
?>

</div>

<!-- =========================================================
     ALL JAVASCRIPT COMMENTED OUT (FOR DEMO PURPOSES)
     ========================================================= -->

<!--
<script>
function exampleFunction() {
    console.log("JS disabled for demo");
}
function updateContract() {
    console.log("Update contract clicked");
}
function updateTenantStatus() {
    console.log("Update tenant status clicked");
}
</script>
-->

</body>
</html>