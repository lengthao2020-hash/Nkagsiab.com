<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$id = intval($_GET['id']);

// ลบทุกอย่างที่เกี่ยวกับ user
$mysqli->query("DELETE FROM posts WHERE user_id=$id");
$mysqli->query("DELETE FROM comments WHERE user_id=$id");
$mysqli->query("DELETE FROM likes WHERE user_id=$id");
$mysqli->query("DELETE FROM users WHERE id=$id");

header("Location: admin.php");
exit;
