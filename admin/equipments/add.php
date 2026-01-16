<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ดึงกลุ่มประเภท ===== */
$catResult = mysqli_query(
    $conn,
    "SELECT 
        c.id,
        t.name AS type_name,
        c.brand
     FROM equipment_categories c
     JOIN equipment_types t ON c.type_id = t.id
     ORDER BY t.name, c.brand"
);

/* ===== บันทึกข้อมูล ===== */
if (isset($_POST['save'])) {

    $category_id = (int)$_POST['category_id'];
    $name  = trim($_POST['name']);
    $model = trim($_POST['model']);
    $status = (int)$_POST['status'];
    $note   = trim($_POST['note'] ?? '');

    /* ===== รูปรวมของรุ่น ===== */
    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $image_name = 'eq_' . time() . '.' . $ext;
            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                '../../assets/uploads/equipments/' . $image_name
            );
        }
    }

    /* ===== Insert รุ่นอุปกรณ์ ===== */
    $sql = "
        INSERT INTO equipments
            (category_id, name, model, status, image, note)
        VALUES
            ('$category_id', '$name', '$model', '$status', '$image_name', '$note')
    ";

    mysqli_query($conn, $sql);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มอุปกรณ์</title>
</head>
<body>

<h2>เพิ่มอุปกรณ์ (ระดับรุ่น)</h2>

<form method="post" enctype="multipart/form-data">

    <label>กลุ่มประเภทอุปกรณ์</label><br>
    <select name="category_id" required>
        <option value="">-- เลือกกลุ่มประเภท --</option>
        <?php while ($c = mysqli_fetch_assoc($catResult)) : ?>
            <option value="<?= (int)$c['id']; ?>">
                <?= htmlspecialchars($c['type_name']); ?> - <?= htmlspecialchars($c['brand']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <label>ชื่ออุปกรณ์</label><br>
    <input type="text" name="name" required>

    <br><br>

    <label>รุ่น</label><br>
    <input type="text" name="model">

    <br><br>

    <label>สถานะเริ่มต้นของรุ่น</label><br>
    <select name="status" required>
        <option value="1">ใช้งานได้</option>
        <option value="2">งดใช้งาน</option>
        <option value="0">เลิกใช้</option>
    </select>

    <br><br>

    <label>หมายเหตุ (ระดับรุ่น)</label><br>
    <textarea name="note" rows="3"
        placeholder="เช่น ใช้เฉพาะแผนก IT / รุ่นเก่าหยุดจัดซื้อ"></textarea>

    <br><br>

    <label>รูปรวมของรุ่นอุปกรณ์</label><br>
    <input type="file" name="image" accept="image/*">

    <br><br>

    <button type="submit" name="save">บันทึก</button>
    <a href="index.php">ยกเลิก</a>

</form>

</body>
</html>
