<?php
require 'config.php';

$user = current_user($mysqli);

// ดึงการตั้งค่าเว็บไซต์
$settings = [];
$res_settings = $mysqli->query("SELECT * FROM site_settings LIMIT 1");
if ($res_settings && $row = $res_settings->fetch_assoc()) {
    $settings = $row;
}

// รับค่าค้นหา
$search = $_GET['search'] ?? '';
$search_sql = "";

if (!empty($search)) {
    $search_safe = $mysqli->real_escape_string($search);
    $search_sql = "WHERE p.content LIKE '%$search_safe%' OR u.username LIKE '%$search_safe%'";
}

// ดึงโพสต์ (รองรับ Search)
$res = $mysqli->query("SELECT p.*, u.username, u.profile_image,
    (SELECT COUNT(*) FROM post_likes l WHERE l.post_id=p.id) AS like_count,
    (SELECT COUNT(*) FROM post_comments c WHERE c.post_id=p.id) AS comment_count
    FROM posts p
    JOIN users u ON p.user_id=u.id
    $search_sql
    ORDER BY p.created_at DESC");

$posts = [];
if ($res) {
    while ($r = $res->fetch_assoc()) $posts[] = $r;
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($settings['site_name'] ?? 'Nkagsiab.com') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50">

    <!-- Navbar -->
    <nav class="p-4 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white flex justify-between items-center shadow">
        <a href="index.php" class="flex items-center">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQeVrlE-A2R9iD9VMT80e237lFDjVM4EqAYdCnFTehXVfIwT3vPOSErgzzYxCE_NLFV16I&usqp=CAU" class="w-12 h-12 rounded-full mr-3">
            <div>
                <div class="font-bold text-lg"><?= htmlspecialchars($settings['site_name'] ?? 'Nkagsiab.com') ?></div>
                <div class="text-xs"><?= htmlspecialchars($settings['welcome_message'] ?? '') ?></div>
            </div>
        </a>

        <div class="flex items-center gap-4">
            <?php if (is_logged_in()): ?>
                <a href="profile.php">My Profile</a>
                <a href="logout.php">Logout</a>
                <a href="upload.php" class="bg-white text-green-700 px-3 py-2 rounded">Upload</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php">New Account</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container mx-auto p-6">

        <h2 class="text-xl font-semibold mb-4">Recent posts</h2>

        <!-- ✅ Search Box -->
        <form method="get" class="mb-6 flex">
            <input type="text"
                name="search"
                id="searchBox"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="🔍 Search posts or users..."
                class="border p-2 rounded w-full">
            <button class="ml-2 px-4 py-2 bg-blue-500 text-white rounded">Search</button>
        </form>

        <?php if (!$posts): ?>
            <div class="bg-white p-4 rounded shadow">No posts found.</div>
        <?php else: ?>
            <?php foreach ($posts as $p): ?>
                <div class="bg-white p-4 rounded shadow mb-4 post-item">
                    <div class="font-semibold"><?= htmlspecialchars($p['username']) ?></div>
                    <div class="text-gray-500 text-xs"><?= $p['created_at'] ?></div>
                    <p class="post-text mt-2"><?= htmlspecialchars($p['content']) ?></p>

                    <?php if ($p['media_path'] && $p['media_type'] == 'image'): ?>
                        <img src="<?= htmlspecialchars($p['media_path']) ?>" class="mt-2 rounded max-h-96">
                    <?php elseif ($p['media_path'] && $p['media_type'] == 'video'): ?>
                        <video src="<?= htmlspecialchars($p['media_path']) ?>" controls class="mt-2 rounded max-h-96 w-full"></video>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>

    <!-- ✅ Live Search Script (เหมือน Facebook) -->
    <script>
        const searchBox = document.getElementById('searchBox');
        const posts = document.querySelectorAll('.post-item');

        searchBox.addEventListener('keyup', function() {
            const keyword = this.value.toLowerCase();

            posts.forEach(post => {
                const text = post.innerText.toLowerCase();
                if (text.includes(keyword)) {
                    post.style.display = 'block';
                } else {
                    post.style.display = 'none';
                }
            });
        });
    </script>

</body>

</html>