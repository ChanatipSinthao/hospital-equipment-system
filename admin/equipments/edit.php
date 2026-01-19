<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจสอบ id ===== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ไม่พบข้อมูลอุปกรณ์');
}

$id = (int)$_GET['id'];
$type_id     = (int)($_GET['type_id'] ?? 0);
$category_id = (int)($_GET['category_id'] ?? 0);

/* ===== ดึงข้อมูลอุปกรณ์ + ประเภท ===== */
$result = mysqli_query($conn, "
SELECT
    e.*,
    c.id AS category_id,
    c.type_id
FROM equipments e
LEFT JOIN equipment_categories c ON e.category_id = c.id
WHERE e.id = $id
LIMIT 1
");

$equipment = mysqli_fetch_assoc($result);
if (!$equipment) {
    die('ไม่พบข้อมูลอุปกรณ์');
}

/* ===== ดึงประเภท ===== */
$typeResult = mysqli_query(
    $conn,
    "SELECT id, name FROM equipment_types ORDER BY name"
);

/* ===== บันทึกการแก้ไข ===== */
if (isset($_POST['save'])) {

    $category_id = (int)($_POST['category_id'] ?? 0);
    if ($category_id === 0) {
        $category_id = null;
    }

    $name   = trim($_POST['name']);
    $model  = trim($_POST['model']);
    $status = (int)$_POST['status'];
    $note   = trim($_POST['note'] ?? '');

    /* ===== จัดการรูป ===== */
    $image_name = $equipment['image'];

    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $image_name = 'eq_' . time() . '.' . $ext;
            move_uploaded_file(
                $_FILES['image']['tmp_name'],
                '../../assets/uploads/equipments/' . $image_name
            );

            if (!empty($equipment['image']) &&
                file_exists('../../assets/uploads/equipments/' . $equipment['image'])) {
                unlink('../../assets/uploads/equipments/' . $equipment['image']);
            }
        }
    }

    /* ===== UPDATE ===== */
    $sql = "
        UPDATE equipments SET
            category_id = " . ($category_id ? "'$category_id'" : "NULL") . ",
            name        = '$name',
            model       = '$model',
            status      = '$status',
            image       = " . ($image_name ? "'$image_name'" : "NULL") . ",
            note        = '$note'
        WHERE id = $id
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
    <title>แก้ไขอุปกรณ์</title>
</head>
<body>

<h2>แก้ไขอุปกรณ์ (ระดับรุ่น)</h2>

<form method="post" enctype="multipart/form-data">

    <label>ประเภทอุปกรณ์</label><br>
    <select id="type_id" name="type_id" required>
        <option value="">-- เลือกประเภท --</option>
        <?php while ($t = mysqli_fetch_assoc($typeResult)) : ?>
            <option value="<?= (int)$t['id']; ?>"
                <?= $equipment['type_id'] == $t['id'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($t['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <label>ยี่ห้อ</label><br>
    <select id="category_id" name="category_id" required>
        <option value="">กำลังโหลด...</option>
    </select>

    <br><br>

    <label>ชื่ออุปกรณ์</label><br>
    <input type="text" name="name"
           value="<?= htmlspecialchars($equipment['name']); ?>" required>

    <br><br>

    <label>รุ่น</label><br>
    <input type="text" name="model"
           value="<?= htmlspecialchars($equipment['model']); ?>">

    <br><br>

    <label>สถานะของรุ่น</label><br>
    <select name="status" required>
        <option value="1" <?= $equipment['status'] == 1 ? 'selected' : ''; ?>>ใช้งานได้</option>
        <option value="2" <?= $equipment['status'] == 2 ? 'selected' : ''; ?>>งดใช้งาน</option>
        <option value="0" <?= $equipment['status'] == 0 ? 'selected' : ''; ?>>เลิกใช้</option>
    </select>

    <br><br>

    <label>หมายเหตุ (ระดับรุ่น)</label><br>
    <textarea name="note" rows="3"><?= htmlspecialchars($equipment['note']); ?></textarea>

    <br><br>

    <label>รูปรวมของรุ่นอุปกรณ์</label><br>
    <?php if (!empty($equipment['image'])) : ?>
        <img src="/asset_management/assets/uploads/equipments/<?= htmlspecialchars($equipment['image']); ?>"
             width="100"><br>
    <?php endif; ?>
    <input type="file" name="image" accept="image/*">

    <br><br>

    <button type="submit" name="save">บันทึกการแก้ไข</button>
    <a href="index.php">ยกเลิก</a>

</form>

<script>
const typeSelect = document.getElementById('type_id');
const catSelect  = document.getElementById('category_id');

function loadCategories(typeId, selectedId = null) {
    fetch('get_categories.php?type_id=' + typeId)
        .then(res => res.text())
        .then(html => {
            catSelect.innerHTML = html;
            if (selectedId) {
                catSelect.value = selectedId;
            }
        });
}

// preload ตอนเปิดหน้า
if (typeSelect.value) {
    loadCategories(typeSelect.value, '<?= (int)($equipment['category_id'] ?? 0); ?>');
}

typeSelect.addEventListener('change', function () {
    loadCategories(this.value);
});
</script>

</body>
</html>
