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

    $category_id = $_POST['category_id'];
    $name        = $_POST['name'];
    $model       = $_POST['model'];
    $price       = $_POST['price'];       // ราคาต่อเครื่อง
    $total_qty   = $_POST['total_qty'];   // จำนวนทั้งหมด
    $status      = $_POST['status'];
    $note        = $_POST['note'] ?? null;

    // จำนวนพร้อมใช้งาน = จำนวนทั้งหมด (ตอนเพิ่มใหม่)
    $available_qty = $total_qty;

    /* ===== รูปกลุ่ม ===== */
    $image_name = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = 'eq_group_' . time() . '.' . $ext;

        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            '../../assets/uploads/equipments/' . $image_name
        );
    }

    /* ===== Insert ===== */
    $sql = "INSERT INTO equipments
        (category_id, name, model, price,
         total_qty, available_qty, status, image, note)
        VALUES
        ('$category_id', '$name', '$model', '$price',
         '$total_qty', '$available_qty', '$status', '$image_name', '$note')";

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

<h2>เพิ่มอุปกรณ์</h2>

<form method="post" enctype="multipart/form-data">

    <label>กลุ่มประเภทอุปกรณ์</label><br>
    <select name="category_id" required>
        <option value="">-- เลือกกลุ่มประเภท --</option>
        <?php while ($c = mysqli_fetch_assoc($catResult)) : ?>
            <option value="<?= $c['id']; ?>">
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

    <label>ราคาต่อเครื่อง (บาท)</label><br>
    <input type="number" name="price" step="0.01" min="0" required>

    <br><br>

    <label>จำนวนอุปกรณ์ทั้งหมด</label><br>
    <input type="number" name="total_qty" min="1" value="1" required>

    <br><br>

    <label>สถานะเริ่มต้นของกลุ่ม</label><br>
    <select name="status" required>
        <option value="1">พร้อมใช้งาน</option>
        <option value="2">ชำรุด</option>
        <option value="0">จำหน่าย</option>
    </select>

    <br><br>

    <label>หมายเหตุ (ระดับกลุ่ม)</label><br>
    <textarea name="note" rows="3"
              placeholder="เช่น ใช้เฉพาะแผนก IT / รุ่นเก่าหยุดจัดซื้อ"></textarea>

    <br><br>

    <label>รูปรวมของกลุ่มอุปกรณ์</label><br>
    <input type="file" name="image">

    <br><br>

    <button type="submit" name="save">บันทึก</button>
    <a href="index.php">ยกเลิก</a>

</form>

</body>
</html>
