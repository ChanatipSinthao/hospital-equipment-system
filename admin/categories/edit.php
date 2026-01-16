<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจ id ===== */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

/* ===== ดึงข้อมูลกลุ่มประเภทเดิม ===== */
$categoryResult = mysqli_query(
    $conn,
    "SELECT * FROM equipment_categories WHERE id = $id"
);
$category = mysqli_fetch_assoc($categoryResult);

if (!$category) {
    header("Location: index.php");
    exit;
}

/* ===== ดึงประเภทหลักทั้งหมด ===== */
$typeResult = mysqli_query(
    $conn,
    "SELECT id, name FROM equipment_types ORDER BY name"
);

/* ===== บันทึกการแก้ไข ===== */
if (isset($_POST['update'])) {

    $type_id = (int)$_POST['type_id'];
    $brand   = trim($_POST['brand']);

    if ($type_id === 0 || $brand === '') {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } else {

        /* ===== ตรวจซ้ำ (ยกเว้นตัวเอง) ===== */
        $check = mysqli_query(
            $conn,
            "SELECT id FROM equipment_categories
             WHERE type_id = '$type_id'
               AND brand = '$brand'
               AND id != $id"
        );

        if (mysqli_num_rows($check) > 0) {
            $error = 'กลุ่มประเภทนี้มีอยู่แล้ว';
        } else {

            /* ===== รูป (ถ้ามีการอัปโหลดใหม่) ===== */
            $image_sql = '';
            if (!empty($_FILES['image']['name'])) {

                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = 'cat_' . time() . '.' . $ext;

                move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    '../../assets/uploads/categories/' . $image_name
                );

                $image_sql = ", image = '$image_name'";
            }

            /* ===== UPDATE ===== */
            mysqli_query(
                $conn,
                "UPDATE equipment_categories SET
                    type_id = '$type_id',
                    brand   = '$brand'
                    $image_sql
                 WHERE id = $id"
            );

            header("Location: index.php?updated=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขกลุ่มประเภทอุปกรณ์</title>
</head>
<body>

<h2>แก้ไขกลุ่มประเภทอุปกรณ์</h2>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= $error; ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

    <label>ประเภทหลัก</label><br>
    <select name="type_id" required>
        <option value="">-- เลือกประเภท --</option>
        <?php while ($t = mysqli_fetch_assoc($typeResult)) : ?>
            <option value="<?= $t['id']; ?>"
                <?= $t['id'] == $category['type_id'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($t['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <label>ยี่ห้อ</label><br>
    <input type="text" name="brand"
           value="<?= htmlspecialchars($category['brand']); ?>" required>

    <br><br>

    <label>รูปกลุ่มประเภท (อัปโหลดใหม่ถ้าต้องการ)</label><br>
    <input type="file" name="image">

    <?php if (!empty($category['image'])) : ?>
        <br>
        <img src="/asset_management/assets/uploads/categories/<?= $category['image']; ?>"
             width="100" style="margin-top:10px;">
    <?php endif; ?>

    <br><br>

    <button type="submit" name="update">บันทึกการแก้ไข</button>
    <a href="index.php">ยกเลิก</a>

</form>

</body>
</html>
