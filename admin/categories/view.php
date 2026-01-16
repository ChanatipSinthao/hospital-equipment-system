<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจ id กลุ่มประเภท ===== */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$category_id = (int)$_GET['id'];

/* ===== ดึงข้อมูลกลุ่มประเภท ===== */
$catResult = mysqli_query(
    $conn,
    "SELECT 
        c.id,
        c.brand,
        c.image,
        t.name AS type_name
     FROM equipment_categories c
     JOIN equipment_types t ON c.type_id = t.id
     WHERE c.id = $category_id"
);

$category = mysqli_fetch_assoc($catResult);

if (!$category) {
    header("Location: index.php");
    exit;
}

/* ===== ดึงอุปกรณ์ในกลุ่มนี้ ===== */
$equipResult = mysqli_query(
    $conn,
    "SELECT
        e.id,
        e.asset_code,
        e.name,
        e.model,
        e.image,
        e.price,
        e.total_qty,
        e.available_qty,
        e.status,
        e.created_at
     FROM equipments e
     WHERE e.category_id = $category_id
     ORDER BY e.id DESC"
);

/* ===== แปลงสถานะ ===== */
function equipmentStatus(int $status): string {
    return match ($status) {
        1 => 'พร้อมใช้งาน',
        2 => 'ชำรุด',
        0 => 'จำหน่าย',
        default => '-',
    };
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อุปกรณ์ในกลุ่มประเภท</title>
</head>
<body>

<h2>อุปกรณ์ในกลุ่มประเภท</h2>

<p>
    <strong>ประเภท:</strong> <?= htmlspecialchars($category['type_name']); ?><br>
    <strong>ยี่ห้อ:</strong> <?= htmlspecialchars($category['brand']); ?>
</p>

<a href="../equipments/add.php">➕ เพิ่มอุปกรณ์</a> |
<a href="index.php">⬅ กลับหน้ากลุ่มประเภท</a>

<br><br>

<table border="1" cellpadding="10" width="100%">
    <tr>
        <th>ID</th>
        <th>รูป</th>
        <th>รายละเอียดอุปกรณ์</th>
        <th>วันที่เพิ่ม</th>
        <th>จำนวน</th>
        <th>สถานะ</th>
        <th>ราคา</th>
        <th>จัดการ</th>
    </tr>

    <?php if (mysqli_num_rows($equipResult) === 0) : ?>
        <tr>
            <td colspan="8" align="center" style="color:red;">
                ยังไม่มีอุปกรณ์ในกลุ่มประเภทนี้
            </td>
        </tr>
    <?php endif; ?>

    <?php while ($row = mysqli_fetch_assoc($equipResult)) : ?>
    <tr>

        <!-- ID -->
        <td><?= $row['id']; ?></td>

        <!-- รูป -->
        <td align="center">
            <?php if (!empty($row['image'])) : ?>
                <img src="/asset_management/assets/uploads/equipments/<?= $row['image']; ?>"
                     width="60" height="60" style="object-fit:cover;">
            <?php else : ?>
                -
            <?php endif; ?>
        </td>

        <!-- รายละเอียด -->
        <td>
            <strong><?= htmlspecialchars($row['name']); ?></strong><br>
            รุ่น: <?= htmlspecialchars($row['model'] ?? '-'); ?><br>
            รหัสครุภัณฑ์: <?= htmlspecialchars($row['asset_code']); ?>
        </td>

        <!-- วันที่เพิ่ม -->
        <td>
            <?= date('d/m/Y', strtotime($row['created_at'])); ?>
        </td>

        <!-- จำนวน -->
        <td align="center">
            <?= (int)$row['available_qty']; ?> /
            <?= (int)$row['total_qty']; ?>
        </td>

        <!-- สถานะ -->
        <td align="center">
            <?= equipmentStatus((int)$row['status']); ?>
        </td>

        <!-- ราคา -->
        <td align="right">
            <?= number_format((float)$row['price'], 2); ?> บาท
        </td>

        <!-- จัดการ -->
        <td align="center">
            <a href="../equipments/edit.php?id=<?= $row['id']; ?>">✏️ แก้ไข</a>
        </td>

    </tr>
    <?php endwhile; ?>

</table>

</body>
</html>
