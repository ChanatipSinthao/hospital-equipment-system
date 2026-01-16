<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจสอบ id ===== */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ไม่พบข้อมูลครุภัณฑ์');
}

$id = (int)$_GET['id'];
$price = (float)($_POST['price'] ?? 0);

/* ===== ดึงข้อมูล item + รุ่น ===== */
$result = mysqli_query($conn, "
SELECT
    ei.*,
    e.name AS equipment_name,
    e.model,
    e.id AS equipment_id
FROM equipment_items ei
JOIN equipments e ON ei.equipment_id = e.id
WHERE ei.id = $id
LIMIT 1
");

$item = mysqli_fetch_assoc($result);
if (!$item) {
    die('ไม่พบข้อมูลครุภัณฑ์');
}

/* ===== บันทึกการแก้ไข ===== */
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $asset_number  = trim($_POST['asset_number'] ?? '');
    $serial_number = trim($_POST['serial_number'] ?? '');
    $note          = trim($_POST['note'] ?? '');
    $status        = (int)($_POST['status'] ?? 1);

    /* ===== ตรวจรูป ===== */
    $image_name = $item['image'];

    if (!empty($_FILES['image']['name'])) {

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $error = 'อนุญาตเฉพาะไฟล์ jpg, jpeg, png, webp';
        } else {

            $image_name = uniqid('item_') . '.' . $ext;
            $upload_path = '../../assets/uploads/equipment_items/' . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                /* ลบรูปเก่า */
                if (!empty($item['image']) && file_exists('../../assets/uploads/equipment_items/' . $item['image'])) {
                    unlink('../../assets/uploads/equipment_items/' . $item['image']);
                }
            } else {
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
        AND id != $id
        LIMIT 1
        ");

        if (mysqli_num_rows($check) > 0) {
            $error = 'เลขครุภัณฑ์นี้ถูกใช้แล้ว';
        }
    }

    if ($price < 0) {
    $error = 'ราคาต้องมากกว่าหรือเท่ากับ 0';
    }

    /* ===== UPDATE ===== */
    if (!$error) {

        mysqli_query($conn, "
        UPDATE equipment_items SET
            asset_number  = '" . mysqli_real_escape_string($conn, $asset_number) . "',
            serial_number = '" . mysqli_real_escape_string($conn, $serial_number) . "',
            image         = " . ($image_name ? "'" . mysqli_real_escape_string($conn, $image_name) . "'" : "NULL") . ",
            note          = '" . mysqli_real_escape_string($conn, $note) . "',
            price         = $price,
            status        = $status
        WHERE id = $id
        ");

        header("Location: ../equipments/view.php?id=" . (int)$item['equipment_id']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขครุภัณฑ์รายเครื่อง</title>
</head>
<body>

<h2>แก้ไขครุภัณฑ์รายเครื่อง</h2>

<p>
<strong>อุปกรณ์:</strong>
<?= htmlspecialchars($item['equipment_name']); ?>
<?= htmlspecialchars($item['model']); ?>
</p>

<?php if ($error) : ?>
<p style="color:red;"><?= htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
<table cellpadding="8">

<tr>
    <td>เลขครุภัณฑ์ *</td>
    <td>
        <input type="text" name="asset_number"
               value="<?= htmlspecialchars($item['asset_number']); ?>" required>
    </td>
</tr>

<tr>
    <td>Serial Number</td>
    <td>
        <input type="text" name="serial_number"
               value="<?= htmlspecialchars($item['serial_number']); ?>">
    </td>
</tr>

<tr>
    <td>รูปอุปกรณ์</td>
    <td>
        <?php if (!empty($item['image'])) : ?>
            <img src="/asset_management/assets/uploads/equipment_items/<?= htmlspecialchars($item['image']); ?>"
                 width="80" style="object-fit:cover;"><br>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*">
    </td>
</tr>

<tr>
    <td>ราคาอุปกรณ์ (บาท)</td>
    <td>
        <input type="number"
               name="price"
               step="0.01"
               min="0"
               value="<?= htmlspecialchars($item['price']); ?>"
               required>
    </td>
</tr>


<tr>
    <td>หมายเหตุ</td>
    <td>
        <textarea name="note" rows="4" cols="40"><?= htmlspecialchars($item['note']); ?></textarea>
    </td>
</tr>

<tr>
    <td>สถานะ</td>
    <td>
        <select name="status">
            <option value="1" <?= $item['status'] == 1 ? 'selected' : ''; ?>>พร้อมใช้งาน</option>
            <option value="2" <?= $item['status'] == 2 ? 'selected' : ''; ?>>ชำรุด</option>
            <option value="0" <?= $item['status'] == 0 ? 'selected' : ''; ?>>จำหน่าย</option>
        </select>
    </td>
</tr>

<tr>
    <td></td>
    <td>
        <button type="submit">💾 บันทึกการแก้ไข</button>
        <a href="../equipments/view.php?id=<?= (int)$item['equipment_id']; ?>">ยกเลิก</a>
    </td>
</tr>

</table>
</form>

</body>
</html>
