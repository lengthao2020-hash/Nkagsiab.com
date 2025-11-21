<?php
require 'config.php';
header('Content-Type: application/json');

if (!is_logged_in() || !isset($_POST['post_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user = current_user($mysqli);
$post_id = (int)$_POST['post_id'];

// ตรวจสอบว่า user เคยกด like หรือยัง
$stmt = $mysqli->prepare("SELECT id FROM post_likes WHERE post_id=? AND user_id=?");
$stmt->bind_param('ii', $post_id, $user['id']);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    // ถ้าเคย like แล้ว → ลบ (Unlike)
    $stmt = $mysqli->prepare("DELETE FROM post_likes WHERE post_id=? AND user_id=?");
    $stmt->bind_param('ii', $post_id, $user['id']);
    $stmt->execute();
    $is_liked = false;
} else {
    // ยังไม่เคย → เพิ่ม like
    $stmt = $mysqli->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $post_id, $user['id']);
    $stmt->execute();
    $is_liked = true;
}

// ดึงจำนวน like ปัจจุบัน
$stmt = $mysqli->prepare("SELECT COUNT(*) as like_count FROM post_likes WHERE post_id=?");
$stmt->bind_param('i', $post_id);
$stmt->execute();
$res_count = $stmt->get_result()->fetch_assoc();
$like_count = (int)$res_count['like_count'];

echo json_encode(['status' => 'ok', 'like_count' => $like_count, 'is_liked' => $is_liked]);
