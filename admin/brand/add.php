<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจ type_id ===== */
if (!isset($_GET['type_id']) || !is_numeric($_GET['type_id'])) {
    header("Location: ../categories/index.php");
    exit;
}

$type_id = (int)$_GET['type_id'];

/* ===== ดึงข้อมูลประเภท ===== */
$typeResult = mysqli_query($conn, "
SELECT id, name
FROM equipment_types
WHERE id = $type_id
LIMIT 1
");

$type = mysqli_fetch_assoc($typeResult);
if (!$type) {
    header("Location: ../categories/index.php");
    exit;
}

/* ===== บันทึกข้อมูล ===== */
if (isset($_POST['save'])) {

    $brand = trim($_POST['brand']);

    if ($brand === '') {
        $error = 'กรุณากรอกชื่อยี่ห้อ';
    } else {

        /* ป้องกันยี่ห้อซ้ำในประเภทเดียวกัน */
        $check = mysqli_query($conn, "
            SELECT id
            FROM equipment_categories
            WHERE type_id = $type_id
              AND brand = '" . mysqli_real_escape_string($conn, $brand) . "'
            LIMIT 1
        ");

        if (mysqli_num_rows($check) > 0) {
            $error = 'ยี่ห้อนี้มีอยู่แล้วในประเภทนี้';
        } else {

            mysqli_query($conn, "
                INSERT INTO equipment_categories (type_id, brand)
                VALUES ($type_id, '" . mysqli_real_escape_string($conn, $brand) . "')
            ");

            header("Location: ../categories/view.php?type_id=$type_id");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มยี่ห้ออุปกรณ์</title>
</head>
<body>

<h2>เพิ่มยี่ห้ออุปกรณ์</h2>

<p>
    <strong>ประเภท:</strong> <?= htmlspecialchars($type['name']); ?>
</p>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post">

    <label>ชื่อยี่ห้อ</label><br>
    <input type="text" name="brand"
           placeholder="เช่น HP, Dell, Asus"
           value="<?= htmlspecialchars($_POST['brand'] ?? ''); ?>"
           required>

    <br><br>

    <button type="submit" name="save">บันทึก</button>
    <a href="../categories/view.php?type_id=<?= (int)$type_id; ?>">ยกเลิก</a>

</form>

</body>
</html>
