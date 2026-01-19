<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจ id ยี่ห้อ ===== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../categories/index.php");
    exit;
}

$brand_id = (int)$_GET['id'];

/* ===== ดึงข้อมูลยี่ห้อ + ประเภท ===== */
$result = mysqli_query($conn, "
SELECT
    c.id,
    c.brand,
    c.type_id,
    t.name AS type_name
FROM equipment_categories c
JOIN equipment_types t ON c.type_id = t.id
WHERE c.id = $brand_id
LIMIT 1
");

$brand = mysqli_fetch_assoc($result);
if (!$brand) {
    header("Location: ../categories/index.php");
    exit;
}

/* ===== บันทึกการแก้ไข ===== */
if (isset($_POST['save'])) {

    $new_brand = trim($_POST['brand']);

    if ($new_brand === '') {
        $error = 'กรุณากรอกชื่อยี่ห้อ';
    } else {

        /* ป้องกันชื่อซ้ำในประเภทเดียวกัน */
        $check = mysqli_query($conn, "
            SELECT id
            FROM equipment_categories
            WHERE type_id = {$brand['type_id']}
              AND brand = '" . mysqli_real_escape_string($conn, $new_brand) . "'
              AND id <> $brand_id
            LIMIT 1
        ");

        if (mysqli_num_rows($check) > 0) {
            $error = 'ยี่ห้อนี้มีอยู่แล้วในประเภทนี้';
        } else {

            mysqli_query($conn, "
                UPDATE equipment_categories
                SET brand = '" . mysqli_real_escape_string($conn, $new_brand) . "'
                WHERE id = $brand_id
            ");

            header("Location: ../categories/view.php?type_id={$brand['type_id']}");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขยี่ห้ออุปกรณ์</title>
</head>
<body>

<h2>แก้ไขยี่ห้ออุปกรณ์</h2>

<p>
    <strong>ประเภท:</strong> <?= htmlspecialchars($brand['type_name']); ?>
</p>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post">

    <label>ชื่อยี่ห้อ</label><br>
    <input type="text"
           name="brand"
           value="<?= htmlspecialchars($_POST['brand'] ?? $brand['brand']); ?>"
           required>

    <br><br>

    <button type="submit" name="save">บันทึกการแก้ไข</button>
    <a href="../categories/view.php?type_id=<?= (int)$brand['type_id']; ?>">
        ยกเลิก
    </a>

</form>

</body>
</html>
