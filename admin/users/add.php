<?php
// Session Guard
include '../../includes/admin_guard.php';

if (isset($_POST['save'])) {

    include '../../config/db.php';

    $username   = $_POST['username'];
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $position   = $_POST['position'];
    $department = $_POST['department'];
    $phone      = $_POST['phone'];
    $role       = 'user';
    $status     = 1;

    $image_name = null;
    if (!empty($_FILES['profile_image']['name'])) {
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $image_name = 'user_' . time() . '.' . $ext;

        move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            '../../assets/uploads/users/' . $image_name
        );
    }

    $sql = "INSERT INTO users 
        (username, password, role, position, department, phone, profile_image, status)
        VALUES 
        ('$username', '$password', '$role', '$position', '$department', '$phone', '$image_name', '$status')";

    mysqli_query($conn, $sql);

    header("Location: index.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มผู้ใช้</title>
</head>
<body>

<h2>เพิ่มผู้ใช้ใหม่</h2>

<form method="post" enctype="multipart/form-data">

    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>ตำแหน่งงาน</label>
    <input type="text" name="position" required>

    <label>แผนก</label>
    <input type="text" name="department" required>

    <label>เบอร์โทร</label>
    <input type="text" name="phone" required>

    <label>รูปโปรไฟล์</label>
    <input type="file" name="profile_image" accept="image/*">

    <button type="submit" name="save">บันทึก</button>
</form>



</body>
</html>
