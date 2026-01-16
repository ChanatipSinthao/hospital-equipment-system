<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ดึงประเภทหลัก ===== */
$typeResult = mysqli_query(
    $conn,
    "SELECT id, name FROM equipment_types ORDER BY name"
);

/* ===== บันทึก ===== */
if (isset($_POST['save'])) {

    $type_id = (int)$_POST['type_id'];
    $brand   = trim($_POST['brand']);

    if ($type_id === 0 || $brand === '') {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } else {

        /* ===== ตรวจซ้ำ (ประเภทหลัก + ยี่ห้อ) ===== */
        $check = mysqli_query(
            $conn,
            "SELECT id FROM equipment_categories
             WHERE type_id = '$type_id' AND brand = '$brand'"
        );

        if (mysqli_num_rows($check) > 0) {
            $error = 'กลุ่มประเภทนี้มีอยู่แล้ว';
        } else {

            /* ===== รูปกลุ่มประเภท ===== */
            $image_name = null;
            if (!empty($_FILES['image']['name'])) {

                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = 'cat_' . time() . '.' . $ext;

                move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    '../../assets/uploads/categories/' . $image_name
                );
            }

            /* ===== INSERT ===== */
            mysqli_query(
                $conn,
                "INSERT INTO equipment_categories (type_id, brand, image)
                 VALUES ('$type_id', '$brand', '$image_name')"
            );

            header("Location: index.php?success=1");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มกลุ่มประเภทอุปกรณ์</title>
</head>
<body>

<h2>เพิ่มกลุ่มประเภทอุปกรณ์</h2>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= $error; ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">

    <label>ประเภทหลัก</label><br>
    <select name="type_id" required>
        <option value="">-- เลือกประเภท --</option>
        <?php while ($t = mysqli_fetch_assoc($typeResult)) : ?>
            <option value="<?= $t['id']; ?>">
                <?= htmlspecialchars($t['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <label>ยี่ห้อ</label><br>
    <input type="text" name="brand" required>

    <br><br>

    <label>รูปกลุ่มประเภท</label><br>
    <input type="file" name="image">

    <br><br>

    <button type="submit" name="save">บันทึก</button>
    <a href="index.php">ยกเลิก</a>

</form>

</body>
</html>
