<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

$username = $_POST['username'];
$password = $_POST['password'];
$role     = $_POST['role'];
$status   = 1;

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password, role, status)
        VALUES ('$username', '$hashed_password', '$role', '$status')";

mysqli_query($conn, $sql);

// บันทึกเสร็จ → กลับหน้า list
header("Location: index.php?success=1");
exit;
