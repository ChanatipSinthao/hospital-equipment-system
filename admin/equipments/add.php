<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

$type_id     = (int)($_GET['type_id'] ?? 0);
$category_id = (int)($_GET['category_id'] ?? 0);

/* ===== ดึงประเภท ===== */
$typeResult = mysqli_query(
    $conn,
    "SELECT id, name FROM equipment_types ORDER BY name"
);

/* ===== บันทึกข้อมูล ===== */
if (isset($_POST['save'])) {

    $category_id = (int)($_POST['category_id'] ?? 0);
    if ($category_id === 0) {
        $category_id = null; // ไม่มีแบรนด์
    }

    $name   = trim($_POST['name']);
    $model  = trim($_POST['model']);
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
            (" . ($category_id ? "'$category_id'" : "NULL") . ",
             '$name', '$model', '$status',
             " . ($image_name ? "'$image_name'" : "NULL") . ",
             '$note')
    ";

    mysqli_query($conn, $sql);

    $redirect = "index.php";

    $query = [];
    if ($type_id > 0) {
        $query[] = "type_id=$type_id";
    }
    if ($category_id > 0) {
        $query[] = "category_id=$category_id";
    }

    if (!empty($query)) {
        $redirect .= '?' . implode('&', $query);
    }

    header("Location: $redirect");
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

    <label>ประเภทอุปกรณ์</label><br>
    <select id="type_id" name="type_id" required>
        <option value="">-- เลือกประเภท --</option>
        <?php while ($t = mysqli_fetch_assoc($typeResult)) : ?>
            <option value="<?= (int)$t['id']; ?>">
                <?= htmlspecialchars($t['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <label>ยี่ห้อ</label><br>
    <select id="category_id" name="category_id" required disabled>
        <option value="">-- กรุณาเลือกประเภทก่อน --</option>
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

<script>
document.getElementById('type_id').addEventListener('change', function () {
    const typeId = this.value;
    const catSelect = document.getElementById('category_id');

    catSelect.innerHTML = '<option>กำลังโหลด...</option>';
    catSelect.disabled = true;

    if (!typeId) {
        catSelect.innerHTML = '<option>-- กรุณาเลือกประเภทก่อน --</option>';
        return;
    }

    fetch('get_categories.php?type_id=' + typeId)
        .then(res => res.text())
        .then(html => {
            catSelect.innerHTML = html;
            catSelect.disabled = false;
        });
});
</script>
