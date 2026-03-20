<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── VISITOR LOGIN ──
if ($action === 'visitor_login') {
    $rfid    = trim($_POST['rfid'] ?? '');
    $type    = trim($_POST['type'] ?? 'Student');
    $program = trim($_POST['program'] ?? '');
    $reason  = trim($_POST['reason'] ?? '');

    if (!$rfid || !$program || !$reason) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
        exit;
    }

    $db = getDB();

    // Check if blocked
    $stmt = $db->prepare("SELECT id FROM blocked_users WHERE rfid = ?");
    $stmt->bind_param('s', $rfid);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => '⚠️ You are not allowed to use the library.']);
        exit;
    }

    // Check if name exists in student_records
    $stmt = $db->prepare("SELECT name FROM student_records WHERE rfid = ?");
    $stmt->bind_param('s', $rfid);
    $stmt->execute();
    $rec = $stmt->get_result()->fetch_assoc();
    $name = $rec ? $rec['name'] : $rfid;

    // Log visit (no password)
    $stmt = $db->prepare("INSERT INTO visitors (rfid, name, password, type, program, reason) VALUES (?, ?, '', ?, ?, ?)");
    $stmt->bind_param('sssss', $rfid, $name, $type, $program, $reason);
    $stmt->execute();

    echo json_encode(['success' => true, 'name' => $name, 'program' => $program, 'reason' => $reason, 'type' => $type]);
    exit;
}
// ── ADMIN LOGIN ──
if ($action === 'admin_login') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    if ($admin) {
    $_SESSION['admin'] = $admin['email'];
    echo json_encode(['success' => true, 'email' => $admin['email']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Username not found. Must be a registered @neu.admin.lib account.']);
}
    exit;
}

// ── ALL BELOW REQUIRE ADMIN SESSION ──
if (empty($_SESSION['admin'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// ── GET VISITORS ──
if ($action === 'get_visitors') {
    $db = getDB();
    $result = $db->query("SELECT id, rfid, name, type, program, reason, timestamp FROM visitors ORDER BY timestamp DESC");
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
    exit;
}

// ── GET BLOCKED ──
if ($action === 'get_blocked') {
    $db = getDB();
    $result = $db->query("SELECT * FROM blocked_users ORDER BY blocked_at DESC");
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
    exit;
}

// ── BLOCK VISITOR ──
if ($action === 'block') {
    $rfid   = trim($_POST['rfid'] ?? '');
    $reason = trim($_POST['reason'] ?? 'Unspecified');
    if (!$rfid) { echo json_encode(['success' => false]); exit; }
    $db = getDB();
    $stmt = $db->prepare("INSERT IGNORE INTO blocked_users (rfid, reason) VALUES (?, ?)");
    $stmt->bind_param('ss', $rfid, $reason);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

// ── UNBLOCK ──
if ($action === 'unblock') {
    $rfid = trim($_POST['rfid'] ?? '');
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM blocked_users WHERE rfid = ?");
    $stmt->bind_param('s', $rfid);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

// ── GET RECORDS ──
if ($action === 'get_records') {
    $db = getDB();
    $result = $db->query("SELECT * FROM student_records ORDER BY name ASC");
    $rows = [];
    while ($row = $result->fetch_assoc()) $rows[] = $row;
    echo json_encode($rows);
    exit;
}

// ── ADD RECORD ──
if ($action === 'add_record') {
    $rfid    = trim($_POST['rfid'] ?? '');
    $name    = trim($_POST['name'] ?? '');
    $type    = trim($_POST['type'] ?? 'Student');
    $program = trim($_POST['program'] ?? '');
    $year    = trim($_POST['year'] ?? '');
    if (!$rfid || !$name || !$program) { echo json_encode(['success' => false, 'message' => 'Fill all required fields.']); exit; }
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO student_records (rfid, name, type, program, year_level) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE name=VALUES(name), type=VALUES(type), program=VALUES(program), year_level=VALUES(year_level)");
    $stmt->bind_param('sssss', $rfid, $name, $type, $program, $year);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

// ── DELETE RECORD ──
if ($action === 'delete_record') {
    $id = (int)($_POST['id'] ?? 0);
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM student_records WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

// ── ADMIN LOGOUT ──
if ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
?>