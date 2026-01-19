<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ดึงข้อมูลกลุ่มประเภท ===== */
$sql = "
SELECT
    t.id AS type_id,
    t.name AS type_name,
    t.created_at,

    COUNT(DISTINCT c.id) AS brand_count,
    COUNT(ei.id) AS total_qty,
    SUM(CASE WHEN ei.status = 1 THEN 1 ELSE 0 END) AS available_qty,
    COALESCE(SUM(ei.price), 0) AS total_price,

    GROUP_CONCAT(DISTINCT c.brand ORDER BY c.brand SEPARATOR ', ') AS brands

FROM equipment_types t
LEFT JOIN equipment_categories c ON c.type_id = t.id
LEFT JOIN equipments e ON e.category_id = c.id
LEFT JOIN equipment_items ei ON ei.equipment_id = e.id

GROUP BY t.id
ORDER BY t.id DESC
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

<a href="../types/add.php">➕ เพิ่มประเภท</a>

<br><br>

<table border="1" cellpadding="10" width="100%">
<tr>
    <th>ID</th>
    <th>รายละเอียดกลุ่มประเภท</th>
    <th>วันที่เพิ่ม</th>
    <th>จำนวน</th>
    <th>ราคารวม</th>
    <th>จัดการ</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($result)) : ?>
<tr>

    <td><?= (int)$row['type_id']; ?></td>

    <!-- รายละเอียด -->
    <td>
        <strong><?= htmlspecialchars($row['type_name']); ?></strong><br>
        ยี่ห้อ:
        <?= $row['brands']
            ? htmlspecialchars($row['brands'])
            : '<span style="color:#999;">ยังไม่มียี่ห้อ</span>'; ?>
    </td>

    <!-- วันที่เพิ่ม -->
    <td align="center">
        <?= date('d/m/Y', strtotime($row['created_at'])); ?>
    </td>

    <!-- จำนวน -->
    <td align="center">
        ยี่ห้อ: <?= (int)$row['brand_count']; ?><br>
        อุปกรณ์:
        <?= (int)$row['available_qty']; ?> /
        <?= (int)$row['total_qty']; ?>
    </td>

    <!-- ราคารวม -->
    <td align="right">
        <?= number_format((float)$row['total_price'], 2); ?> บาท
    </td>

    <!-- จัดการ -->
    <td align="center">
        <a href="../types/edit.php?id=<?= (int)$row['type_id']; ?>">✏️ แก้ไข</a> |
        <a href="view.php?type_id=<?= (int)$row['type_id']; ?>">🔍 ดูรายละเอียด</a>
    </td>

</tr>
<?php endwhile; ?>
</table>

</body>
</html>
