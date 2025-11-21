<?php
require 'config.php';

/* Admin key protection */
$admin_key = 'nkagsiab_admin_2025';

if (!isset($_GET['key']) || $_GET['key'] !== $admin_key) {
    die("<h2 style='text-align:center;margin-top:100px;'>Access Denied</h2>");
}

/* Handle POST updates for users */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['page'])) {
    if ($_GET['page'] === 'users' && isset($_GET['update'])) {
        $user_id = intval($_GET['update']);
        $username = $mysqli->real_escape_string($_POST['username']);
        $email = $mysqli->real_escape_string($_POST['email']);
        $role = $mysqli->real_escape_string($_POST['role']);
        $status = $mysqli->real_escape_string($_POST['status']);

        $mysqli->query("UPDATE users SET username='$username', email='$email', role='$role', status='$status' WHERE id=$user_id");
        header("Location: ?key=$admin_key&page=users");
        exit;
    }

    // Handle POST updates for settings
    if ($_GET['page'] === 'settings') {
        $site_name = $mysqli->real_escape_string($_POST['site_name']);
        $welcome_message = $mysqli->real_escape_string($_POST['welcome_message']);

        $check = $mysqli->query("SELECT id FROM settings LIMIT 1");
        if ($check->num_rows) {
            $mysqli->query("UPDATE settings SET site_name='$site_name', welcome_message='$welcome_message' WHERE id=1");
        } else {
            $mysqli->query("INSERT INTO settings (site_name, welcome_message) VALUES ('$site_name', '$welcome_message')");
        }

        $msg = "Settings updated successfully!";
    }
}

// Get search term if any
$search = trim($_GET['search'] ?? '');
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Admin Panel - Nkagsiab</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen">

    <!-- Header -->
    <div class="bg-gradient-to-r from-red-500 to-pink-500 p-5 text-white text-center">
        <h1 class="text-2xl font-bold">Admin Control Panel</h1>
        <p class="text-sm">Nkagsiab.com</p>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto p-6">

        <!-- Menu -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <a href="?key=<?= $admin_key ?>&page=users" class="bg-white p-4 rounded shadow text-center hover:bg-indigo-50">👥 Manage Users</a>
            <a href="?key=<?= $admin_key ?>&page=posts" class="bg-white p-4 rounded shadow text-center hover:bg-indigo-50">📝 Manage Posts</a>
            <a href="?key=<?= $admin_key ?>&page=settings" class="bg-white p-4 rounded shadow text-center hover:bg-indigo-50">⚙️ Website Settings</a>
        </div>

        <!-- Content Area -->
        <div class="bg-white p-6 rounded shadow">
            <?php
            $page = $_GET['page'] ?? 'home';

            if ($page === 'users') {
                // Handle Edit User
                if (isset($_GET['edit'])) {
                    $user_id = intval($_GET['edit']);
                    $stmt = $mysqli->prepare("SELECT id, username, email, role, status FROM users WHERE id=?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user_edit = $stmt->get_result()->fetch_assoc();
            ?>
                    <h2 class='text-xl font-bold mb-4'>Edit User: <?= htmlspecialchars($user_edit['username']) ?></h2>
                    <form method="post" action="?key=<?= $admin_key ?>&page=users&update=<?= $user_edit['id'] ?>" class="space-y-4">
                        <input type="text" name="username" value="<?= htmlspecialchars($user_edit['username']) ?>" class="border p-2 rounded w-full">
                        <input type="email" name="email" value="<?= htmlspecialchars($user_edit['email']) ?>" class="border p-2 rounded w-full">
                        <select name="role" class="border p-2 rounded w-full">
                            <option value="user" <?= $user_edit['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            <option value="admin" <?= $user_edit['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <select name="status" class="border p-2 rounded w-full">
                            <option value="active" <?= $user_edit['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= $user_edit['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
                    </form>
                <?php
                } elseif (isset($_GET['delete'])) {
                    $user_id = intval($_GET['delete']);
                    $mysqli->query("DELETE FROM users WHERE id=$user_id");
                    echo "<p class='text-green-600'>User deleted.</p>";
                } else {
                    // Search form
                ?>
                    <h2 class='text-xl font-bold mb-4'>Manage Users</h2>
                    <form method="get" class="mb-4 flex space-x-2">
                        <input type="hidden" name="key" value="<?= $admin_key ?>">
                        <input type="hidden" name="page" value="users">
                        <input type="text" name="search" placeholder="Search username or email" value="<?= htmlspecialchars($search) ?>" class="border p-2 rounded flex-grow">
                        <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded">Search</button>
                    </form>
                    <?php
                    $query = "SELECT id, username, email, role, status FROM users";
                    if ($search) {
                        $search_esc = $mysqli->real_escape_string($search);
                        $query .= " WHERE username LIKE '%$search_esc%' OR email LIKE '%$search_esc%'";
                    }
                    $query .= " ORDER BY id DESC";
                    $users = $mysqli->query($query);
                    ?>
                    <table class="w-full border border-gray-200 text-sm">
                        <tr class="bg-gray-100">
                            <th class="p-2 border">ID</th>
                            <th class="p-2 border">Username</th>
                            <th class="p-2 border">Email</th>
                            <th class="p-2 border">Role</th>
                            <th class="p-2 border">Status</th>
                            <th class="p-2 border">Action</th>
                        </tr>
                        <?php while ($u = $users->fetch_assoc()): ?>
                            <tr>
                                <td class="p-2 border"><?= $u['id'] ?></td>
                                <td class="p-2 border"><?= htmlspecialchars($u['username']) ?></td>
                                <td class="p-2 border"><?= htmlspecialchars($u['email']) ?></td>
                                <td class="p-2 border"><?= $u['role'] ?></td>
                                <td class="p-2 border"><?= $u['status'] ?></td>
                                <td class="p-2 border">
                                    <a href="?key=<?= $admin_key ?>&page=users&edit=<?= $u['id'] ?>" class="text-blue-500">Edit</a> |
                                    <a href="?key=<?= $admin_key ?>&page=users&delete=<?= $u['id'] ?>" class="text-red-500" onclick="return confirm('Delete this user?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php
                }
            } elseif ($page === 'posts') {
                if (isset($_GET['delete'])) {
                    $post_id = intval($_GET['delete']);
                    $mysqli->query("DELETE FROM posts WHERE id=$post_id");
                    echo "<p class='text-green-600'>Post deleted.</p>";
                }

                // Search form for posts
                ?>
                <h2 class='text-xl font-bold mb-4'>Manage Posts</h2>
                <form method="get" class="mb-4 flex space-x-2">
                    <input type="hidden" name="key" value="<?= $admin_key ?>">
                    <input type="hidden" name="page" value="posts">
                    <input type="text" name="search" placeholder="Search post content or username" value="<?= htmlspecialchars($search) ?>" class="border p-2 rounded flex-grow">
                    <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded">Search</button>
                </form>
                <?php
                $query = "SELECT p.id, p.user_id, p.content, u.username FROM posts p JOIN users u ON p.user_id=u.id";
                if ($search) {
                    $search_esc = $mysqli->real_escape_string($search);
                    $query .= " WHERE p.content LIKE '%$search_esc%' OR u.username LIKE '%$search_esc%'";
                }
                $query .= " ORDER BY p.id DESC";
                $posts = $mysqli->query($query);
                ?>
                <table class="w-full border border-gray-200 text-sm">
                    <tr class="bg-gray-100">
                        <th class="p-2 border">ID</th>
                        <th class="p-2 border">User</th>
                        <th class="p-2 border">Content</th>
                        <th class="p-2 border">Action</th>
                    </tr>
                    <?php while ($p = $posts->fetch_assoc()): ?>
                        <tr>
                            <td class="p-2 border"><?= $p['id'] ?></td>
                            <td class="p-2 border"><?= htmlspecialchars($p['username']) ?></td>
                            <td class="p-2 border"><?= htmlspecialchars(mb_strimwidth($p['content'], 0, 50, '...')) ?></td>
                            <td class="p-2 border">
                                <a href="?key=<?= $admin_key ?>&page=posts&delete=<?= $p['id'] ?>" class="text-red-500" onclick="return confirm('Delete this post?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php
            } elseif ($page === 'settings') {
                $settings = $mysqli->query("SELECT site_name, welcome_message FROM settings LIMIT 1")->fetch_assoc() ?? ['site_name' => 'Nkagsiab.com', 'welcome_message' => 'Welcome to Nkagsiab!'];
            ?>
                <h2 class='text-xl font-bold mb-4'>Website Settings</h2>
                <?php if (isset($msg)) echo "<p class='text-green-600 mb-4'>$msg</p>"; ?>
                <form method="post" class="space-y-4">
                    <div>
                        <label class="block font-medium mb-1">Site Name:</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" class="border p-2 rounded w-full">
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Welcome Message:</label>
                        <textarea name="welcome_message" class="border p-2 rounded w-full" rows="4"><?= htmlspecialchars($settings['welcome_message']) ?></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Settings</button>
                </form>
            <?php
            } else {
            ?>
                <h2 class="text-xl font-bold mb-2">Welcome Admin 👑</h2>
                <p>Select a menu to manage website.</p>
            <?php
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-xs text-gray-500 p-4">
        &copy; <?= date('Y') ?> Nkagsiab Admin Panel
    </div>

</body>

</html>