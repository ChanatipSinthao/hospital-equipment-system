<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ดึงคำขอเบิก ===== */
$sql = "
SELECT
    b.id,
    b.borrow_qty,
    b.borrow_date,
    b.status,
    b.borrower_name,
    b.department,

    e.asset_code,
    e.name AS equipment_name,
    e.image,
    e.price,

    c.brand,
    t.name AS type_name

FROM equipment_borrow b
JOIN equipments e ON b.equipment_id = e.id
JOIN equipment_categories c ON e.category_id = c.id
JOIN equipment_types t ON c.type_id = t.id

ORDER BY b.borrow_date DESC
";

$result = mysqli_query($conn, $sql);

/* ===== แสดงสถานะ ===== */
function borrowStatus($status) {
    return match ((int)$status) {
        0 => '<span style="color:orange;">รออนุมัติ</span>',
        1 => '<span style="color:green;">อนุมัติแล้ว</span>',
        2 => '<span style="color:red;">ไม่อนุมัติ</span>',
        default => '-',
    };
}
?>

<h2>คำขอเบิกอุปกรณ์</h2>

<table border="1" cellpadding="10" width="100%">
    <tr>
        <th>ID</th>
        <th>รูป</th>
        <th>รายละเอียดคำขอ</th>
        <th>วันที่ขอ</th>
        <th>จำนวน</th>
        <th>ราคารวม</th>
        <th>สถานะ</th>
        <th>จัดการ</th>
    </tr>

<?php if (mysqli_num_rows($result) === 0) : ?>
<tr>
    <td colspan="8" align="center" style="color:red;">
        ยังไม่มีคำขอเบิกอุปกรณ์
    </td>
</tr>
<?php endif; ?>

<?php while ($row = mysqli_fetch_assoc($result)) : ?>
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

    <!-- รายละเอียดคำขอ -->
    <td>
        <strong><?= htmlspecialchars($row['equipment_name']); ?></strong><br>
        เลขครุภัณฑ์: <?= htmlspecialchars($row['asset_code']); ?><br>
        ประเภท: <?= htmlspecialchars($row['type_name']); ?><br>
        ยี่ห้อ: <?= htmlspecialchars($row['brand']); ?><br>
        ผู้ขอ: <?= htmlspecialchars($row['borrower_name']); ?><br>
        แผนก: <?= htmlspecialchars($row['department']); ?>
    </td>

    <!-- วันที่ขอ -->
    <td>
        <?= date('d/m/Y H:i', strtotime($row['borrow_date'])); ?>
    </td>

    <!-- จำนวน -->
    <td align="center">
        <?= (int)$row['borrow_qty']; ?>
    </td>

    <!-- ราคารวม -->
    <td align="right">
        <?= number_format($row['price'] * $row['borrow_qty'], 2); ?> บาท
    </td>

    <!-- สถานะ -->
    <td align="center">
        <?= borrowStatus($row['status']); ?>
    </td>

    <!-- จัดการ -->
    <td align="center">
        <?php if ($_SESSION['role'] === 'admin' && (int)$row['status'] === 0) : ?>
            <a href="approve.php?id=<?= $row['id']; ?>">อนุมัติ</a>
        <?php else : ?>
            -
        <?php endif; ?>
    </td>

</tr>
<?php endwhile; ?>
</table>
