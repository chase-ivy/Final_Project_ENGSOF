<?php
require_once "config.php";

class oopPHP {

    private $conn;

    public function __construct() {
        global $connect;
        $this->conn = $connect;
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    /* ── LOGIN ── */
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([":email" => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'landlord')   header("Location: landlord_dashboard.php");
            elseif ($user['role'] === 'tenant') header("Location: tenant_dashboard.php");
            else                                header("Location: index.php");
            exit();
        } else {
            echo "<script>alert('Invalid login credentials.');</script>";
        }
    }

    /* ── UPLOAD HELPER ── */
    private function uploadImage($fileKey, $index = 0) {
        if (!isset($_FILES[$fileKey]['name'][$index]) || $_FILES[$fileKey]['error'][$index] !== UPLOAD_ERR_OK) {
            return null;
        }
        $uploadDir = "images/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES[$fileKey]['name'][$index]));
        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'][$index], $uploadDir . $filename)) {
            return $filename;
        }
        return null;
    }

    private function saveImages($room_id, array $files) {
        // Get the current max sort_order so new images append after existing
        $maxStmt = $this->conn->prepare("SELECT COALESCE(MAX(sort_order), -1) FROM room_images WHERE room_id=:id");
        $maxStmt->execute([":id" => $room_id]);
        $startOrder = (int)$maxStmt->fetchColumn() + 1;

        $stmt = $this->conn->prepare("INSERT INTO room_images (room_id, filename, sort_order) VALUES (:r, :f, :s)");
        foreach ($files as $i => $filename) {
            $stmt->execute([":r" => $room_id, ":f" => $filename, ":s" => $startOrder + $i]);
        }
    }

    /* ── ROOMS READ ── */
    public function get_rooms() {
        $stmt = $this->conn->query("
            SELECT r.*,
                   COUNT(DISTINCT t.tenant_id) AS current_occupants,
                   (SELECT filename FROM room_images ri WHERE ri.room_id = r.room_id ORDER BY ri.sort_order ASC LIMIT 1) AS cover_image
            FROM rooms r
            LEFT JOIN tenants t ON t.room_id = r.room_id AND t.status = 'Active'
            GROUP BY r.room_id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_room($name) {
        $stmt = $this->conn->prepare("
            SELECT r.*,
                   COUNT(DISTINCT t.tenant_id) AS current_occupants,
                   (SELECT filename FROM room_images ri WHERE ri.room_id = r.room_id ORDER BY ri.sort_order ASC LIMIT 1) AS cover_image
            FROM rooms r
            LEFT JOIN tenants t ON t.room_id = r.room_id AND t.status = 'Active'
            WHERE r.room_name LIKE :name
            GROUP BY r.room_id
        ");
        $stmt->execute([":name" => "%$name%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_room_by_id($room_id) {
        $stmt = $this->conn->prepare("
            SELECT r.*,
                   COUNT(DISTINCT t.tenant_id) AS current_occupants
            FROM rooms r
            LEFT JOIN tenants t ON t.room_id = r.room_id AND t.status = 'Active'
            WHERE r.room_id = :id
            GROUP BY r.room_id
        ");
        $stmt->execute([":id" => $room_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function get_room_images($room_id) {
        $stmt = $this->conn->prepare("SELECT * FROM room_images WHERE room_id=:id ORDER BY sort_order ASC");
        $stmt->execute([":id" => $room_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── DELETE SINGLE IMAGE ── */
    public function delete_image($image_id, $room_id) {
        if (!isset($_SESSION['user_id'])) return ["status" => "error", "message" => "Not authenticated"];

        // Verify the room belongs to this landlord
        $check = $this->conn->prepare("SELECT owner_id FROM rooms WHERE room_id=:rid");
        $check->execute([":rid" => $room_id]);
        $room = $check->fetch(PDO::FETCH_ASSOC);
        if (!$room || (int)$room['owner_id'] !== (int)$_SESSION['user_id']) {
            return ["status" => "error", "message" => "Unauthorized"];
        }

        // Get filename to delete physical file
        $fetch = $this->conn->prepare("SELECT filename FROM room_images WHERE image_id=:id AND room_id=:rid");
        $fetch->execute([":id" => $image_id, ":rid" => $room_id]);
        $img = $fetch->fetch(PDO::FETCH_ASSOC);
        if (!$img) return ["status" => "error", "message" => "Image not found"];

        // Delete DB record
        $this->conn->prepare("DELETE FROM room_images WHERE image_id=:id")->execute([":id" => $image_id]);

        // Delete physical file (non-fatal if missing)
        $filepath = "images/" . $img['filename'];
        if (file_exists($filepath)) @unlink($filepath);

        // Re-sequence sort_order so cover is always 0
        $reorder = $this->conn->prepare("SELECT image_id FROM room_images WHERE room_id=:rid ORDER BY sort_order ASC");
        $reorder->execute([":rid" => $room_id]);
        $remaining = $reorder->fetchAll(PDO::FETCH_COLUMN);
        $upd = $this->conn->prepare("UPDATE room_images SET sort_order=:s WHERE image_id=:id");
        foreach ($remaining as $order => $iid) {
            $upd->execute([":s" => $order, ":id" => $iid]);
        }

        return ["status" => "deleted", "remaining" => count($remaining)];
    }

    /* ── ROOMS ADD ── */
    public function add_room($name, $price, $desc, $max_occupants) {
        if (!isset($_SESSION['user_id'])) return false;

        $stmt = $this->conn->prepare("
            INSERT INTO rooms (room_name, price, description, status, max_occupants, owner_id)
            VALUES (:name, :price, :desc, 'Available', :max, :owner)
        ");
        $ok = $stmt->execute([
            ":name"  => $name,  ":price" => $price,
            ":desc"  => $desc,  ":max"   => max(1, (int)$max_occupants),
            ":owner" => $_SESSION['user_id']
        ]);
        if (!$ok) return false;

        $room_id  = $this->conn->lastInsertId();
        $uploaded = [];
        if (isset($_FILES['images'])) {
            for ($i = 0; $i < min(count($_FILES['images']['name']), 8); $i++) {
                $fn = $this->uploadImage('images', $i);
                if ($fn) $uploaded[] = $fn;
            }
        }
        if (!empty($uploaded)) {
            $this->saveImages($room_id, $uploaded);
            $_SESSION['upload_status'] = count($uploaded) . " image(s) stored successfully.";
        }
        return true;
    }

    /* ── ROOMS UPDATE ── */
    public function update_room($id, $name, $price, $desc, $max_occupants) {
        if (!isset($_SESSION['user_id'])) return false;

        $stmt = $this->conn->prepare("
            UPDATE rooms SET room_name=:name, price=:price, description=:desc, max_occupants=:max
            WHERE room_id=:id AND owner_id=:owner
        ");
        $ok = $stmt->execute([
            ":id" => $id, ":name" => $name, ":price" => $price,
            ":desc" => $desc, ":max" => max(1, (int)$max_occupants),
            ":owner" => $_SESSION['user_id']
        ]);
        if (!$ok) return false;

        // Only upload new images if any were selected — they APPEND to existing
        $hasNew = false;
        if (isset($_FILES['images'])) {
            foreach ($_FILES['images']['error'] as $err) {
                if ($err === UPLOAD_ERR_OK) { $hasNew = true; break; }
            }
        }
        if ($hasNew) {
            // Check how many images already exist
            $countStmt = $this->conn->prepare("SELECT COUNT(*) FROM room_images WHERE room_id=:id");
            $countStmt->execute([":id" => $id]);
            $existing = (int)$countStmt->fetchColumn();
            $slots    = max(0, 8 - $existing);

            $uploaded = [];
            for ($i = 0; $i < min(count($_FILES['images']['name']), $slots); $i++) {
                $fn = $this->uploadImage('images', $i);
                if ($fn) $uploaded[] = $fn;
            }
            if (!empty($uploaded)) {
                $this->saveImages($id, $uploaded);
                $_SESSION['upload_status'] = count($uploaded) . " new image(s) added.";
            }
        }
        return true;
    }

    /* ── ROOMS DELETE ── */
    public function delete_room($id) {
        $stmt = $this->conn->prepare("DELETE FROM rooms WHERE room_id=:id AND owner_id=:owner");
        return $stmt->execute([":id" => $id, ":owner" => $_SESSION['user_id']]);
    }

    /* ── TENANTS READ ── */
    public function get_tenants() {
        $stmt = $this->conn->query("
            SELECT t.*, u.name
            FROM tenants t
            JOIN users u ON t.user_id = u.user_id
            WHERE t.status = 'Active'
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get_room_tenants($room_id) {
        $stmt = $this->conn->prepare("
            SELECT t.tenant_id, t.start_date, t.end_date, u.name, u.user_id
            FROM tenants t
            JOIN users u ON t.user_id = u.user_id
            WHERE t.room_id = :id AND t.status = 'Active'
            ORDER BY t.start_date ASC
        ");
        $stmt->execute([":id" => $room_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── ASSIGN TENANT ── */
    public function assign_tenant($user_id, $room_id, $start_date, $end_date) {
        $check = $this->conn->prepare("SELECT max_occupants FROM rooms WHERE room_id=:id");
        $check->execute([":id" => $room_id]);
        $room = $check->fetch(PDO::FETCH_ASSOC);
        if (!$room) return ["status" => "error", "message" => "Room not found"];

        $countStmt = $this->conn->prepare("SELECT COUNT(*) FROM tenants WHERE room_id=:id AND status='Active'");
        $countStmt->execute([":id" => $room_id]);
        $current = (int)$countStmt->fetchColumn();

        if ($current >= (int)$room['max_occupants']) {
            return ["status" => "error", "message" => "Room is full ({$current}/{$room['max_occupants']} slots)"];
        }
        if (empty($start_date) || empty($end_date)) {
            return ["status" => "error", "message" => "Start and end date are required."];
        }
        if ($end_date <= $start_date) {
            return ["status" => "error", "message" => "End date must be after start date."];
        }

        $this->conn->prepare("
            INSERT INTO tenants (user_id, room_id, start_date, end_date, status)
            VALUES (:u, :r, :s, :e, 'Active')
        ")->execute([":u" => $user_id, ":r" => $room_id, ":s" => $start_date, ":e" => $end_date]);

        $newCount  = $current + 1;
        $newStatus = ($newCount >= (int)$room['max_occupants']) ? 'Occupied' : 'Available';
        $this->conn->prepare("UPDATE rooms SET status=:s WHERE room_id=:id")
                   ->execute([":s" => $newStatus, ":id" => $room_id]);

        return ["status" => "success"];
    }

    /* ── REMOVE SPECIFIC TENANT ── */
    public function remove_tenant($tenant_id) {
        $fetch = $this->conn->prepare("SELECT room_id FROM tenants WHERE tenant_id=:id");
        $fetch->execute([":id" => $tenant_id]);
        $row = $fetch->fetch(PDO::FETCH_ASSOC);
        if (!$row) return ["status" => "error", "message" => "Tenant not found"];

        $room_id = $row['room_id'];

        $this->conn->prepare("
            UPDATE tenants SET status='Left', end_date=LEAST(end_date, CURDATE())
            WHERE tenant_id=:id
        ")->execute([":id" => $tenant_id]);

        $countStmt = $this->conn->prepare("SELECT COUNT(*) FROM tenants WHERE room_id=:id AND status='Active'");
        $countStmt->execute([":id" => $room_id]);
        $remaining = (int)$countStmt->fetchColumn();

        $this->conn->prepare("UPDATE rooms SET status=:s WHERE room_id=:id")
                   ->execute([":s" => $remaining > 0 ? 'Occupied' : 'Available', ":id" => $room_id]);

        return ["status" => "removed"];
    }
}
?>
