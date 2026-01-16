<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

$result = mysqli_query($conn, "SELECT id, username, role FROM users");
$sql = "SELECT * FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายชื่อผู้ใช้</title>
</head>
<body>

<h2>รายชื่อผู้ใช้</h2>

<a href="add.php">➕ เพิ่มผู้ใช้</a>

<?php if (isset($_GET['success'])): ?>
    <p style="color:green;">เพิ่มผู้ใช้เรียบร้อย</p>
<?php endif; ?>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>รูป</th>
        <th>รายละเอียดผู้ใช้งาน</th>
        <th>วันที่ลงทะเบียน</th>
        <th>สถานะ</th>
        <th>จัดการ</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
    <tr>
        <td><?= $row['id']; ?></td>

        <td>
            <?php if ($row['profile_image']) : ?>
                <img src="/asset_management/assets/uploads/users/<?= $row['profile_image']; ?>"
                     width="50" height="50" style="object-fit:cover;">
            <?php else : ?>
                -
            <?php endif; ?>
        </td>

        <td>
            <strong><?= $row['username']; ?></strong><br>
            ตำแหน่ง: <?= $row['position']; ?><br>
            แผนก: <?= $row['department']; ?><br>
            โทร: <?= $row['phone']; ?>
        </td>

        <td>
            <?= date('d/m/Y', strtotime($row['created_at'])); ?>
        </td>

        <td>
            <?= $row['status'] == 1 ? 'ใช้งาน' : 'ปิดใช้งาน'; ?>
        </td>

        <td>
            <a href="edit.php?id=<?= $row['id']; ?>">แก้ไข</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>



</body>
</html>
