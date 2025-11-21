<?php
require 'config.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

$user = current_user($mysqli);

$post_id = intval($_POST['post_id']);

$stmt = $mysqli->prepare("INSERT INTO post_reports (post_id, user_id, reason) VALUES (?, ?, 'Reported')");
$stmt->bind_param("ii", $post_id, $user['id']);
$stmt->execute();

header("Location: profile.php");
exit;
