<?php
require 'config.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? ''); // username หรือ email
    $password = $_POST['password'] ?? '';

    if ($login && $password) {
        // ตรวจสอบ username หรือ email
        $stmt = $mysqli->prepare("SELECT id, username, email, password FROM users WHERE username=? OR email=? LIMIT 1");
        if (!$stmt) die("Prepare failed: " . $mysqli->error);
        $stmt->bind_param('ss', $login, $login);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows) {
            $user = $res->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header('Location: index.php');
                exit;
            } else {
                $err = 'Incorrect password.';
            }
        } else {
            $err = 'Username or Email not found.';
        }
    } else {
        $err = 'Please enter your username/email and password.';
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Login - Nkagsiab.com</title>
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

    <!-- Login Form -->
    <main class="flex-grow flex items-center justify-center p-6">
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-gray-200">
            <h2 class="text-2xl font-bold mb-6 text-center text-indigo-600">Login to Your Account</h2>

            <?php if ($err): ?>
                <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-center"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1" for="login">Username or Email</label>
                    <input type="text" name="login" id="login" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="password">Password</label>
                    <input type="password" name="password" id="password" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none" required>
                </div>
                <button type="submit" class="w-full py-3 bg-indigo-500 text-white rounded-lg font-semibold hover:bg-indigo-600 transition duration-200">Login</button>
            </form>

            <div class="mt-6 text-center text-sm text-gray-600">
                Yog koj tsis tau muaj account? <a href="signup.php" class="text-indigo-500 font-medium hover:underline">Sign Up</a><br>
                <a href="forget-password.php" class="text-indigo-500 font-medium hover:underline">Forgot Password?</a>
            </div>
        </div>
    </main>

    <footer class="p-4 text-center text-gray-500 text-xs">
        &copy; <?= date('Y') ?> Nkagsiab.com. All rights reserved.
    </footer>

</body>

</html>