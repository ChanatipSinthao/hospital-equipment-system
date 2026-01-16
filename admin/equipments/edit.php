<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจ id ===== */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

/* ===== ดึงข้อมูลอุปกรณ์เดิม ===== */
$sql = "
SELECT 
    e.*,
    c.id AS category_id,
    c.brand,
    t.name AS type_name
FROM equipments e
LEFT JOIN equipment_categories c ON e.category_id = c.id
LEFT JOIN equipment_types t ON c.type_id = t.id
WHERE e.id = $id
";
$result = mysqli_query($conn, $sql);
$equipment = mysqli_fetch_assoc($result);

if (!$equipment) {
    header("Location: index.php");
    exit;
}

/* ===== ดึงกลุ่มประเภททั้งหมด ===== */
$catResult = mysqli_query(
    $conn,
    "SELECT c.id, t.name AS type_name, c.brand
     FROM equipment_categories c
     JOIN equipment_types t ON c.type_id = t.id
     ORDER BY t.name, c.brand"
);

/* ===== Update ===== */
if (isset($_POST['update'])) {

    $category_id   = $_POST['category_id'];
    $asset_code    = $_POST['asset_code'];
    $name          = $_POST['name'];
    $model         = $_POST['model'];
    $serial        = $_POST['serial_number'];
    $department    = $_POST['department'];
    $price         = $_POST['price'];
    $total_qty     = $_POST['total_qty'];
    $available_qty = $_POST['available_qty'];
    $status        = $_POST['status'];
    $note          = $_POST['note'] ?? null;   // ⭐ เพิ่มหมายเหตุ

    if ($available_qty > $total_qty) {
        die('จำนวนที่พร้อมใช้งานต้องไม่มากกว่าจำนวนทั้งหมด');
    }

    /* ===== รูป (ถ้าอัปโหลดใหม่) ===== */
    $image_sql = '';
    if (!empty($_FILES['image']['name'])) {

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = 'eq_' . time() . '.' . $ext;

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            '../../assets/uploads/equipments/' . $image_name
        );

        $image_sql = ", image = '$image_name'";
    }

    /* ===== Update DB ===== */
    $sql = "UPDATE equipments SET
        category_id   = '$category_id',
        asset_code    = '$asset_code',
        name          = '$name',
        model         = '$model',
        serial_number = '$serial',
        department    = '$department',
        price         = '$price',
        total_qty     = '$total_qty',
        available_qty = '$available_qty',
        status        = '$status',
        note          = '$note'
        $image_sql
    WHERE id = $id";

    mysqli_query($conn, $sql);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขอุปกรณ์</title>
</head>
<body>

<h2>แก้ไขอุปกรณ์</h2>

<form method="post" enctype="multipart/form-data">

    <label>รหัสครุภัณฑ์</label>
    <input type="text" name="asset_code"
           value="<?= htmlspecialchars($equipment['asset_code']); ?>" required>

    <label>ชื่ออุปกรณ์</label>
    <input type="text" name="name"
           value="<?= htmlspecialchars($equipment['name']); ?>" required>

    <label>กลุ่มประเภทอุปกรณ์</label>
    <select name="category_id" required>
        <option value="">-- เลือกกลุ่มประเภท --</option>
        <?php while ($c = mysqli_fetch_assoc($catResult)) : ?>
            <option value="<?= $c['id']; ?>"
                <?= $c['id'] == $equipment['category_id'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($c['type_name']); ?> - <?= htmlspecialchars($c['brand']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>รุ่น</label>
    <input type="text" name="model"
           value="<?= htmlspecialchars($equipment['model'] ?? ''); ?>">

    <label>Serial Number</label>
    <input type="text" name="serial_number"
           value="<?= htmlspecialchars($equipment['serial_number'] ?? ''); ?>">

    <label>แผนก</label>
    <input type="text" name="department"
           value="<?= htmlspecialchars($equipment['department'] ?? ''); ?>">

    <label>ราคา (บาท)</label>
    <input type="number" name="price" step="0.01" min="0"
           value="<?= htmlspecialchars($equipment['price']); ?>" required>

    <label>จำนวนอุปกรณ์ทั้งหมด</label>
    <input type="number" name="total_qty" min="1"
           value="<?= htmlspecialchars($equipment['total_qty']); ?>" required>

    <label>จำนวนที่พร้อมใช้งาน</label>
    <input type="number" name="available_qty" min="0"
           value="<?= htmlspecialchars($equipment['available_qty']); ?>" required>

    <label>สถานะอุปกรณ์</label>
    <select name="status">
        <option value="1" <?= $equipment['status']==1?'selected':''; ?>>พร้อมใช้งาน</option>
        <option value="2" <?= $equipment['status']==2?'selected':''; ?>>ชำรุด</option>
        <option value="0" <?= $equipment['status']==0?'selected':''; ?>>จำหน่าย</option>
    </select>

    <label>หมายเหตุอุปกรณ์</label>
    <textarea name="note" rows="3"
              placeholder="เช่น ห้ามเคลื่อนย้าย / ใช้เฉพาะห้อง X-Ray"><?= htmlspecialchars($equipment['note'] ?? ''); ?></textarea>

    <label>รูปอุปกรณ์ (อัปโหลดใหม่ถ้าต้องการ)</label>
    <input type="file" name="image">

    <?php if (!empty($equipment['image'])) : ?>
        <br>
        <img src="/asset_management/assets/uploads/equipments/<?= htmlspecialchars($equipment['image']); ?>"
             width="120" style="margin-top:10px;">
    <?php endif; ?>

    <br><br>
    <button type="submit" name="update">บันทึกการแก้ไข</button>
    <a href="index.php">ยกเลิก</a>

</form>

</body>
</html>
