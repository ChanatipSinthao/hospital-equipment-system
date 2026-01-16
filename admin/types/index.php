<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ดึงรายการประเภทหลัก ===== */
$sql = "
SELECT 
    t.id,
    t.name,
    t.created_at,
    COUNT(c.id) AS category_count
FROM equipment_types t
LEFT JOIN equipment_categories c ON c.type_id = t.id
GROUP BY t.id
ORDER BY t.id DESC
";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ประเภทอุปกรณ์หลัก</title>
</head>
<body>

<h2>ประเภทอุปกรณ์หลัก</h2>

<a href="add.php">➕ เพิ่มประเภทหลัก</a>

<br><br>

<table border="1" cellpadding="10" width="60%">
    <tr>
        <th>ID</th>
        <th>ชื่อประเภท</th>
        <th>วันที่เพิ่ม</th>
        <th>จำนวนกลุ่มประเภท</th>
        <th>จัดการ</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
    <tr>

        <td><?= $row['id']; ?></td>

        <td>
            <strong><?= htmlspecialchars($row['name']); ?></strong>
        </td>

        <td>
            <?= date('d/m/Y', strtotime($row['created_at'])); ?>
        </td>

        <td align="center">
            <?= $row['category_count']; ?>
        </td>

        <td align="center">
            <a href="edit.php?id=<?= $row['id']; ?>">✏️ แก้ไข</a>
        </td>

    </tr>
    <?php endwhile; ?>

</table>

</body>
</html>
