<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจ type_id ===== */
if (!isset($_GET['type_id']) || !is_numeric($_GET['type_id'])) {
    header("Location: index.php");
    exit;
}

$type_id = (int)$_GET['type_id'];

/* ===== ข้อมูลประเภท ===== */
$typeResult = mysqli_query($conn, "
SELECT id, name, created_at
FROM equipment_types
WHERE id = $type_id
LIMIT 1
");

$type = mysqli_fetch_assoc($typeResult);
if (!$type) {
    header("Location: index.php");
    exit;
}

/* ===== ยี่ห้อในประเภทนี้ ===== */
$result = mysqli_query($conn, "
SELECT
    c.id AS brand_id,
    c.brand,
    c.created_at,

    COUNT(ei.id) AS total_qty,
    SUM(CASE WHEN ei.status = 1 THEN 1 ELSE 0 END) AS available_qty,
    COALESCE(SUM(ei.price), 0) AS total_price

FROM equipment_categories c
LEFT JOIN equipments e ON e.category_id = c.id
LEFT JOIN equipment_items ei ON ei.equipment_id = e.id
WHERE c.type_id = $type_id
GROUP BY c.id
ORDER BY c.brand
");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ยี่ห้ออุปกรณ์ในประเภท <?= htmlspecialchars($type['name']); ?></title>
</head>
<body>

<h2>ยี่ห้ออุปกรณ์</h2>

<p>
    <strong>ประเภท:</strong> <?= htmlspecialchars($type['name']); ?><br>
    <strong>วันที่เพิ่มประเภท:</strong>
    <?= date('d/m/Y', strtotime($type['created_at'])); ?>
</p>

<a href="../brand/add.php?type_id=<?= (int)$type_id; ?>">➕ เพิ่มยี่ห้อ</a> |
<a href="index.php">⬅ กลับหน้ากลุ่มประเภท</a>

<br><br>

<table border="1" cellpadding="10" width="100%">
<tr>
    <th>ID</th>
    <th>ยี่ห้อ</th>
    <th>วันที่เพิ่มยี่ห้อ</th>
    <th>จำนวนอุปกรณ์</th>
    <th>ราคารวม</th>
    <th>จัดการ</th>
</tr>

<?php if (mysqli_num_rows($result) === 0): ?>
<tr>
    <td colspan="6" align="center" style="color:red;">
        ยังไม่มียี่ห้อในประเภทนี้
    </td>
</tr>
<?php endif; ?>

<?php while ($row = mysqli_fetch_assoc($result)) : ?>
<tr>

    <td align="center"><?= (int)$row['brand_id']; ?></td>

    <td>
        <strong><?= htmlspecialchars($row['brand']); ?></strong>
    </td>

    <td align="center">
        <?= date('d/m/Y', strtotime($row['created_at'])); ?>
    </td>

    <td align="center">
        <?= (int)$row['available_qty']; ?> /
        <?= (int)$row['total_qty']; ?> เครื่อง
    </td>

    <td align="right">
        <?= number_format((float)$row['total_price'], 2); ?> บาท
    </td>

    <td align="center">
        <a href="../equipments/index.php?type_id=<?= (int)$type_id; ?>&category_id=<?= (int)$row['brand_id']; ?>">
            🔍 ดูรายละเอียด
        </a>
        |
        <a href="../brand/edit.php?id=<?= (int)$row['brand_id']; ?>">
            ✏️ แก้ไข
        </a>
    </td>

</tr>
<?php endwhile; ?>
</table>

</body>
</html>
