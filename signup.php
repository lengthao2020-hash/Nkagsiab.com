<?php
require 'config.php';

$err = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $country = trim($_POST['country'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($username && $email && $password) {

        // ตรวจสอบ username ซ้ำ
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
        if (!$stmt) die("Prepare failed: " . $mysqli->error);
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows > 0) {
            $err = 'Username หรือ Email นี้ถูกใช้งานแล้ว';
        } else {
            // hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $mysqli->prepare("INSERT INTO users (username,email,password,country,phone) VALUES (?,?,?,?,?)");
            if (!$stmt) die("Prepare failed: " . $mysqli->error);
            $stmt->bind_param('sssss', $username, $email, $hashed_password, $country, $phone);
            if ($stmt->execute()) {
                $success = 'สมัครสมาชิกสำเร็จ! คุณสามารถเข้าสู่ระบบได้ทันที';
            } else {
                $err = 'เกิดข้อผิดพลาดในการสมัครสมาชิก';
            }
        }
    } else {
        $err = 'กรุณากรอก Username, Email และ Password';
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Sign Up - Nkagsiab.com</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-to-r from-purple-100 via-pink-100 to-indigo-100 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="p-4 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 text-white flex justify-between items-center shadow">
        <a href="index.php" class="flex items-center">
            <img src="https://i.pinimg.com/1200x/64/3b/78/643b786ab30b8e3a8338bb41c6cbc79d.jpg" class="w-12 h-12 rounded-full mr-3" alt="Nkagsiab">
            <div>
                <div class="font-bold text-lg">Nkagsiab.com</div>
                <div class="text-xs">Connecting Hmong People</div>
            </div>
        </a>
    </nav>

    <!-- Signup Form -->
    <main class="flex-grow flex items-center justify-center p-6">
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-gray-200">
            <h2 class="text-2xl font-bold mb-6 text-center text-indigo-600">Create New Account</h2>

            <?php if ($err): ?>
                <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1" for="username">Username</label>
                    <input type="text" name="username" id="username" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="email">Email</label>
                    <input type="email" name="email" id="email" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="password">Password</label>
                    <input type="password" name="password" id="password" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="country">Country</label>
                    <input type="text" name="country" id="country" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1" for="phone">Phone Number</label>
                    <input type="text" name="phone" id="phone" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-3 bg-indigo-500 text-white rounded-lg font-semibold hover:bg-indigo-600 transition duration-200">Sign Up</button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-600">
                Already have an account? <a href="login.php" class="text-indigo-500 font-medium hover:underline">Login</a>
            </div>
        </div>
    </main>

    <footer class="p-4 text-center text-gray-500 text-xs">
        &copy; <?= date('Y') ?> Nkagsiab.com. All rights reserved.
    </footer>

</body>

</html>