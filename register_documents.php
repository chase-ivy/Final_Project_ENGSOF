<?php
session_start();
require_once "config.php";
require_once "oop.php";
$oop = new oopPHP();

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = ['pdf','jpg','jpeg','png'];
    $messages = [];

    if (!empty($_FILES['documents']['name'][0])) {
        foreach ($_FILES['documents']['name'] as $i => $name) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if(!in_array($ext,$allowed)){
                $messages[] = "File $name has invalid format.";
                continue;
            }

            $tmp = $_FILES['documents']['tmp_name'][$i];
            $dest = "uploads/docs/".basename($name);

            if(move_uploaded_file($tmp, $dest)){
                $messages[] = "$name uploaded successfully.";
                // Optional: Save file info to DB
                $stmt = $connect->prepare("INSERT INTO landlord_docs (user_id,filename,uploaded_at,status) VALUES (?, ?, NOW(),'Pending')");
                $stmt->execute([$_SESSION['user_id'], $name]);
            } else {
                $messages[] = "Failed to upload $name.";
            }
        }
    } else {
        $messages[] = "No files selected.";
    }

    $_SESSION['msg'] = implode("<br>", $messages);
    header("Location: register_documents.php"); exit();
}

$msg = $_SESSION['msg'] ?? null;
unset($_SESSION['msg']);
$isErr = $msg && str_starts_with($msg,'Failed');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Submit Documents · RSYNC</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
<style>
    :root{--ink:#0a0a0f;--ink2:#3d3d4a;--ink3:#8e8ea0;--bg:#f4f3ef;--amber:#e8a020;--green:#059669;--red:#dc2626;--border:rgba(0,0,0,.08);--r:14px;--sh:0 2px 12px rgba(0,0,0,.06);}
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}

    body{font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);min-height:100vh;display:flex;flex-direction:column;}

    a{text-decoration:none;}
    nav{background:var(--ink);display:flex;align-items:center;justify-content:space-between;padding:0 32px;height:56px;}
    nav span{font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:#fff;letter-spacing:3px;}
    nav span em{color:var(--amber);font-style:normal;}
    nav .nav-actions{display:flex;align-items:center;gap:10px;}
    nav a{font-size:13px;color:rgba(255,255,255,.65);border:1px solid rgba(255,255,255,.2);padding:6px 14px;border-radius:8px;transition:.18s;}
    nav a:hover{color:#fff;border-color:rgba(255,255,255,.5);}
    .wrap{flex:1; width:92%;max-width:960px;margin:26px auto;}
    h1{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;margin-bottom:18px;}
    .toast{padding:10px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:7px;background:#fefce8;color:#92400e;border:1px solid #fde68a;}
    .toast.err{background:#fef2f2;color:var(--red);border-color:#fecaca;}
    .field{margin-bottom:13px;}
    .field label{display:block;font-size:11px;font-weight:600;color:var(--ink3);margin-bottom:4px;letter-spacing:.3px;text-transform:uppercase;}
    .field input[type=file]{width:100%;padding:9px;border:1.5px solid var(--border);border-radius:8px;background:#fff;}
    .btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border:none;border-radius:9px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:.15s;}
    .btn-dark{background:var(--ink);color:#fff;}.btn-dark:hover{background:#1e1e2e;}
    .modal-foot{display:flex;justify-content:flex-end;gap:8px;margin-top:18px;padding-top:14px;border-top:1px solid var(--border);}
    footer{background:var(--ink);border-top:1px solid rgba(255,255,255,.06);padding:22px 32px;display:flex;justify-content:space-between;align-items:center;margin-top:auto;}
    .foot-logo{font-family:'Syne',sans-serif;font-size:15px;font-weight:800;color:#fff;letter-spacing:2px;}
    .foot-logo em{color:var(--amber);font-style:normal;}
    footer p{font-size:12px;color:rgba(255,255,255,.3);}
    @media(max-width:640px){nav{padding:0 16px;}.wrap{width:96%;}footer{flex-direction:column;gap:6px;padding:18px;}}

    nav a.disabled {
        pointer-events: none;       /* makes it unclickable */
        opacity: 0.5;               /* greys it out */
        cursor: default;            /* shows normal arrow instead of hand */
        border-color: rgba(255,255,255,.1); /* lighter border */
        color: rgba(255,255,255,.4);       /* lighter text */
    }
</style>
</head>
<body>

<nav>
    <span>RS<em>Y</em>NC</span>
    <div class="nav-actions">
        <a href="landlord_dashboard.php" class="<?= $currentPage=='landlord_dashboard.php' ? 'disabled' : '' ?>">Dashboard</a>
        <a href="maintenance.php" class="<?= $currentPage=='maintenance.php' ? 'disabled' : '' ?>">Maintenance</a>
        <a href="register_documents.php" class="<?= $currentPage=='register_documents.php' ? 'disabled' : '' ?>">Submit Documents</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="wrap">
    <h1>Submit Your Business Documents</h1>

    <?php if ($msg): ?>
        <div class="toast <?= $isErr?'err':'' ?>"><?= $msg ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="field">
            <label>Upload Business Permits, IDs, or other required files (PDF, JPG, PNG)</label>
            <input type="file" name="documents[]" multiple required>
        </div>
        <div class="modal-foot">
            <button type="submit" class="btn btn-dark">Submit Documents</button>
        </div>
    </form>
</div>

<footer>
    <div class="foot-logo">RS<em>Y</em>NC</div>
    <p>© 2026 RSYNC. All rights reserved.</p>
</footer>

</body>
</html>