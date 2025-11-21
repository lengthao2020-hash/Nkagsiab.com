<?php
// config.php - เชื่อมต่อฐานข้อมูลและฟังก์ชันช่วย
session_start();

$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'nkagsiab_db'; // ใช้ฐานข้อมูลเดิม

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    die('DB connect error: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

function is_logged_in()
{
    return isset($_SESSION['user_id']);
}

function current_user($mysqli)
{
    if (!is_logged_in()) return null;
    $id = intval($_SESSION['user_id']);
    $res = $mysqli->query("SELECT id, username, email, country, phone FROM users WHERE id=$id LIMIT 1");
    if ($res && $res->num_rows) return $res->fetch_assoc();
    return null;
}
