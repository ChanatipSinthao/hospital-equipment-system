<?php
session_start();
include '../config/db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username = '$username' AND status = 1";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 1) {

    $user = mysqli_fetch_assoc($result);

    if (password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../user/dashboard.php");
        }
        exit;

    } else {
        $_SESSION['login_error'] = "รหัสผ่านไม่ถูกต้อง";
        header("Location: login.php");
        exit;
    }

} else {
    $_SESSION['login_error'] = "ไม่พบผู้ใช้งาน หรือบัญชีถูกปิดใช้งาน";
    header("Location: login.php");
    exit;
}
