<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจสอบ equipment_id ===== */
if (!isset($_GET['equipment_id']) || !is_numeric($_GET['equipment_id'])) {
    die('ไม่พบข้อมูลอุปกรณ์');
}

$equipment_id = (int)$_GET['equipment_id'];

/* ===== ดึงข้อมูลรุ่น ===== */
$result_equipment = mysqli_query($conn, "
SELECT id, name, model
FROM equipments
WHERE id = $equipment_id
LIMIT 1
");

$equipment = mysqli_fetch_assoc($result_equipment);
if (!$equipment) {
    die('ไม่พบข้อมูลอุปกรณ์');
}

/* ===== บันทึกข้อมูล ===== */
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $asset_number  = trim($_POST['asset_number'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $note          = trim($_POST['note'] ?? '');
    $status        = (int)($_POST['status'] ?? 1);

    /* ===== ตรวจสอบรูป ===== */
    $image_name = null;
    if (!empty($_FILES['image']['name'])) {

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = 'อนุญาตเฉพาะไฟล์รูป jpg, png, webp';
        } else {
            $image_name = uniqid('item_') . '.' . $ext;
            $upload_path = '../../assets/uploads/equipment_items/' . $image_name;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $error = 'อัปโหลดรูปไม่สำเร็จ';
            }
        }
    }

    if ($asset_number === '') {
        $error = 'กรุณากรอกเลขครุภัณฑ์';
    }

    /* ===== ตรวจเลขครุภัณฑ์ซ้ำ ===== */
    if (!$error) {
        $check = mysqli_query($conn, "
            SELECT id FROM equipment_items
            WHERE asset_number = '" . mysqli_real_escape_string($conn, $asset_number) . "'
            LIMIT 1
        ");

        if (mysqli_num_rows($check) > 0) {
            $error = 'เลขครุภัณฑ์นี้ถูกใช้แล้ว';
        }
    }

    if ($price < 0) {
    $error = 'ราคาต้องมากกว่าหรือเท่ากับ 0';
    }

    /* ===== บันทึกลง DB ===== */
    if (!$error) {

        mysqli_query($conn, "
            INSERT INTO equipment_items (
                equipment_id,
                asset_number,
                serial_number,
                image,
                note,
                price,
                status
            ) VALUES (
                $equipment_id,
                '" . mysqli_real_escape_string($conn, $asset_number) . "',
                '" . mysqli_real_escape_string($conn, $serial_number) . "',
                " . ($image_name ? "'" . mysqli_real_escape_string($conn, $image_name) . "'" : "NULL") . ",
                '" . mysqli_real_escape_string($conn, $note) . "',
                $price,
                $status
            )
        ");

        header("Location: ../equipments/view.php?id=$equipment_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มครุภัณฑ์รายเครื่อง</title>
</head>
<body>

<h2>เพิ่มครุภัณฑ์รายเครื่อง</h2>

<p>
<strong>อุปกรณ์:</strong>
<?= htmlspecialchars($equipment['name']); ?>
<?= htmlspecialchars($equipment['model']); ?>
</p>

<?php if ($error) : ?>
<p style="color:red;"><?= htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
<table cellpadding="8">

<tr>
    <td>เลขครุภัณฑ์ *</td>
    <td><input type="text" name="asset_number" required></td>
</tr>

<tr>
    <td>Serial Number</td>
    <td><input type="text" name="serial_number"></td>
</tr>

<tr>
    <td>รูปอุปกรณ์</td>
    <td><input type="file" name="image" accept="image/*"></td>
</tr>

<tr>
    <td>ราคาอุปกรณ์ (บาท)</td>
    <td>
        <input type="number"
               name="price"
               step="0.01"
               min="0"
               required>
    </td>
</tr>

<tr>
    <td>หมายเหตุ</td>
    <td><textarea name="note" rows="4" cols="40"></textarea></td>
</tr>

<tr>
    <td>สถานะ</td>
    <td>
        <select name="status">
            <option value="1">พร้อมใช้งาน</option>
            <option value="2">ชำรุด</option>
            <option value="0">จำหน่าย</option>
        </select>
    </td>
</tr>

<tr>
    <td></td>
    <td>
        <button type="submit">💾 บันทึก</button>
        <a href="../equipments/view.php?id=<?= $equipment_id; ?>">ยกเลิก</a>
    </td>
</tr>

</table>
</form>

</body>
</html>
