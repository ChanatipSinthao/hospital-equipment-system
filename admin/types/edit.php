<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจ id ===== */
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

/* ===== ดึงข้อมูลประเภทเดิม ===== */
$typeResult = mysqli_query(
    $conn,
    "SELECT * FROM equipment_types WHERE id = $id"
);
$type = mysqli_fetch_assoc($typeResult);

if (!$type) {
    header("Location: index.php");
    exit;
}

/* ===== บันทึกการแก้ไข ===== */
if (isset($_POST['update'])) {

    $name = trim($_POST['name']);

    if ($name === '') {
        $error = 'กรุณากรอกชื่อประเภท';
    } else {

        /* ===== ตรวจชื่อซ้ำ (ยกเว้นตัวเอง) ===== */
        $check = mysqli_query(
            $conn,
            "SELECT id FROM equipment_types 
             WHERE name = '$name' AND id != $id"
        );

        if (mysqli_num_rows($check) > 0) {
            $error = 'ชื่อประเภทนี้มีอยู่แล้ว';
        } else {

            mysqli_query(
                $conn,
                "UPDATE equipment_types
                 SET name = '$name'
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
    <title>แก้ไขประเภทอุปกรณ์หลัก</title>
</head>
<body>

<h2>แก้ไขประเภทอุปกรณ์หลัก</h2>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= $error; ?></p>
<?php endif; ?>

<form method="post">

    <label>ชื่อประเภทหลัก</label><br>
    <input type="text" name="name"
           value="<?= htmlspecialchars($type['name']); ?>"
           required>
    <br><br>

    <button type="submit" name="update">บันทึกการแก้ไข</button>
    <a href="index.php">ยกเลิก</a>

</form>

</body>
</html>
