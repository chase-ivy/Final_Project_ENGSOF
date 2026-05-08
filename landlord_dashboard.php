<?php
session_start();
require_once "config.php";
require_once "oop.php";
$oop = new oopPHP();

// Authentication temporarily disabled so the admin can access the dashboard directly.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_room'])) {
        $res = $oop->add_room($_POST['room_name'], $_POST['price'], $_POST['description'], $_POST['max_occupants']);
        $_SESSION['msg'] = $res ? "Room added successfully." : "err:Failed to add room.";
        header("Location: landlord_dashboard.php"); exit();
    }
    if (isset($_POST['update_room'])) {
        $res = $oop->update_room($_POST['room_id'], $_POST['room_name'], $_POST['price'], $_POST['description'], $_POST['max_occupants']);
        $_SESSION['msg'] = $res ? "Room updated." : "err:Failed to update.";
        header("Location: landlord_dashboard.php"); exit();
    }

    header("Content-Type: application/json");
    if (isset($_POST['delete_room']))      { echo json_encode(["status" => $oop->delete_room($_POST['room_id']) ? "deleted" : "error"]); exit(); }
    if (isset($_POST['assign_tenant']))    { echo json_encode($oop->assign_tenant($_POST['user_id'], $_POST['room_id'], $_POST['start_date'], $_POST['end_date'])); exit(); }
    if (isset($_POST['remove_tenant']))    { echo json_encode($oop->remove_tenant($_POST['tenant_id'])); exit(); }
    if (isset($_POST['get_room_tenants'])) { echo json_encode($oop->get_room_tenants($_POST['room_id'])); exit(); }
    if (isset($_POST['get_room_images']))  { echo json_encode($oop->get_room_images($_POST['room_id'])); exit(); }
    if (isset($_POST['delete_image']))     { echo json_encode($oop->delete_image($_POST['image_id'], $_POST['room_id'])); exit(); }
    if (isset($_POST['get_room_applicants'])) { echo json_encode($oop->get_room_applications($_POST['room_id'])); exit(); }
    if (isset($_POST['verify_application'])) { echo json_encode($oop->verify_application($_POST['application_id'], $_POST['user_id'], $_POST['room_id'])); exit(); }
    if (isset($_POST['reject_application'])) { echo json_encode($oop->reject_application($_POST['application_id'], $_POST['room_id'])); exit(); }
}

$rooms   = $oop->get_rooms();
$tenants = $oop->get_tenants();
$users   = $connect->query("SELECT user_id,name FROM users WHERE role='tenant'")->fetchAll(PDO::FETCH_ASSOC);
$tenantMap = [];
foreach ($tenants as $t) $tenantMap[$t['room_id']][] = $t;

$msg = $_SESSION['msg'] ?? null;
$uploadStatus = $_SESSION['upload_status'] ?? null;
unset($_SESSION['msg'], $_SESSION['upload_status']);
$isErr = $msg && str_starts_with($msg, 'err:');
if ($isErr) $msg = substr($msg, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard · RSYNC</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
<style>
:root{--ink:#0a0a0f;--ink2:#3d3d4a;--ink3:#8e8ea0;--bg:#f4f3ef;--amber:#e8a020;--green:#059669;--red:#dc2626;--border:rgba(0,0,0,.08);--r:14px;--sh:0 2px 12px rgba(0,0,0,.06);}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{display:flex;flex-direction:column;min-height:100vh;font-family:'DM Sans',sans-serif;background:var(--bg);color:var(--ink);font-size:14px;}
a{text-decoration:none;}

nav{background:var(--ink);display:flex;align-items:center;justify-content:space-between;padding:0 32px;height:56px;}
nav span{font-family:'Syne',sans-serif;font-size:18px;font-weight:800;color:#fff;letter-spacing:3px;}
nav span em{color:var(--amber);font-style:normal;}
nav .nav-actions{display:flex;align-items:center;gap:10px;}
nav a{font-size:13px;color:rgba(255,255,255,.65);border:1px solid rgba(255,255,255,.2);padding:6px 14px;border-radius:8px;transition:.18s;}
nav a:hover{color:#fff;border-color:rgba(255,255,255,.5);}

.wrap{flex:1; width:92%;max-width:1320px;margin:26px auto;}
.top-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.top-bar h1{font-family:'Syne',sans-serif;font-size:20px;font-weight:800;}

.toast{padding:10px 16px;border-radius:9px;margin-bottom:16px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:7px;background:#fefce8;color:#92400e;border:1px solid #fde68a;}
.toast.err{background:#fef2f2;color:var(--red);border-color:#fecaca;}

.btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border:none;border-radius:9px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:500;cursor:pointer;transition:.15s;}
.btn-dark{background:var(--ink);color:#fff;}.btn-dark:hover{background:#1e1e2e;}
.btn-ghost{background:transparent;border:1px solid var(--border);color:var(--ink);}.btn-ghost:hover{background:rgba(0,0,0,.04);}
.btn-amber{background:rgba(232,160,32,.12);color:#92400e;border:1px solid rgba(232,160,32,.3);}
.btn-green{background:rgba(5,150,105,.1);color:var(--green);border:1px solid rgba(5,150,105,.25);}
.btn-red{background:rgba(220,38,38,.08);color:var(--red);border:1px solid rgba(220,38,38,.2);}
.btn-sm{padding:5px 10px;font-size:12px;}

/* TABLE */
.tbl-wrap{background:#fff;border-radius:var(--r);box-shadow:var(--sh);overflow:auto;border:1px solid var(--border);}
table{width:100%;border-collapse:collapse;min-width:960px;}
thead th{background:var(--ink);color:#fff;padding:11px 14px;font-size:12px;font-weight:600;text-align:left;white-space:nowrap;letter-spacing:.3px;}
tbody td{padding:11px 14px;border-bottom:1px solid var(--border);vertical-align:middle;}
tbody tr:last-child td{border-bottom:none;}
tbody tr:hover{background:#fafaf8;}

.img-strip{display:flex;gap:3px;flex-wrap:wrap;}
.img-strip img{width:38px;height:38px;object-fit:cover;border-radius:6px;border:1px solid var(--border);}
.img-strip img:first-child{border:2px solid var(--amber);}
.no-img{color:var(--ink3);font-size:11px;}
.badge{display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;font-weight:600;}
.badge-av{background:rgba(209,250,229,.9);color:#065f46;}
.badge-oc{background:rgba(254,226,226,.9);color:#991b1b;}
.bar-wrap{display:flex;align-items:center;gap:7px;}
.bar-wrap span{font-size:12px;font-weight:500;white-space:nowrap;}
.bar{flex:1;height:4px;border-radius:999px;background:rgba(0,0,0,.08);overflow:hidden;min-width:40px;}
.bar-fill{height:100%;border-radius:999px;background:var(--ink);}
.bar-fill.full{background:var(--red);}
.t-chip{font-size:11px;color:var(--ink3);display:block;line-height:1.6;}

/* MODAL */
.modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.42);justify-content:center;align-items:center;z-index:999;padding:20px;overflow-y:auto;}
.modal.show{display:flex;}
.modal-box{background:#fff;width:min(500px,96vw);border-radius:16px;padding:26px;box-shadow:0 24px 60px rgba(0,0,0,.16);position:relative;margin:auto;}
.modal-box h3{font-family:'Syne',sans-serif;font-size:17px;font-weight:800;margin-bottom:16px;}
.close-x{position:absolute;top:16px;right:16px;background:none;border:none;font-size:18px;cursor:pointer;color:var(--ink3);line-height:1;}
.close-x:hover{color:var(--ink);}
.field{margin-bottom:13px;}
.field label{display:block;font-size:11px;font-weight:600;color:var(--ink3);margin-bottom:4px;letter-spacing:.3px;text-transform:uppercase;}
.field input,.field textarea,.field select{width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:8px;font-family:'DM Sans',sans-serif;font-size:14px;outline:none;background:#fff;transition:.18s;}
.field input:focus,.field textarea:focus,.field select:focus{border-color:var(--amber);}
.field textarea{resize:vertical;min-height:68px;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:11px;}

/* ── EXISTING IMAGES SECTION ── */
.existing-imgs{margin-bottom:12px;}
.existing-imgs-label{font-size:11px;font-weight:600;color:var(--ink3);text-transform:uppercase;letter-spacing:.3px;margin-bottom:8px;}
.existing-grid{display:flex;flex-wrap:wrap;gap:8px;}
.existing-item{position:relative;width:76px;height:76px;border-radius:10px;overflow:visible;}
.existing-item img{width:76px;height:76px;object-fit:cover;border-radius:10px;border:2px solid transparent;display:block;}
.existing-item.is-cover img{border-color:var(--amber);}
.existing-item .cover-tag{position:absolute;bottom:4px;left:50%;transform:translateX(-50%);font-size:9px;font-weight:700;background:var(--amber);color:#fff;padding:2px 6px;border-radius:4px;white-space:nowrap;}
.existing-item .del-img{position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:var(--red);color:#fff;border:none;cursor:pointer;font-size:11px;display:flex;align-items:center;justify-content:center;line-height:1;box-shadow:0 1px 4px rgba(0,0,0,.2);transition:.15s;}
.existing-item .del-img:hover{background:#b91c1c;transform:scale(1.1);}
.no-existing{font-size:12px;color:var(--ink3);}

/* ── NEW IMAGE UPLOAD GRID ── */
.img-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:7px;margin-top:5px;}
.img-slot{position:relative;}
.img-slot label{display:flex;flex-direction:column;align-items:center;justify-content:center;height:72px;border:1.5px dashed var(--border);border-radius:9px;cursor:pointer;font-size:10px;color:var(--ink3);background:#fafaf8;margin:0;transition:.18s;gap:2px;}
.img-slot label:hover{border-color:var(--amber);color:var(--amber);}
.img-slot label.filled{border-style:solid;border-color:var(--amber);padding:0;overflow:hidden;}
.img-slot label.filled img{width:100%;height:100%;object-fit:cover;}
.img-slot input[type=file]{display:none;}
.new-cover-tag{position:absolute;top:3px;left:3px;font-size:9px;font-weight:700;background:var(--amber);color:#fff;padding:2px 5px;border-radius:4px;}

.modal-foot{display:flex;justify-content:flex-end;gap:8px;margin-top:18px;padding-top:14px;border-top:1px solid var(--border);}

footer{background:var(--ink);border-top:1px solid rgba(255,255,255,.06);padding:22px 32px;display:flex;justify-content:space-between;align-items:center;margin-top:auto;}
.foot-logo{font-family:'Syne',sans-serif;font-size:15px;font-weight:800;color:#fff;letter-spacing:2px;}
.foot-logo em{color:var(--amber);font-style:normal;}
footer p{font-size:12px;color:rgba(255,255,255,.3);}
@media(max-width:640px){nav{padding:0 16px;}.wrap{width:96%;}.img-grid{grid-template-columns:repeat(2,1fr);}footer{flex-direction:column;gap:6px;padding:18px;}}
</style>
</head>
<body>

<nav>
    <span>RS<em>Y</em>NC</span>
    <div class="nav-actions">
        <a href="maintenance.php">Maintenance</a>
        <a href="register_documents.php">Submit Documents</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="wrap">
    <div class="top-bar">
        <h1>Room Management</h1>
        <button class="btn btn-dark" onclick="document.getElementById('addModal').classList.add('show')">+ Add Room</button>
    </div>

    <?php if ($msg): ?><div class="toast <?= $isErr?'err':'' ?>"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if ($uploadStatus): ?><div class="toast"><?= htmlspecialchars($uploadStatus) ?></div><?php endif; ?>

    <div class="tbl-wrap">
    <table>
        <thead><tr><th>Images</th><th>Room</th><th>Price</th><th>Occupants</th><th>Status</th><th>Tenants</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($rooms as $row):
            $max = (int)($row['max_occupants'] ?? 1);
            $cur = (int)($row['current_occupants'] ?? 0);
            $pct = $max > 0 ? round($cur/$max*100) : 0;
            $full = $cur >= $max;
            $imgs = $oop->get_room_images($row['room_id']);
            $rTen = $tenantMap[$row['room_id']] ?? [];
        ?>
        <tr>
            <td>
                <?php if (!empty($imgs)): ?>
                <div class="img-strip">
                    <?php foreach ($imgs as $img): ?><img src="images/<?= htmlspecialchars($img['filename']) ?>" onerror="this.style.display='none'" title="<?= $img['sort_order']==0?'Cover':'Image '.($img['sort_order']+1) ?>"><?php endforeach; ?>
                </div>
                <?php else: ?><span class="no-img">No images</span><?php endif; ?>
            </td>
            <td><strong><?= htmlspecialchars($row['room_name']) ?></strong></td>
            <td>₱<?= number_format($row['price']) ?></td>
            <td>
                <div class="bar-wrap">
                    <span><?= $cur ?>/<?= $max ?></span>
                    <div class="bar"><div class="bar-fill <?= $full?'full':'' ?>" style="width:<?= $pct ?>%"></div></div>
                </div>
            </td>
            <td><span class="badge <?= $row['status']==='Available'?'badge-av':'badge-oc' ?>"><?= $row['status'] ?></span></td>
            <td>
                <?php if (!empty($rTen)): ?>
                    <?php foreach ($rTen as $t): ?><span class="t-chip">· <?= htmlspecialchars($t['name']) ?> <span style="opacity:.55">(<?= $t['start_date'] ?>–<?= $t['end_date'] ?>)</span></span><?php endforeach; ?>
                <?php else: ?><span style="color:var(--ink3)">—</span><?php endif; ?>
            </td>
            <td style="white-space:nowrap">
                <button class="btn btn-green btn-sm" onclick="openAssign(<?= $row['room_id'] ?>)">Assign</button>
                <button class="btn btn-amber btn-sm" onclick="openEdit(<?= $row['room_id'] ?>,'<?= addslashes(htmlspecialchars($row['room_name'])) ?>','<?= $row['price'] ?>','<?= addslashes(htmlspecialchars($row['description'])) ?>','<?= $max ?>')">Edit</button>
                <button class="btn btn-red btn-sm"   onclick="openRemove(<?= $row['room_id'] ?>)">Remove Tenant</button>
                <button class="btn btn-ghost btn-sm" onclick="openApplicants(<?= $row['room_id'] ?>)">Applicants</button>
                <button class="btn btn-ghost btn-sm" onclick="delRoom(<?= $row['room_id'] ?>)">Delete</button>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- ══ ADD MODAL ══ -->
<div class="modal" id="addModal">
<div class="modal-box">
    <button class="close-x" onclick="closeModal('addModal')">✕</button>
    <h3>Add Room</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_room" value="1">
        <div class="field"><label>Room Name</label><input type="text" name="room_name" placeholder="e.g. Room 201" required></div>
        <div class="row2">
            <div class="field"><label>Price / month</label><input type="number" name="price" placeholder="3500" min="0" required></div>
            <div class="field"><label>Max Occupants</label><input type="number" name="max_occupants" value="1" min="1" required></div>
        </div>
        <div class="field"><label>Images — up to 8 · first = cover</label><div class="img-grid" id="add_grid"></div></div>
        <div class="field"><label>Description</label><textarea name="description" placeholder="Describe the room…"></textarea></div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" onclick="closeModal('addModal')">Cancel</button>
            <button type="submit" class="btn btn-dark">Save Room</button>
        </div>
    </form>
</div>
</div>

<!-- ══ EDIT MODAL ══ -->
<div class="modal" id="editModal">
<div class="modal-box">
    <button class="close-x" onclick="closeModal('editModal')">✕</button>
    <h3>Edit Room</h3>
    <form method="POST" enctype="multipart/form-data" id="editForm">
        <input type="hidden" name="update_room" value="1">
        <input type="hidden" name="room_id" id="edit_id">

        <div class="field"><label>Room Name</label><input type="text" name="room_name" id="edit_name" required></div>
        <div class="row2">
            <div class="field"><label>Price / month</label><input type="number" name="price" id="edit_price" min="0" required></div>
            <div class="field"><label>Max Occupants</label><input type="number" name="max_occupants" id="edit_max" min="1" required></div>
        </div>

        <!-- Existing images with delete buttons -->
        <div class="existing-imgs">
            <div class="existing-imgs-label">Current Images</div>
            <div class="existing-grid" id="existing_grid">
                <span class="no-existing">Loading…</span>
            </div>
        </div>

        <!-- Upload new images to fill remaining slots -->
        <div class="field" id="new_imgs_field">
            <label>Add More Images <span style="text-transform:none;font-weight:400" id="slots_left_label"></span></label>
            <div class="img-grid" id="edit_grid"></div>
        </div>

        <div class="field"><label>Description</label><textarea name="description" id="edit_desc"></textarea></div>
        <div class="modal-foot">
            <button type="button" class="btn btn-ghost" onclick="closeModal('editModal')">Cancel</button>
            <button type="submit" class="btn btn-dark">Update Room</button>
        </div>
    </form>
</div>
</div>

<!-- ══ ASSIGN MODAL ══ -->
<div class="modal" id="assignModal">
<div class="modal-box">
    <button class="close-x" onclick="closeModal('assignModal')">✕</button>
    <h3>Assign Tenant</h3>
    <input type="hidden" id="ar_id">
    <div class="field"><label>Tenant</label>
        <select id="ar_user">
            <?php foreach ($users as $u): ?><option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['name']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="row2">
        <div class="field"><label>Contract Start</label><input type="date" id="ar_start"></div>
        <div class="field"><label>Contract End</label><input type="date" id="ar_end"></div>
    </div>
    <div class="modal-foot">
        <button class="btn btn-ghost" onclick="closeModal('assignModal')">Cancel</button>
        <button class="btn btn-dark"  onclick="assignTenant()">Assign</button>
    </div>
</div>
</div>

<!-- ══ REMOVE MODAL ══ -->
<div class="modal" id="removeModal">
<div class="modal-box">
    <button class="close-x" onclick="closeModal('removeModal')">✕</button>
    <h3>Remove Tenant</h3>
    <p style="font-size:13px;color:var(--ink3);margin-bottom:14px">Select the tenant to remove from this room.</p>
    <div class="field"><label>Tenant</label><select id="rm_tid"><option>Loading…</option></select></div>
    <div id="rm_info" style="font-size:12px;color:var(--ink3);margin-top:5px"></div>
    <div class="modal-foot">
        <button class="btn btn-ghost" onclick="closeModal('removeModal')">Cancel</button>
        <button class="btn btn-red"   onclick="removeTenant()">Remove</button>
    </div>
</div>
</div>

<!-- ══ APPLICANTS MODAL ══ -->
<div class="modal" id="applicantsModal">
<div class="modal-box">
    <button class="close-x" onclick="closeModal('applicantsModal')">✕</button>
    <h3>Room Applicants</h3>
    <div id="applicants_list" style="margin-top:16px;max-height:400px;overflow-y:auto;">
        <div style="text-align:center;padding:20px;color:var(--ink3);">
            <i class="bx bx-loader-alt bx-spin" style="font-size:24px;"></i>
            <p>Loading applicants…</p>
        </div>
    </div>
</div>
</div>

<footer>
    <div class="foot-logo">RS<em>Y</em>NC</div>
    <p>© 2026 RSYNC. All rights reserved.</p>
</footer>

<script>
function closeModal(id){document.getElementById(id).classList.remove('show');}

// ── ADD GRID ──
function buildAddGrid(){
    const g=document.getElementById('add_grid');g.innerHTML='';
    for(let i=0;i<8;i++) g.appendChild(makeSlot('add_grid',i,i===0));
}
buildAddGrid();

// ── EDIT: open modal, load existing images ──
let currentEditRoomId = null;

function openEdit(id,name,price,desc,max){
    currentEditRoomId = id;
    document.getElementById('edit_id').value    = id;
    document.getElementById('edit_name').value  = name;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_desc').value  = desc;
    document.getElementById('edit_max').value   = max;

    document.getElementById('existing_grid').innerHTML = '<span class="no-existing">Loading…</span>';
    document.getElementById('edit_grid').innerHTML = '';
    document.getElementById('editModal').classList.add('show');

    loadExistingImages(id);
}

function loadExistingImages(roomId){
    post('get_room_images=1&room_id='+roomId).then(imgs=>{
        renderExistingImages(imgs, roomId);
    });
}

function renderExistingImages(imgs, roomId){
    const grid = document.getElementById('existing_grid');
    grid.innerHTML = '';

    if(!imgs.length){
        grid.innerHTML = '<span class="no-existing">No images yet.</span>';
    } else {
        imgs.forEach((img,i)=>{
            const item = document.createElement('div');
            item.className = 'existing-item' + (i===0?' is-cover':'');
            item.id = 'eimg_'+img.image_id;

            const im = document.createElement('img');
            im.src = 'images/'+img.filename;
            im.onerror = ()=>{ im.src='https://placehold.co/76x76?text=?'; };

            const del = document.createElement('button');
            del.type='button';
            del.className='del-img';
            del.title='Delete this image';
            del.innerHTML='✕';
            del.onclick = ()=> deleteExistingImage(img.image_id, roomId);

            item.appendChild(im);
            item.appendChild(del);

            if(i===0){
                const tag=document.createElement('span');
                tag.className='cover-tag';tag.textContent='COVER';
                item.appendChild(tag);
            }

            grid.appendChild(item);
        });
    }

    // Rebuild upload slots for remaining capacity
    const used = imgs.length;
    const slots = Math.max(0, 8 - used);
    const lbl = document.getElementById('slots_left_label');
    lbl.textContent = slots > 0 ? '('+slots+' slot'+(slots!==1?'s':'')+' remaining)' : '(room full — 8/8)';

    const editGrid = document.getElementById('edit_grid');
    editGrid.innerHTML='';
    // First new upload becomes cover if no existing images
    for(let i=0;i<slots;i++) editGrid.appendChild(makeSlot('edit_grid',i,used===0&&i===0));
}

function deleteExistingImage(imageId, roomId){
    if(!confirm('Remove this image from the room?')) return;
    post('delete_image=1&image_id='+imageId+'&room_id='+roomId).then(res=>{
        if(res.status==='deleted'){
            // Reload images in modal
            loadExistingImages(roomId);
        } else {
            alert(res.message || 'Could not delete image.');
        }
    });
}

// ── GENERIC SLOT BUILDER ──
function makeSlot(gridId, i, isCover){
    const slot = document.createElement('div');
    slot.className='img-slot';

    const lbl=document.createElement('label');
    lbl.htmlFor=gridId+'_s'+i;
    lbl.innerHTML = isCover
        ? '📷<span>Cover</span>'
        : '<span style="font-size:17px;line-height:1">+</span><span>'+(i+1)+'</span>';

    const inp=document.createElement('input');
    inp.type='file'; inp.name='images[]'; inp.id=gridId+'_s'+i; inp.accept='image/*';
    inp.addEventListener('change',function(){
        if(this.files[0]){
            const r=new FileReader();
            r.onload=e=>{
                lbl.classList.add('filled');
                lbl.innerHTML='<img src="'+e.target.result+'">';
                if(isCover){
                    const t=document.createElement('span');
                    t.className='new-cover-tag';t.textContent='COVER';
                    slot.appendChild(t);
                }
            };
            r.readAsDataURL(this.files[0]);
        }
    });

    slot.appendChild(lbl); slot.appendChild(inp);
    return slot;
}

// ── ASSIGN ──
function openAssign(rid){
    document.getElementById('ar_id').value=rid;
    const today=new Date(),end=new Date(today);end.setMonth(end.getMonth()+1);
    document.getElementById('ar_start').value=today.toISOString().split('T')[0];
    document.getElementById('ar_end').value=end.toISOString().split('T')[0];
    document.getElementById('assignModal').classList.add('show');
}

// ── REMOVE TENANT ──
function openRemove(rid){
    const sel=document.getElementById('rm_tid');
    sel.innerHTML='<option>Loading…</option>';
    document.getElementById('rm_info').textContent='';
    document.getElementById('removeModal').classList.add('show');
    post('get_room_tenants=1&room_id='+rid).then(list=>{
        if(!list.length){sel.innerHTML='<option value="">No active tenants</option>';return;}
        sel.innerHTML=list.map(t=>`<option value="${t.tenant_id}" data-s="${t.start_date}" data-e="${t.end_date}">${t.name} (${t.start_date}–${t.end_date})</option>`).join('');
    });
}
document.getElementById('rm_tid').addEventListener('change',function(){
    const o=this.options[this.selectedIndex];
    document.getElementById('rm_info').textContent=o.dataset.s?'Contract: '+o.dataset.s+' → '+o.dataset.e:'';
});

// ── SHARED POST ──
function post(b){return fetch('',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:b}).then(r=>r.json());}

function assignTenant(){
    post('assign_tenant=1&room_id='+document.getElementById('ar_id').value+'&user_id='+document.getElementById('ar_user').value+'&start_date='+document.getElementById('ar_start').value+'&end_date='+document.getElementById('ar_end').value)
    .then(d=>{if(d.status==='error')alert(d.message);else location.reload();});
}
function removeTenant(){
    const id=document.getElementById('rm_tid').value;
    if(!id){alert('No tenant selected.');return;}
    if(!confirm('Remove this tenant?'))return;
    post('remove_tenant=1&tenant_id='+id).then(()=>location.reload());
}
function delRoom(id){
    if(!confirm('Delete this room? This cannot be undone.'))return;
    post('delete_room=1&room_id='+id).then(()=>location.reload());
}

// ── APPLICANTS ──
let currentApplicantsRoomId = null;

function openApplicants(roomId){
    currentApplicantsRoomId = roomId;
    document.getElementById('applicantsModal').classList.add('show');
    post('get_room_applicants=1&room_id='+roomId).then(apps=>{
        renderApplicants(apps);
    });
}

function renderApplicants(apps){
    const list = document.getElementById('applicants_list');
    if(!apps.length){
        list.innerHTML='<p style="text-align:center;color:var(--ink3);padding:20px;">No applicants yet.</p>';
        return;
    }
    list.innerHTML = apps.map(app=>`
        <div style="padding:12px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
            <div>
                <strong>${app.name}</strong>
                <div style="font-size:12px;color:var(--ink3);">${app.email}</div>
                <div style="font-size:11px;color:var(--ink3);margin-top:4px;">Applied: ${new Date(app.applied_at).toLocaleDateString()}</div>
                <div style="margin-top:5px;">
                    <span class="badge ${app.status === 'Verified' ? 'badge-av' : app.status === 'Rejected' ? 'badge-oc' : ''}" style="background:${app.status==='Pending'?'rgba(251,191,36,.2)':app.status==='Verified'?'rgba(5,150,105,.15)':'rgba(220,38,38,.1)'};color:${app.status==='Pending'?'#b45309':app.status==='Verified'?'var(--green)':'var(--red)'}">${app.status}</span>
                </div>
            </div>
            <div style="display:flex;gap:6px;">
                ${app.status === 'Pending' ? `
                    <button class="btn btn-green btn-sm" onclick="verifyApplicant(${app.application_id},${app.user_id})">Verify</button>
                    <button class="btn btn-red btn-sm" onclick="rejectApplicant(${app.application_id})">Reject</button>
                ` : `<span style="font-size:11px;color:var(--ink3);">No action</span>`}
            </div>
        </div>
    `).join('');
}

function verifyApplicant(appId, userId){
    if(!confirm('Verify this applicant?'))return;
    post('verify_application=1&application_id='+appId+'&user_id='+userId+'&room_id='+currentApplicantsRoomId).then(res=>{
        if(res.status==='success') post('get_room_applicants=1&room_id='+currentApplicantsRoomId).then(apps=>renderApplicants(apps));
        else alert('Error verifying applicant.');
    });
}

function rejectApplicant(appId){
    if(!confirm('Reject this applicant?'))return;
    post('reject_application=1&application_id='+appId+'&room_id='+currentApplicantsRoomId).then(res=>{
        if(res.status==='success') post('get_room_applicants=1&room_id='+currentApplicantsRoomId).then(apps=>renderApplicants(apps));
        else alert('Error rejecting applicant.');
    });
}
</script>
</body>
</html>
