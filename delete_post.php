<?php
require 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user = current_user($mysqli);
$post_id = intval($_GET['id'] ?? 0);

// ตรวจสอบว่าเป็นเจ้าของโพสต์
$stmt = $mysqli->prepare("SELECT media_path FROM posts WHERE id=? AND user_id=? LIMIT 1");
$stmt->bind_param('ii', $post_id, $user['id']);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();

if (!$post) {
    die("Post not found or you don't have permission.");
}

// ลบไฟล์สื่อถ้ามี
if ($post['media_path'] && file_exists($post['media_path'])) {
    unlink($post['media_path']);
}

// ลบโพสต์จากฐานข้อมูล
$stmt = $mysqli->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
$stmt->bind_param('ii', $post_id, $user['id']);
$stmt->execute();

header('Location: profile.php');
exit;
