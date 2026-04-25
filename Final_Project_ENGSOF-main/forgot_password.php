<?php
session_start();
require_once 'config.php';

$error = $success = '';

if (isset($_POST['send_otp'])) {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $connect->prepare("SELECT user_id, name FROM users WHERE email = :e");
    $stmt->execute([':e' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error = "No account found with that email.";
    } else {
        // Delete old OTPs for this email
        $connect->prepare("DELETE FROM password_resets WHERE email = :e")->execute([':e' => $email]);

        // Generate 6-digit OTP
        $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $connect->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (:e, :o, :x)")
                ->execute([':e' => $email, ':o' => $otp, ':x' => $expires]);

        // Store email in session for next step
        $_SESSION['reset_email'] = $email;

        // --- Send OTP via PHP mail() ---
        $to      = $email;
        $subject = "RSYNC — Your Password Reset OTP";
        $body    = "Hello {$user['name']},\n\n"
                 . "Your OTP code is: {$otp}\n\n"
                 . "This code expires in 10 minutes.\n"
                 . "If you did not request this, ignore this email.\n\n"
                 . "— RSYNC Team";
        $headers = "From: no-reply@rsync.local\r\nX-Mailer: PHP/" . phpversion();

        $sent = mail($to, $subject, $body, $headers);

        // DEV FALLBACK: show OTP on screen if mail() not configured
        if (!$sent) {
            $_SESSION['dev_otp'] = $otp; // remove this in production
        }

        header("Location: verify_otp.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password · RSYNC</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
<style>
:root{--ink:#0a0a0f;--ink2:#3d3d4a;--ink3:#8e8ea0;--bg:#f4f3ef;--amber:#e8a020;--border:rgba(0,0,0,.09);--r:12px;}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh;display:flex;justify-content:center;align-items:center;padding:24px;}
.box{width:100%;max-width:400px;background:#fff;border:1px solid var(--border);border-radius:18px;padding:38px;box-shadow:0 8px 32px rgba(0,0,0,.07);animation:up .5s both;}
@keyframes up{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}
.box-logo{font-family:'Syne',sans-serif;font-size:18px;font-weight:800;letter-spacing:3px;color:var(--ink);margin-bottom:22px;}
.box-logo span{color:var(--amber);}
.icon-wrap{width:52px;height:52px;background:rgba(232,160,32,.12);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:24px;color:var(--amber);margin-bottom:18px;}
.box h2{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;margin-bottom:6px;}
.box .sub{font-size:13px;color:var(--ink3);line-height:1.6;margin-bottom:24px;}
.field{margin-bottom:16px;}
.field label{display:block;font-size:11px;font-weight:600;color:var(--ink3);text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;}
.field .inp-wrap{position:relative;}
.field input{width:100%;padding:11px 38px 11px 13px;border:1.5px solid var(--border);border-radius:var(--r);font-family:'DM Sans',sans-serif;font-size:14px;outline:none;transition:.18s;background:#fafaf8;}
.field input:focus{border-color:var(--amber);background:#fff;box-shadow:0 0 0 3px rgba(232,160,32,.13);}
.field i{position:absolute;right:11px;top:50%;transform:translateY(-50%);font-size:17px;color:var(--ink3);}
.err{background:#fef2f2;border:1px solid #fecaca;color:#dc2626;font-size:13px;padding:10px 13px;border-radius:var(--r);margin-bottom:16px;}
.btn-main{width:100%;padding:12px;border:none;border-radius:var(--r);background:var(--ink);color:#fff;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:.18s;}
.btn-main:hover{background:#1e1e2e;}
.btn-back{display:block;width:100%;margin-top:10px;padding:11px;text-align:center;border-radius:var(--r);background:transparent;border:1.5px solid var(--border);color:var(--ink2);font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;text-decoration:none;transition:.18s;}
.btn-back:hover{border-color:var(--amber);color:var(--ink);}
</style>
</head>
<body>
<div class="box">
    <div class="box-logo">RS<span>Y</span>NC</div>
    <div class="icon-wrap"><i class="bx bx-lock-open-alt"></i></div>
    <h2>Forgot your password?</h2>
    <p class="sub">Enter your account email and we'll send you a 6-digit OTP to reset your password.</p>

    <?php if ($error): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST">
        <div class="field">
            <label>Email Address</label>
            <div class="inp-wrap">
                <input type="email" name="email" placeholder="you@example.com" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <i class="bx bx-envelope"></i>
            </div>
        </div>
        <button class="btn-main" name="send_otp">Send OTP</button>
        <a href="login.php" class="btn-back">← Back to Login</a>
    </form>
</div>
</body>
</html>
