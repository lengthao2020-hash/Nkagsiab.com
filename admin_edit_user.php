<?php
require 'config.php';

if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $mysqli->real_escape_string($_POST['username']);
    $email = $mysqli->real_escape_string($_POST['email']);

    $mysqli->query("UPDATE users SET username='$username', email='$email' WHERE id=$id");
    header("Location: admin.php");
    exit;
}

$res = $mysqli->query("SELECT * FROM users WHERE id=$id");
$user = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Edit user</title>
</head>

<body>
    <h2>Edit User</h2>

    <form method="post">
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br><br>
        <button type="submit">Save</button>
    </form>

    <a href="admin.php">Back</a>

</body>

</html>