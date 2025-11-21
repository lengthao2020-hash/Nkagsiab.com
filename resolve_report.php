<?php
include 'database.php';

if (isset($_GET['id'])) {
    $report_id = intval($_GET['id']);

    mysqli_query($conn, "UPDATE reports SET status='resolved' WHERE id=$report_id");
}

header("Location: admin.php");
exit();
