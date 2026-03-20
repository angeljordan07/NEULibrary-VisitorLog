<?php
define('DB_HOST', 'sql101.infinityfree.com');
define('DB_USER', 'if0_41435729');
define('DB_PASS', 'KR16ooxuBfTY5R');
define('DB_NAME', 'if0_41435729_neu_library');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'DB failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
?>