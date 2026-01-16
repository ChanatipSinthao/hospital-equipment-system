<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจ id ===== */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$equipment_id = (int)$_GET['id'];

/* ===== ดึงข้อมูลกลุ่มอุปกรณ์ ===== */
$sql = "
SELECT 
    e.*,
    t.name AS type_name,
    c.brand
FROM equipments e
LEFT JOIN equipment_categories c ON e.category_id = c.id
LEFT JOIN equipment_types t ON c.type_id = t.id
WHERE e.id = $equipment_id
";
$equipment = mysqli_fetch_assoc(mysqli_query($conn, $sql));

if (!$equipment) {
    header("Location: index.php");
    exit;
}

/* ===== ดึงเครื่องย่อย ===== */
$itemSql = "
SELECT *
FROM equipment_items
WHERE equipment_id = $equipment_id
ORDER BY id ASC
";
$itemResult = mysqli_query($conn, $itemSql);

/* ===== แปลงสถานะ ===== */
function itemStatus(int $status): string {
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
    <title>รายละเอียดอุปกรณ์</title>
</head>
<body>

<a href="index.php">⬅ กลับหน้ารายการอุปกรณ์</a>

<h2>รายละเอียดอุปกรณ์</h2>

<!-- ===== ข้อมูลกลุ่ม ===== -->
<table border="1" cellpadding="10" width="100%">
<tr>
    <td width="150">รูปกลุ่ม</td>
    <td>
        <?php if (!empty($equipment['image'])) : ?>
            <img src="/asset_management/assets/uploads/equipments/<?= htmlspecialchars($equipment['image']); ?>"
                 width="120" style="object-fit:cover;">
        <?php else : ?>
            -
        <?php endif; ?>
    </td>
</tr>
<tr>
    <td>ชื่ออุปกรณ์</td>
    <td><strong><?= htmlspecialchars($equipment['name']); ?></strong></td>
</tr>
<tr>
    <td>ประเภท</td>
    <td><?= htmlspecialchars($equipment['type_name'] ?? '-'); ?></td>
</tr>
<tr>
    <td>ยี่ห้อ</td>
    <td><?= htmlspecialchars($equipment['brand'] ?? '-'); ?></td>
</tr>
<tr>
    <td>รุ่น</td>
    <td><?= htmlspecialchars($equipment['model'] ?? '-'); ?></td>
</tr>
<tr>
    <td>จำนวน</td>
    <td>
        พร้อมใช้งาน <?= (int)$equipment['available_qty']; ?> /
        ทั้งหมด <?= (int)$equipment['total_qty']; ?>
    </td>
</tr>
<tr>
    <td>ราคา</td>
    <td><?= number_format((float)$equipment['price'], 2); ?> บาท</td>
</tr>
<tr>
    <td>หมายเหตุ (กลุ่ม)</td>
    <td><?= nl2br(htmlspecialchars($equipment['note'] ?? '-')); ?></td>
</tr>
</table>

<br>

<!-- ===== ปุ่มเพิ่มเครื่องย่อย ===== -->
<a href="../equipment_items/add.php?equipment_id=<?= $equipment_id; ?>">
    ➕ เพิ่มเครื่องย่อย
</a>

<br><br>

<!-- ===== ตารางเครื่องย่อย ===== -->
<h3>รายการเครื่องย่อย</h3>

<table border="1" cellpadding="10" width="100%">
<tr>
    <th>ID</th>
    <th>รูป</th>
    <th>Serial Number</th>
    <th>หมายเหตุ</th>
    <th>สถานะ</th>
    <th>จัดการ</th>
</tr>

<?php if (mysqli_num_rows($itemResult) === 0) : ?>
<tr>
    <td colspan="6" align="center" style="color:red;">
        ยังไม่มีเครื่องย่อยในกลุ่มนี้
    </td>
</tr>
<?php endif; ?>

<?php while ($item = mysqli_fetch_assoc($itemResult)) : ?>
<tr>

    <td><?= (int)$item['id']; ?></td>

    <td align="center">
        <?php if (!empty($item['image'])) : ?>
            <img src="/asset_management/assets/uploads/equipment_items/<?= htmlspecialchars($item['image']); ?>"
                 width="60" height="60" style="object-fit:cover;">
        <?php else : ?>
            -
        <?php endif; ?>
    </td>

    <td><?= htmlspecialchars($item['serial_number'] ?? '-'); ?></td>

    <td><?= nl2br(htmlspecialchars($item['note'] ?? '-')); ?></td>

    <td align="center"><?= itemStatus((int)$item['status']); ?></td>

    <td align="center">
        <a href="../equipment_items/edit.php?id=<?= (int)$item['id']; ?>">✏️ แก้ไข</a>
    </td>

</tr>
<?php endwhile; ?>
</table>

</body>
</html>
