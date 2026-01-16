<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ดึงรายการอุปกรณ์ ===== */
$sql = "
SELECT 
    e.id,
    e.name,
    e.model,
    e.image,
    e.price,
    e.total_qty,
    e.available_qty,
    e.status,
    e.note,
    e.created_at,

    t.name AS type_name,
    c.brand

FROM equipments e
LEFT JOIN equipment_categories c ON e.category_id = c.id
LEFT JOIN equipment_types t ON c.type_id = t.id
ORDER BY e.id DESC
";




$result = mysqli_query($conn, $sql);

/* ===== แปลงสถานะอุปกรณ์ ===== */
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
    <title>รายการอุปกรณ์</title>
</head>
<body>

<h2>รายการอุปกรณ์</h2>

<a href="add.php">➕ เพิ่มอุปกรณ์</a>
<a href="../categories/index.php">📂 จัดการกลุ่มอุปกรณ์</a

<br><br>

<table border="1" cellpadding="10" width="100%">
<tr>
    <th>ID</th>
    <th>รูป</th>
    <th>รายละเอียดอุปกรณ์</th>
    <th>จำนวน</th>
    <th>หมายเหตุ</th>
    <th>ราคารวม</th>
    <th>จัดการ</th>
</tr>

<?php while ($row = mysqli_fetch_assoc($result)) : ?>
<tr>

    <td><?= (int)$row['id']; ?></td>

    <!-- รูปรวมของกลุ่ม -->
    <td align="center">
        <?php if (!empty($row['image'])) : ?>
            <img src="/asset_management/assets/uploads/equipments/<?= htmlspecialchars($row['image']); ?>"
                 width="60" height="60" style="object-fit:cover;">
        <?php else : ?>
            -
        <?php endif; ?>
    </td>

    <!-- รายละเอียด -->
    <td>
        <strong><?= htmlspecialchars($row['name']); ?></strong><br>
        ประเภท: <?= htmlspecialchars($row['type_name'] ?? '-'); ?>
        ยี่ห้อ: <?= htmlspecialchars($row['brand'] ?? '-'); ?>
        รุ่น: <?= htmlspecialchars($row['model'] ?? '-'); ?>
    </td>

    <!-- จำนวน -->
    <td align="center">
        <?= (int)$row['available_qty']; ?> /
        <?= (int)$row['total_qty']; ?>
    </td>

    <!-- ⭐ หมายเหตุ -->
    <td>
        <?php if (!empty($row['note'])) : ?>
            <?= nl2br(htmlspecialchars($row['note'])); ?>
        <?php else : ?>
            -
        <?php endif; ?>
    </td>

    <!-- ราคารวม -->
    <td align="right">
        <?= number_format($row['price'] * $row['total_qty'], 2); ?> บาท
    </td>

    <!-- จัดการ -->
    <td align="center">
        <a href="view.php?id=<?= (int)$row['id']; ?>">🔍 ดูรายละเอียด</a> |
        <a href="edit.php?id=<?= (int)$row['id']; ?>">✏️ แก้ไข</a>
    </td>

</tr>
<?php endwhile; ?>
</table>

</body>
</html>
