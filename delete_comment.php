<?php
require 'config.php';
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user = current_user($mysqli);
$comment_id = intval($_POST['comment_id'] ?? 0);
if (!$comment_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// check if user owns the comment
$stmt = $mysqli->prepare("SELECT user_id, post_id FROM post_comments WHERE id=?");
$stmt->bind_param('i', $comment_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row || $row['user_id'] != $user['id']) {
    echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
    exit;
}

// delete comment
$stmt = $mysqli->prepare("DELETE FROM post_comments WHERE id=?");
$stmt->bind_param('i', $comment_id);
if ($stmt->execute()) {
    // update comment count
    $res2 = $mysqli->query("SELECT COUNT(*) as cnt FROM post_comments WHERE post_id=" . $row['post_id']);
    $cnt = $res2->fetch_assoc()['cnt'];
    echo json_encode(['status' => 'ok', 'comment_count' => $cnt]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
