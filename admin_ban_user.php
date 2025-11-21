<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$id = intval($_GET['id']);
$res = $mysqli->query("SELECT status FROM users WHERE id=$id");
$row = $res->fetch_assoc();

if ($row['status'] == 'active') {
    $mysqli->query("UPDATE users SET status='banned' WHERE id=$id");
} else {
    $mysqli->query("UPDATE users SET status='active' WHERE id=$id");
}

header("Location: admin.php");
exit;
