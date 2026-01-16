<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ไม่พบข้อมูลอุปกรณ์');
}

$id = (int)$_GET['id'];

/* ===== ข้อมูลรุ่น ===== */
$result_equipment = mysqli_query($conn, "
SELECT
    e.id,
    e.name,
    e.model,
    e.created_at,
    c.brand,
    t.name AS type_name,
    COALESCE(SUM(ei.price), 0) AS total_price
FROM equipments e
LEFT JOIN equipment_items ei ON ei.equipment_id = e.id
LEFT JOIN equipment_categories c ON e.category_id = c.id
LEFT JOIN equipment_types t ON c.type_id = t.id
WHERE e.id = $id
GROUP BY e.id
LIMIT 1
");

$equipment = mysqli_fetch_assoc($result_equipment);
if (!$equipment) {
    die('ไม่พบข้อมูลอุปกรณ์');
}

/* ===== รายการเครื่อง ===== */
$result_items = mysqli_query($conn, "
SELECT
    ei.id,
    ei.asset_number,
    ei.serial_number,
    ei.image,
    ei.note,
    ei.price,
    ei.status,
    ei.created_at
FROM equipment_items ei
WHERE ei.equipment_id = $id
ORDER BY ei.id ASC
");

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
    <title>รายละเอียดอุปกรณ์</title>
</head>
<body>

<h2>รายละเอียดอุปกรณ์</h2>

<!-- 🔹 ข้อมูลรุ่น -->
<table border="1" cellpadding="10" width="70%">
    <tr>
        <th width="30%">ชื่ออุปกรณ์</th>
        <td><?= htmlspecialchars($equipment['name']); ?></td>
    </tr>
    <tr>
        <th>ประเภท</th>
        <td><?= htmlspecialchars($equipment['type_name'] ?? '-'); ?></td>
    </tr>
    <tr>
        <th>ยี่ห้อ</th>
        <td><?= htmlspecialchars($equipment['brand'] ?? '-'); ?></td>
    </tr>
    <tr>
        <th>รุ่น</th>
        <td><?= htmlspecialchars($equipment['model'] ?? '-'); ?></td>
    </tr>
    <tr>
        <th>วันที่เพิ่ม</th>
        <td><?= date('d/m/Y H:i', strtotime($equipment['created_at'])); ?></td>
    </tr>
    <tr>
        <th>ราคารวมทั้งหมด</th>
        <td><?= number_format((float)$equipment['total_price'], 2); ?> บาท</td>
    </tr>
</table>

<br>

<!-- 🔹 รายการเครื่อง -->
<h3>รายการครุภัณฑ์ (รายเครื่อง)</h3>

<a href="../equipment_items/add.php?equipment_id=<?= (int)$equipment['id']; ?>">
    ➕ เพิ่มอุปกรณ์
</a>

<table border="1" cellpadding="10" width="100%">
    <tr>
        <tr>
            <th>#</th>
            <th>เลขครุภัณฑ์</th>
            <th>รูป</th>
            <th>Serial Number</th>
            <th>ราคา (บาท)</th>
            <th>หมายเหตุ</th>
            <th>วันที่เพิ่ม</th>
            <th>สถานะ</th>
            <th>จัดการ</th>
        </tr>
    </tr>

    <?php $i = 1; while ($item = mysqli_fetch_assoc($result_items)) : ?>
    <tr>

        <td align="center"><?= $i++; ?></td>

        <td><?= htmlspecialchars($item['asset_number']); ?></td>

        <td align="center">
            <?php if (!empty($item['image'])) : ?>
                <img src="/asset_management/assets/uploads/equipment_items/<?= htmlspecialchars($item['image']); ?>"
                    width="60" height="60" style="object-fit:cover;">
            <?php else : ?>
                -
            <?php endif; ?>
        </td>

        <td><?= htmlspecialchars($item['serial_number'] ?? '-'); ?></td>

        <td align="right">
            <?= number_format((float)$item['price'], 2); ?>
        </td>

        <td>
            <?= !empty($item['note'])
                ? nl2br(htmlspecialchars($item['note']))
                : '-'; ?>
        </td>

        <td align="center">
            <?= date('d/m/Y H:i', strtotime($item['created_at'])); ?>
        </td>

        <td><?= equipmentStatus((int)$item['status']); ?></td>

        <td align="center">
            <a href="../equipment_items/edit.php?id=<?= (int)$item['id']; ?>">✏️ แก้ไข</a>
        </td>

    </tr>
    <?php endwhile; ?>

</table>


<br>
<a href="index.php">⬅️ กลับหน้ารายการ</a>

</body>
</html>
