<?php
require 'config.php';

if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$user = current_user($mysqli);
$err = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $country  = trim($_POST['country'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $profile_filename = 'uploads/profile_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $profile_filename)) {
            if (!empty($user['profile_image']) && file_exists($user['profile_image'])) unlink($user['profile_image']);
            $stmt = $mysqli->prepare("UPDATE users SET profile_image=? WHERE id=?");
            $stmt->bind_param('si', $profile_filename, $user['id']);
            $stmt->execute();
            $user['profile_image'] = $profile_filename;
        }
    }

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $cover_filename = 'uploads/cover_' . uniqid() . '.' . $ext;
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_filename)) {
            if (!empty($user['cover_image']) && file_exists($user['cover_image'])) unlink($user['cover_image']);
            $stmt = $mysqli->prepare("UPDATE users SET cover_image=? WHERE id=?");
            $stmt->bind_param('si', $cover_filename, $user['id']);
            $stmt->execute();
            $user['cover_image'] = $cover_filename;
        }
    }

    $stmt = $mysqli->prepare("UPDATE users SET username=?, country=?, phone=? WHERE id=?");
    $stmt->bind_param('sssi', $username, $country, $phone, $user['id']);
    if ($stmt->execute()) {
        $success = 'Profile updated successfully!';
        $user['username'] = $username;
        $user['country'] = $country;
        $user['phone'] = $phone;
    } else {
        $err = 'Failed to update profile: ' . $mysqli->error;
    }
}

// Fetch user's posts
$sql = "SELECT p.*, u.profile_image AS user_profile_image, u.username AS post_username,
               (SELECT COUNT(*) FROM post_likes WHERE post_id=p.id) AS like_count,
               (SELECT COUNT(*) FROM post_comments WHERE post_id=p.id) AS comment_count
        FROM posts p
        JOIN users u ON p.user_id=u.id
        WHERE p.user_id=?
        ORDER BY p.created_at DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$res = $stmt->get_result();
$posts = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Profile - Nkagsiab.com</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" />
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: #fff;
            max-width: 500px;
            width: 90%;
            max-height: 80%;
            overflow-y: auto;
            padding: 1rem;
            border-radius: 0.5rem;
        }

        .close-btn {
            float: right;
            cursor: pointer;
            font-weight: bold;
        }

        .comment-item {
            border-bottom: 1px solid #ddd;
            padding: 0.5rem 0;
        }
    </style>
</head>

<body class="bg-gray-50">

    <nav class="p-4 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white flex justify-between items-center shadow">
        <a href="index.php" class="flex items-center">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQeVrlE-A2R9iD9VMT80e237lFDjVM4EqAYdCnFTehXVfIwT3vPOSErgzzYxCE_NLFV16I&usqp=CAU" class="w-12 h-12 rounded-full mr-3" alt="Nkagsiab">
            <div>
                <div class="font-bold text-lg">Nkagsiab.com</div>
                <div class="text-xs">Coj peb hmoob thoob ntuj los koom ua ke</div>
            </div>
        </a>
        <div class="flex items-center gap-2">
            <a href="logout.php" class="hover:underline">Logout</a>
            <a href="upload.php" class="bg-white text-green-700 px-3 py-2 rounded hover:bg-gray-100">Upload</a>
        </div>
    </nav>

    <main class="container mx-auto p-6">
        <?php if ($err): ?>
            <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($err) ?></div>
        <?php elseif ($success): ?>
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Profile Info -->
        <div class="bg-white rounded shadow p-6 mb-6">
            <?php if (!empty($user['cover_image'])): ?>
                <img src="<?= htmlspecialchars($user['cover_image']) ?>" class="w-full h-40 object-cover rounded mb-4">
            <?php endif; ?>
            <div class="flex items-center mb-4">
                <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" class="w-24 h-24 rounded-full mr-4">
                <?php endif; ?>
                <div>
                    <div class="font-bold text-xl"><?= htmlspecialchars($user['username'] ?? '') ?></div>
                    <div class="text-sm text-gray-500">
                        <?= htmlspecialchars($user['country'] ?? '') ?> |
                        <?= htmlspecialchars($user['phone'] ?? '') ?> |
                        Joined: <?= htmlspecialchars($user['created_at'] ?? '') ?>
                    </div>
                </div>
            </div>

            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="update_profile" value="1">
                <div>
                    <label class="block text-sm font-medium mb-1">Username</label>
                    <input type="text" name="username" class="w-full p-2 border rounded" value="<?= htmlspecialchars($user['username'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Country</label>
                    <input type="text" name="country" class="w-full p-2 border rounded" value="<?= htmlspecialchars($user['country'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" name="phone" class="w-full p-2 border rounded" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Profile Image</label>
                    <input type="file" name="profile_image" class="w-full p-2 border rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Cover Image</label>
                    <input type="file" name="cover_image" class="w-full p-2 border rounded">
                </div>
                <button type="submit" class="w-full py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">Update Profile</button>
            </form>
        </div>

        <h2 class="text-xl font-semibold mb-4">Your Posts</h2>
        <?php if (!$posts): ?>
            <div class="bg-white p-4 rounded shadow">No posts yet.</div>
        <?php else: ?>
            <?php foreach ($posts as $p): ?>
                <?php
                $stmt = $mysqli->prepare("SELECT c.*, u.username, u.profile_image FROM post_comments c JOIN users u ON c.user_id=u.id WHERE c.post_id=? ORDER BY c.created_at ASC");
                $stmt->bind_param('i', $p['id']);
                $stmt->execute();
                $res_comments = $stmt->get_result();
                $comments = $res_comments ? $res_comments->fetch_all(MYSQLI_ASSOC) : [];
                ?>
                <div class="bg-white p-4 rounded shadow mb-4" id="post-<?= $p['id'] ?>">
                    <div class="flex items-center mb-2">
                        <?php if (!empty($p['user_profile_image'])): ?>
                            <img src="<?= htmlspecialchars($p['user_profile_image']) ?>" class="w-10 h-10 rounded-full mr-2">
                        <?php endif; ?>
                        <div class="text-sm font-semibold"><?= htmlspecialchars($p['post_username']) ?></div>
                    </div>

                    <p><?= htmlspecialchars($p['content']) ?></p>

                    <?php if (!empty($p['media_path'])): ?>
                        <?php if ($p['media_type'] == 'image'): ?>
                            <a href="<?= htmlspecialchars($p['media_path']) ?>" class="glightbox" data-type="image">
                                <img src="<?= htmlspecialchars($p['media_path']) ?>" class="mt-2 rounded max-h-96">
                            </a>
                        <?php elseif ($p['media_type'] == 'video'): ?>
                            <video class="mt-2 rounded max-h-96 w-full" controls>
                                <source src="<?= htmlspecialchars($p['media_path']) ?>" type="video/mp4">
                            </video>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="mt-4 flex items-center gap-4 text-sm text-gray-700">
                        <button onclick="likePost(<?= $p['id'] ?>)" id="like-btn-<?= $p['id'] ?>" class="hover:text-blue-600">
                            👍 Like (<span id="like-count-<?= $p['id'] ?>"><?= $p['like_count'] ?></span>)
                        </button>
                        <button onclick="openCommentModal(<?= $p['id'] ?>)" class="hover:text-green-600">
                            💬 Comment (<span id="comment-count-<?= $p['id'] ?>"><?= $p['comment_count'] ?></span>)
                        </button>
                        <button onclick="copyShare(<?= $p['id'] ?>)" class="hover:text-purple-600">🔗 Share</button>
                        <button onclick="reportPost(<?= $p['id'] ?>)" class="hover:text-red-600">🚩 Report</button>
                    </div>

                    <div class="mt-2 flex gap-2">
                        <a href="edit_post.php?id=<?= $p['id'] ?>" class="px-2 py-1 bg-yellow-400 text-white rounded">Edit</a>
                        <a href="delete_post.php?id=<?= $p['id'] ?>" class="px-2 py-1 bg-red-500 text-white rounded" onclick="return confirm('Are you sure to delete this post?')">Delete</a>
                    </div>

                    <div id="modal-<?= $p['id'] ?>" class="modal flex">
                        <div class="modal-content">
                            <span class="close-btn" onclick="closeModal(<?= $p['id'] ?>)">&times;</span>
                            <h3 class="font-semibold mb-2">Comments</h3>
                            <div id="modal-comments-<?= $p['id'] ?>">
                                <?php foreach ($comments as $c): ?>
                                    <div class="comment-item flex items-start gap-2" data-comment-id="<?= $c['id'] ?>">
                                        <?php if (!empty($c['profile_image'])): ?>
                                            <img src="<?= htmlspecialchars($c['profile_image']) ?>" class="w-8 h-8 rounded-full">
                                        <?php endif; ?>
                                        <div class="flex-1">
                                            <div class="text-sm font-semibold"><?= htmlspecialchars($c['username']) ?> <span class="text-xs text-gray-400"><?= date('Y-m-d H:i', strtotime($c['created_at'])) ?></span></div>
                                            <div class="text-sm text-gray-700"><?= htmlspecialchars($c['comment']) ?></div>
                                        </div>
                                        <?php if ($c['user_id'] == $user['id']): ?>
                                            <div class="flex gap-1">
                                                <button onclick="editComment(<?= $c['id'] ?>, <?= $p['id'] ?>, this)" class="text-blue-500 text-xs">Edit</button>
                                                <button onclick="deleteComment(<?= $c['id'] ?>, <?= $p['id'] ?>, this)" class="text-red-500 text-xs">Delete</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <form method="post" class="mt-2" onsubmit="return postComment(event, <?= $p['id'] ?>)">
                                <textarea name="comment" class="border rounded w-full p-2" placeholder="Write a comment..."></textarea>
                                <button class="px-3 py-1 bg-blue-500 text-white rounded mt-1">Post</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>

    <footer class="p-4 text-center text-gray-500 text-xs">
        &copy; <?= date('Y') ?> Nkagsiab.com. All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
    <script>
        const lightbox = GLightbox({
            selector: '.glightbox',
            touchNavigation: true,
            loop: true
        });

        function copyShare(id) {
            const url = window.location.origin + "/post.php?id=" + id;
            navigator.clipboard.writeText(url);
            alert("Link copied: " + url);
        }

        function openCommentModal(id) {
            document.getElementById('modal-' + id).style.display = 'flex';
        }

        function closeModal(id) {
            document.getElementById('modal-' + id).style.display = 'none';
        }

        function likePost(id) {
            fetch('post_like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'post_id=' + id
                })
                .then(r => r.json()).then(res => {
                    if (res.status === 'ok') {
                        document.getElementById('like-count-' + id).textContent = res.like_count;
                        const btn = document.getElementById('like-btn-' + id);
                        btn.textContent = (res.is_liked ? '👍 Unlike (' : '👍 Like (') + res.like_count + ')';
                    } else alert(res.message);
                });
        }

        function reportPost(id) {
            fetch('post_report.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'post_id=' + id
                })
                .then(r => r.text()).then(res => alert("Post reported"));
        }

        function postComment(event, postId) {
            event.preventDefault();
            const form = event.target;
            const comment = form.comment.value.trim();
            if (!comment) return;
            fetch('post_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'post_id=' + postId + '&comment=' + encodeURIComponent(comment)
            }).then(r => r.json()).then(res => {
                if (res.status === 'ok') {
                    const container = document.getElementById('modal-comments-' + postId);
                    const div = document.createElement('div');
                    div.className = 'comment-item flex items-start gap-2';
                    div.setAttribute('data-comment-id', res.comment_id);
                    div.innerHTML = `
                <img src="${res.profile_image}" class="w-8 h-8 rounded-full">
                <div class="flex-1">
                    <div class="text-sm font-semibold">${res.username} <span class="text-xs text-gray-400">${res.created_at}</span></div>
                    <div class="text-sm text-gray-700">${res.comment}</div>
                </div>
                <div class="flex gap-1">
                    <button onclick="editComment(${res.comment_id}, ${postId}, this)" class="text-blue-500 text-xs">Edit</button>
                    <button onclick="deleteComment(${res.comment_id}, ${postId}, this)" class="text-red-500 text-xs">Delete</button>
                </div>`;
                    container.appendChild(div);
                    form.comment.value = '';
                    document.getElementById('comment-count-' + postId).textContent = res.comment_count;
                } else alert(res.message);
            });
        }

        function deleteComment(commentId, postId, btn) {
            if (!confirm('Delete this comment?')) return;
            fetch('delete_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'comment_id=' + commentId
                })
                .then(r => r.json()).then(res => {
                    if (res.status === 'ok') {
                        btn.closest('.comment-item').remove();
                        const countEl = document.getElementById('comment-count-' + postId);
                        countEl.textContent = parseInt(countEl.textContent) - 1;
                    } else alert(res.message);
                });
        }

        function editComment(commentId, postId, btn) {
            const item = btn.closest('.comment-item');
            const textEl = item.querySelector('.text-sm.text-gray-700');
            const oldText = textEl.textContent;
            const textarea = document.createElement('textarea');
            textarea.value = oldText;
            textarea.className = 'border rounded w-full p-1 text-sm';
            textEl.replaceWith(textarea);
            btn.textContent = 'Save';
            btn.onclick = function() {
                const newComment = textarea.value.trim();
                if (!newComment) return alert('Comment cannot be empty');
                fetch('edit_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'comment_id=' + commentId + '&comment=' + encodeURIComponent(newComment)
                }).then(r => r.json()).then(res => {
                    if (res.status === 'ok') {
                        const div = document.createElement('div');
                        div.className = 'text-sm text-gray-700';
                        div.textContent = res.comment;
                        textarea.replaceWith(div);
                        btn.textContent = 'Edit';
                        btn.onclick = function() {
                            editComment(commentId, postId, btn);
                        }
                    } else alert(res.message);
                });
            }
        }
    </script>

</body>

</html>