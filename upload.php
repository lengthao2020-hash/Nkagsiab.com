<?php
require 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user = current_user($mysqli);
$err = '';
$success = '';

$back_url = $_SERVER['HTTP_REFERER'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media'])) {
    $content = $_POST['content'] ?? '';

    $f = $_FILES['media'];
    if ($f['error'] === 0) {
        $allowed_images = ['image/jpeg', 'image/png', 'image/gif'];
        $allowed_videos = ['video/mp4', 'video/quicktime'];
        $media_type = in_array($f['type'], $allowed_images) ? 'image' : (in_array($f['type'], $allowed_videos) ? 'video' : '');

        if (!$media_type) {
            $err = 'Unsupported file type.';
        } else {
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }

            $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
            $filename = uniqid('m_') . '.' . $ext;
            $dest = __DIR__ . '/uploads/' . $filename;

            if (move_uploaded_file($f['tmp_name'], $dest)) {
                $media_path = 'uploads/' . $filename;

                $stmt = $mysqli->prepare("INSERT INTO posts (user_id, content, media_path, media_type) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('isss', $user['id'], $content, $media_path, $media_type);
                if ($stmt->execute()) {
                    $success = 'Upload successful!';
                } else {
                    $err = 'Database error: ' . $mysqli->error;
                }
            } else {
                $err = 'Upload failed.';
            }
        }
    } else {
        $err = 'File upload error.';
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Upload Post - Nkagsiab.com</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="p-4 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white flex justify-between items-center shadow">
        <a href="index.php" class="flex items-center">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQeVrlE-A2R9iD9VMT80e237lFDjVM4EqAYdCnFTehXVfIwT3vPOSErgzzYxCE_NLFV16I&usqp=CAU" class="w-12 h-12 rounded-full mr-3" alt="Nkagsiab">
            <div>
                <div class="font-bold text-lg">Nkagsiab.com</div>
                <div class="text-xs">Coj peb hmoob thoob ntuj los koom ua ke</div>
            </div>
        </a>
        <div>
            <a href="index.php" class="hover:underline">Back to Dashboard</a>
        </div>
    </nav>

    <!-- Upload Form -->
    <main class="flex-grow flex items-center justify-center p-6">
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-gray-200 relative">

            <!-- ✅ ปุ่มยกเลิก (X) -->
            <a href="<?= htmlspecialchars($back_url) ?>"
                class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-xl font-bold"
                title="Cancel & Go Back">❌</a>

            <h2 class="text-2xl font-bold mb-6 text-center text-indigo-600">Upload a Post</h2>

            <?php if ($err): ?>
                <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($err) ?></div>
            <?php elseif ($success): ?>
                <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1" for="content">Post Content</label>
                    <textarea name="content" id="content" rows="4" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="media">Upload Image/Video</label>
                    <input type="file" name="media" id="media" class="w-full p-2 border rounded-lg" required>
                </div>
                <button type="submit" class="w-full py-3 bg-indigo-500 text-white rounded-lg font-semibold hover:bg-indigo-600 transition duration-200">Upload</button>
            </form>

            <!-- ✅ ปุ่มยกเลิกแบบปุ่มใหญ่ด้านล่าง -->
            <a href="<?= htmlspecialchars($back_url) ?>"
                class="block mt-4 text-center py-3 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-100 transition">
                ❌ Cancel & Go Back
            </a>

        </div>
    </main>

    <footer class="p-4 text-center text-gray-500 text-xs">
        &copy; <?= date('Y') ?> Nkagsiab.com. All rights reserved.
    </footer>

</body>

</html>