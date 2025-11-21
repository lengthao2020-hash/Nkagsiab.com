<?php
require 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user = current_user($mysqli);
$err = '';
$success = '';

$post_id = intval($_GET['id'] ?? 0);

// ดึงโพสต์
$stmt = $mysqli->prepare("SELECT * FROM posts WHERE id=? AND user_id=? LIMIT 1");
$stmt->bind_param('ii', $post_id, $user['id']);
$stmt->execute();
$res = $stmt->get_result();
$post = $res->fetch_assoc();

if (!$post) {
    die("Post not found or you don't have permission.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';

    // อัปโหลดไฟล์ใหม่ถ้ามี
    $media_path = $post['media_path'];
    $media_type = $post['media_type'];
    if (isset($_FILES['media']) && $_FILES['media']['error'] === 0) {
        $f = $_FILES['media'];
        $allowed_images = ['image/jpeg', 'image/png', 'image/gif'];
        $allowed_videos = ['video/mp4', 'video/quicktime'];

        $type = in_array($f['type'], $allowed_images) ? 'image' : (in_array($f['type'], $allowed_videos) ? 'video' : '');
        if ($type) {
            if (!is_dir('uploads')) mkdir('uploads', 0755, true);
            $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
            $filename = uniqid('m_') . '.' . $ext;
            $dest = __DIR__ . '/uploads/' . $filename;
            if (move_uploaded_file($f['tmp_name'], $dest)) {
                $media_path = 'uploads/' . $filename;
                $media_type = $type;
            }
        }
    }

    $stmt = $mysqli->prepare("UPDATE posts SET content=?, media_path=?, media_type=? WHERE id=? AND user_id=?");
    $stmt->bind_param('sssii', $content, $media_path, $media_type, $post_id, $user['id']);
    if ($stmt->execute()) {
        $success = "Post updated successfully!";
        $post['content'] = $content;
        $post['media_path'] = $media_path;
        $post['media_type'] = $media_type;
    } else {
        $err = "Failed to update post.";
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Edit Post</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto bg-white p-6 rounded shadow">

        <h2 class="text-xl font-bold mb-4">Edit Post</h2>

        <?php if ($err): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($err) ?></div>
        <?php elseif ($success): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label>Content</label>
                <textarea name="content" class="w-full border p-2 rounded"><?= htmlspecialchars($post['content']) ?></textarea>
            </div>
            <div>
                <label>Upload Image/Video</label>
                <input type="file" name="media" class="w-full border p-2 rounded">
                <?php if ($post['media_path']): ?>
                    <?php if ($post['media_type'] == 'image'): ?>
                        <img src="<?= htmlspecialchars($post['media_path']) ?>" class="mt-2 max-h-48 rounded">
                    <?php else: ?>
                        <video src="<?= htmlspecialchars($post['media_path']) ?>" controls class="mt-2 max-h-48 w-full rounded"></video>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <button type="submit" class="bg-indigo-500 text-white py-2 px-4 rounded hover:bg-indigo-600">Update Post</button>
        </form>

        <a href="profile.php" class="text-blue-500 hover:underline mt-4 inline-block">Back to Profile</a>

    </div>
</body>

</html>