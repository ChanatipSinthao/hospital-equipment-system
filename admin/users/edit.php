<?php
// ===== Guard + DB =====
include '../../includes/admin_guard.php';
include '../../config/db.php';

// ===== ตรวจ id =====
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// ===== ดึงข้อมูลผู้ใช้ =====
$sql = "SELECT * FROM users WHERE id = $id";
$user = mysqli_fetch_assoc(mysqli_query($conn, $sql));

// ===== Update =====
if (isset($_POST['update'])) {

    $username   = $_POST['username'];
    $position   = $_POST['position'];
    $department = $_POST['department'];
    $phone      = $_POST['phone'];
    $status     = $_POST['status'];

    $password_sql = '';
    if (!empty($_POST['new_password'])) {
        $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $password_sql = ", password = '$hashed_password'";
    }

    $image_sql = '';
    if (!empty($_FILES['profile_image']['name'])) {
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $image_name = 'user_' . time() . '.' . $ext;

        move_uploaded_file(
            $_FILES['profile_image']['tmp_name'],
            '../../assets/uploads/users/' . $image_name
        );

        $image_sql = ", profile_image = '$image_name'";
    }

    $sql = "UPDATE users SET
        username   = '$username',
        position   = '$position',
        department = '$department',
        phone      = '$phone',
        status     = '$status'
        $password_sql
        $image_sql
        WHERE id = $id";

    mysqli_query($conn, $sql);

    header("Location: index.php");
    exit;
}

?>

<?php include '../../layouts/admin/header.php'; ?>

<div class="admin-layout">

    <?php include '../../layouts/admin/sidebar.php'; ?>

    <div class="admin-main">

        <?php include '../../layouts/admin/navbar.php'; ?>

        <!-- ===== Content ===== -->
        <main class="admin-content">

            <h2>แก้ไขผู้ใช้งาน</h2>

            <form method="post" enctype="multipart/form-data">

                <label>Username</label>
                <input type="text" name="username"
                       value="<?= $user['username']; ?>" required>
                
                <label>รหัสผ่านใหม่</label>
                <input type="password" name="new_password">

                <label>ตำแหน่งงาน</label>
                <input type="text" name="position"
                       value="<?= $user['position']; ?>">

                <label>แผนก</label>
                <input type="text" name="department"
                       value="<?= $user['department']; ?>">

                <label>เบอร์โทร</label>
                <input type="text" name="phone"
                       value="<?= $user['phone']; ?>">

                <label>สถานะ</label>
                <select name="status">
                    <option value="1" <?= $user['status']==1?'selected':''; ?>>ใช้งาน</option>
                    <option value="0" <?= $user['status']==0?'selected':''; ?>>ปิดใช้งาน</option>
                </select>

                <label>รูปใหม่</label>
                <input type="file" name="profile_image">

                <button type="submit" name="update">
                    บันทึกการแก้ไข
                </button>
            </form>

        </main>

    </div>
</div>

<?php include '../../layouts/admin/footer.php'; ?>
