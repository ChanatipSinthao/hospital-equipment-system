<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ดึงข้อมูลกลุ่มประเภท ===== */
$sql = "
SELECT
    c.id,
    c.brand,
    c.image,
    c.created_at,

    t.name AS type_name,

    COUNT(e.id) AS equipment_count,
    COALESCE(SUM(e.total_qty), 0) AS total_qty,
    COALESCE(SUM(e.available_qty), 0) AS total_available,
    COALESCE(SUM(e.price * e.total_qty), 0) AS total_price

FROM equipment_categories c
JOIN equipment_types t ON c.type_id = t.id
LEFT JOIN equipments e ON e.category_id = c.id

GROUP BY c.id
ORDER BY c.id DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>กลุ่มประเภทอุปกรณ์</title>
</head>
<body>

<h2>กลุ่มประเภทอุปกรณ์</h2>

<a href="add.php">➕ เพิ่มกลุ่มประเภท</a> |
<a href="../types/index.php">📁 จัดการประเภทหลัก</a>

<br><br>

<table border="1" cellpadding="10" width="100%">
    <tr>
        <th>ID</th>
        <th>รูป</th>
        <th>รายละเอียดกลุ่มประเภท</th>
        <th>วันที่เพิ่ม</th>
        <th>จำนวนอุปกรณ์</th>
        <th>ราคารวม</th>
        <th>จัดการ</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
    <tr>

        <!-- ID -->
        <td><?= $row['id']; ?></td>

        <!-- รูป -->
        <td align="center">
            <?php if (!empty($row['image'])) : ?>
                <img src="/asset_management/assets/uploads/categories/<?= $row['image']; ?>"
                     width="60" height="60" style="object-fit:cover;">
            <?php else : ?>
                -
            <?php endif; ?>
        </td>

        <!-- รายละเอียด -->
        <td>
            <strong><?= htmlspecialchars($row['type_name']); ?></strong><br>
            ยี่ห้อ: <?= htmlspecialchars($row['brand']); ?>
        </td>

        <!-- วันที่เพิ่ม -->
        <td>
            <?= date('d/m/Y', strtotime($row['created_at'])); ?>
        </td>

        <!-- จำนวน -->
        <td align="center">
            <?= (int)$row['total_available']; ?> /
            <?= (int)$row['total_qty']; ?>
            <br>
            <small>(<?= (int)$row['equipment_count']; ?> รายการ)</small>
        </td>

        <!-- ราคารวม -->
        <td align="right">
            <?= number_format((float)$row['total_price'], 2); ?> บาท
        </td>

        <!-- จัดการ -->
        <td align="center">
            <a href="view.php?id=<?= $row['id']; ?>">🔍 ดูอุปกรณ์</a> |
            <a href="edit.php?id=<?= $row['id']; ?>">✏️ แก้ไข</a>
        </td>

    </tr>
    <?php endwhile; ?>

</table>

</body>
</html>
