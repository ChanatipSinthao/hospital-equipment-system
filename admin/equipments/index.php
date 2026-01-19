<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

$type_id     = (int)($_GET['type_id'] ?? 0);
if ($type_id > 0) {
    $where[] = "t.id = $type_id";
}

$category_id = (int)($_GET['category_id'] ?? 0);
if ($category_id > 0) {
    $where[] = "e.category_id = $category_id";
}

/* ===== รับค่าค้นหา ===== */
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $q_safe = mysqli_real_escape_string($conn, $q);
    $where[] = "(
        e.name LIKE '%$q_safe%' OR
        e.model LIKE '%$q_safe%' OR
        t.name LIKE '%$q_safe%' OR
        c.brand LIKE '%$q_safe%'
    )";
}

$date_from = $_GET['date_from'] ?? '';
if ($date_from !== '') {
    $where[] = "DATE(e.created_at) >= '" . mysqli_real_escape_string($conn, $date_from) . "'";
}

$date_to = $_GET['date_to'] ?? '';
if ($date_to !== '') {
    $where[] = "DATE(e.created_at) <= '" . mysqli_real_escape_string($conn, $date_to) . "'";
}

$where_sql = '';
if (!empty($where)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

$params = [];
if (!empty($_GET['type_id'])) $params[] = 'type_id='.(int)$_GET['type_id'];
if (!empty($_GET['category_id'])) $params[] = 'category_id='.(int)$_GET['category_id'];
$query = !empty($params) ? '?'.implode('&', $params) : '';

/* ===== ดึงรายการอุปกรณ์ ===== */
$sql = "
SELECT
    e.id,
    e.name,
    e.model,
    e.image,
    e.status,
    e.note,
    e.created_at,

    c.brand,
    t.name AS type_name,

    COUNT(ei.id) AS total_qty,
    SUM(CASE WHEN ei.status = 1 THEN 1 ELSE 0 END) AS available_qty,
    COALESCE(SUM(ei.price), 0) AS total_price

FROM equipments e
LEFT JOIN equipment_categories c ON e.category_id = c.id
LEFT JOIN equipment_types t ON c.type_id = t.id
LEFT JOIN equipment_items ei ON ei.equipment_id = e.id

$where_sql

GROUP BY e.id
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

<a href="add.php<?= $query; ?>">➕ เพิ่มอุปกรณ์</a>
<a href="../categories/index.php">📂 จัดการกลุ่มอุปกรณ์</a>

<form method="get">

    <input type="text"
           name="q"
           placeholder="ค้นหา ชื่อ / รุ่น / ประเภท / ยี่ห้อ"
           value="<?= htmlspecialchars($_GET['q'] ?? ''); ?>">

    วันที่เพิ่ม:
    <input type="date" name="date_from"
           value="<?= htmlspecialchars($_GET['date_from'] ?? ''); ?>">

    ถึง
    <input type="date" name="date_to"
           value="<?= htmlspecialchars($_GET['date_to'] ?? ''); ?>">

    <button type="submit">🔍 ค้นหา</button>
    <a href="index.php">ล้าง</a>

</form>

<br>


<br><br>

<table border="1" cellpadding="10" width="100%">
<tr>
    <th>ID</th>
    <th>รูป</th>
    <th>รายละเอียดอุปกรณ์</th>
    <th>จำนวน</th>
    <th>หมายเหตุ</th>
    <th>ราคารวม</th>
    <th>วันที่เพิ่ม</th>
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
        <?= number_format((float)$row['total_price'], 2); ?> บาท
    </td>

    <td align="center">
    <?= date('d/m/Y', strtotime($row['created_at'])); ?>
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
