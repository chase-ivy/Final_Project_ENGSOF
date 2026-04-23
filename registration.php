<?php
session_start();
require_once "config.php";

if (isset($_POST['register'])) {
    $name     = $_POST['name'];
    $age      = $_POST['age'];
    $email    = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role     = $_POST['role'];

    try {
        $check = $connect->prepare("SELECT email FROM users WHERE email=:e");
        $check->execute([':e' => $email]);

        if ($check->rowCount() > 0) {
            $error = "Email already exists.";
        } else {
            $connect->prepare("INSERT INTO users (name,age,email,password,role) VALUES (:n,:a,:e,:p,:r)")
                    ->execute([':n'=>$name,':a'=>$age,':e'=>$email,':p'=>$password,':r'=>$role]);
            header("Location: login.php?registered=1"); exit();
        }
    } catch (PDOException $ex) {
        $error = "Something went wrong. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Register · RSYNC</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
<style>
:root{--ink:#0a0a0f;--ink2:#3d3d4a;--ink3:#8e8ea0;--bg:#f4f3ef;--amber:#e8a020;--border:rgba(0,0,0,.09);--r:12px;}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh;display:flex;}

.left{flex:1;position:relative;background:url('https://media.architecturaldigest.com/photos/63345d8c5e811ad45ceaace4/16:9/w_2560%2Cc_limit/Sleeping%2520Porch_Boggy%2520Slough_Sandersarchitecture.jpg') center/cover no-repeat;}
.left::after{content:'';position:absolute;inset:0;background:rgba(10,10,15,.48);}
.left-copy{position:absolute;bottom:52px;left:52px;z-index:2;color:#fff;max-width:380px;}
.left-copy .logo{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;letter-spacing:3px;margin-bottom:18px;}
.left-copy .logo span{color:var(--amber);}
.left-copy h2{font-family:'Syne',sans-serif;font-size:32px;font-weight:800;line-height:1.15;margin-bottom:10px;}
.left-copy p{font-size:14px;opacity:.7;line-height:1.6;}

.right{flex:1;display:flex;justify-content:center;align-items:center;padding:32px 20px;overflow-y:auto;}
.box{width:100%;max-width:380px;background:#fff;border:1px solid var(--border);border-radius:18px;padding:36px;box-shadow:0 8px 32px rgba(0,0,0,.07);animation:up .5s both;}
@keyframes up{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}

.box-logo{font-family:'Syne',sans-serif;font-size:18px;font-weight:800;letter-spacing:3px;color:var(--ink);margin-bottom:20px;}
.box-logo span{color:var(--amber);}
.box h2{font-family:'Syne',sans-serif;font-size:24px;font-weight:800;margin-bottom:4px;}
.box .sub{font-size:13px;color:var(--ink3);margin-bottom:24px;}

.err{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;font-size:13px;padding:10px 13px;border-radius:var(--r);margin-bottom:16px;}

.field{margin-bottom:14px;}
.field label{display:block;font-size:11px;font-weight:600;color:var(--ink3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;}
.field .inp-wrap{position:relative;}
.field input,.field select{width:100%;padding:11px 38px 11px 13px;border:1.5px solid var(--border);border-radius:var(--r);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:.18s;background:#fafaf8;appearance:none;}
.field input:focus,.field select:focus{border-color:var(--amber);background:#fff;box-shadow:0 0 0 3px rgba(232,160,32,.13);}
.field i{position:absolute;right:11px;top:50%;transform:translateY(-50%);font-size:17px;color:var(--ink3);cursor:pointer;pointer-events:none;}
.field i.clickable{pointer-events:auto;}

.btn-main{width:100%;padding:12px;border:none;border-radius:var(--r);background:var(--ink);color:#fff;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:.18s;margin-top:4px;}
.btn-main:hover{background:#1e1e2e;}

.foot{margin-top:18px;text-align:center;font-size:13px;color:var(--ink3);}
.foot a{color:var(--amber);font-weight:500;}
.foot a:hover{text-decoration:underline;}

@media(max-width:860px){.left{display:none;}.right{background:var(--bg);}}
</style>
</head>
<body>

<div class="left">
    <div class="left-copy">
        <div class="logo">RS<span>Y</span>NC</div>
        <h2>Start your journey</h2>
        <p>Find your ideal boarding space — designed for comfort, safety, and student living.</p>
    </div>
</div>

<div class="right">
<form method="POST" class="box">
    <div class="box-logo">RS<span>Y</span>NC</div>
    <h2>Create account</h2>
    <p class="sub">Join RSYNC and manage your boarding experience</p>

    <?php if (!empty($error)): ?>
        <div class="err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="field">
        <label>Full Name</label>
        <div class="inp-wrap">
            <input type="text" name="name" placeholder="Your Name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            <i class="bx bx-user"></i>
        </div>
    </div>

    <div class="field">
        <label>Age</label>
        <div class="inp-wrap">
            <input type="number" name="age" placeholder="Your Age" min="1" max="120" required value="<?= htmlspecialchars($_POST['age'] ?? '') ?>">
            <i class="bx bx-calendar"></i>
        </div>
    </div>

    <div class="field">
        <label>Email</label>
        <div class="inp-wrap">
            <input type="email" name="email" placeholder="Your email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <i class="bx bx-envelope"></i>
        </div>
    </div>

    <div class="field">
        <label>Password</label>
        <div class="inp-wrap">
            <input type="password" id="pw" name="Your password" placeholder="••••••••" required>
            <i class="bx bx-show clickable" onclick="togglePw()"></i>
        </div>
    </div>

    <div class="field">
        <label>Role</label>
        <div class="inp-wrap">
            <select name="role" required>
                <option value="" disabled <?= empty($_POST['role'])?'selected':'' ?>>Select role</option>
                <option value="tenant"    <?= ($_POST['role']??'')==='tenant'   ?'selected':'' ?>>Tenant</option>
                <option value="landlord"  <?= ($_POST['role']??'')==='landlord' ?'selected':'' ?>>Landlord</option>
                <option value="sublessor" <?= ($_POST['role']??'')==='sublessor'?'selected':'' ?>>Sublessor</option>
            </select>
            <i class="bx bx-chevron-down"></i>
        </div>
    </div>

    <button class="btn-main" name="register">Create Account</button>

    <div class="foot">Already have an account? <a href="login.php">Sign in</a></div>
</form>
</div>

<script>
function togglePw(){const p=document.getElementById('pw');p.type=p.type==='password'?'text':'password';}
</script>
</body>
</html>
