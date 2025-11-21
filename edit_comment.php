<?php
require 'config.php';
if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user = current_user($mysqli);
$comment_id = intval($_POST['comment_id'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

if (!$comment_id || !$comment) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// check if user owns the comment
$stmt = $mysqli->prepare("SELECT user_id FROM post_comments WHERE id=?");
$stmt->bind_param('i', $comment_id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row || $row['user_id'] != $user['id']) {
    echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
    exit;
}

// update comment
$stmt = $mysqli->prepare("UPDATE post_comments SET comment=? WHERE id=?");
$stmt->bind_param('si', $comment, $comment_id);
if ($stmt->execute()) {
    echo json_encode(['status' => 'ok', 'comment' => $comment]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
