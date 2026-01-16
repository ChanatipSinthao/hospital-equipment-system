<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== บันทึกข้อมูล ===== */
if (isset($_POST['save'])) {

    $name = trim($_POST['name']);

    if ($name === '') {
        $error = 'กรุณากรอกชื่อประเภท';
    } else {

        /* ===== ตรวจชื่อซ้ำ ===== */
        $check = mysqli_query(
            $conn,
            "SELECT id FROM equipment_types WHERE name = '$name'"
        );

        if (mysqli_num_rows($check) > 0) {
            $error = 'ประเภทนี้มีอยู่แล้ว';
        } else {

            mysqli_query(
                $conn,
                "INSERT INTO equipment_types (name) VALUES ('$name')"
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
    <title>เพิ่มประเภทอุปกรณ์หลัก</title>
</head>
<body>

<h2>เพิ่มประเภทอุปกรณ์หลัก</h2>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= $error; ?></p>
<?php endif; ?>

<form method="post">

    <label>ชื่อประเภทหลัก</label><br>
    <input type="text" name="name"
           placeholder="เช่น PC, Notebook, Printer"
           required>
    <br><br>

    <button type="submit" name="save">บันทึก</button>
    <a href="index.php">ยกเลิก</a>

</form>

</body>
</html>
