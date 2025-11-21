<?php
require 'config.php';
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user = current_user($mysqli);
$post_id = intval($_POST['post_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if (!$post_id || !$comment) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO post_comments (post_id,user_id,comment,created_at) VALUES (?,?,?,NOW())");
$stmt->bind_param('iis', $post_id, $user['id'], $comment);
if ($stmt->execute()) {
    $comment_id = $mysqli->insert_id;
    // update comment count
    $res = $mysqli->query("SELECT COUNT(*) as cnt FROM post_comments WHERE post_id=$post_id");
    $cnt = $res->fetch_assoc()['cnt'];
    echo json_encode([
        'status' => 'ok',
        'comment_id' => $comment_id,
        'username' => $user['username'],
        'profile_image' => $user['profile_image'] ?: 'default.png',
        'comment' => $comment,
        'created_at' => date('Y-m-d H:i'),
        'comment_count' => $cnt
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
