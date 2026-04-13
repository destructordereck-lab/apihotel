<?php
// test_db.php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$host = getenv('DB_HOST') ?: 'sql100.infinityfree.com';
$user = getenv('DB_USER') ?: 'if0_41629492';
$pass = getenv('DB_PASS') ?: 'Dereckxz11';
$db   = getenv('DB_NAME') ?: 'if0_41629492_497467';

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo "CONNECT_ERR: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit;
}

$result = $mysqli->query("SHOW TABLES");
if (!$result) {
    echo "SHOW_TABLES_ERR: " . $mysqli->error;
    exit;
}

$tables = [];
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}

echo "OK\n";
echo "Host: $host\n";
echo "DB: $db\n";
echo "Tables: " . implode(', ', $tables) . "\n";
$mysqli->close();
