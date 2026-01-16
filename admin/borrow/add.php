<?php
include '../../includes/admin_guard.php';
include '../../config/db.php';

/* ===== ตรวจ equipment_id ===== */
if (!isset($_GET['equipment_id'])) {
    header("Location: ../equipments/index.php");
    exit;
}

$equipment_id = (int)$_GET['equipment_id'];

/* ===== ดึงข้อมูลอุปกรณ์ ===== */
$equipResult = mysqli_query(
    $conn,
    "SELECT id, name, asset_code, available_qty
     FROM equipments
     WHERE id = $equipment_id"
);

$equipment = mysqli_fetch_assoc($equipResult);

if (!$equipment) {
    header("Location: ../equipments/index.php");
    exit;
}

/* ===== บันทึกการเบิก ===== */
if (isset($_POST['save'])) {

    $issue_qty  = (int)$_POST['issue_qty'];
    $issued_to  = trim($_POST['issued_to']);
    $department = trim($_POST['department']);
    $note       = trim($_POST['note']);
    $issued_by  = $_SESSION['user_id'];

    if ($issue_qty <= 0) {
        $error = 'จำนวนที่เบิกต้องมากกว่า 0';
    } elseif ($issue_qty > $equipment['available_qty']) {
        $error = 'จำนวนที่เบิกมากกว่าจำนวนคงเหลือ';
    } elseif ($issued_to === '') {
        $error = 'กรุณากรอกชื่อผู้เบิกหรือหน่วยงาน';
    } else {

        /* ===== บันทึกประวัติการเบิก ===== */
        mysqli_query(
            $conn,
            "INSERT INTO equipment_issues
             (equipment_id, issue_qty, issued_to, department, issued_by, note)
             VALUES
             ('$equipment_id', '$issue_qty', '$issued_to', '$department',
              '$issued_by', '$note')"
        );

        /* ===== ตัดสต๊อก ===== */
        mysqli_query(
            $conn,
            "UPDATE equipments
             SET available_qty = available_qty - $issue_qty
             WHERE id = $equipment_id"
        );

        header("Location: index.php?success=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เบิกอุปกรณ์</title>
</head>
<body>

<h2>เบิกอุปกรณ์</h2>

<p>
    <strong>อุปกรณ์:</strong> <?= htmlspecialchars($equipment['name']); ?><br>
    <strong>รหัสครุภัณฑ์:</strong> <?= htmlspecialchars($equipment['asset_code']); ?><br>
    <strong>คงเหลือ:</strong> <?= (int)$equipment['available_qty']; ?>
</p>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= $error; ?></p>
<?php endif; ?>

<form method="post">

    <label>จำนวนที่เบิก</label><br>
    <input type="number"
           name="issue_qty"
           min="1"
           max="<?= (int)$equipment['available_qty']; ?>"
           required>

    <br><br>

    <label>ผู้เบิก / หน่วยงาน</label><br>
    <input type="text" name="issued_to" required>

    <br><br>

    <label>แผนก</label><br>
    <input type="text" name="department">

    <br><br>

    <label>หมายเหตุ</label><br>
    <textarea name="note" rows="3"></textarea>

    <br><br>

    <button type="submit" name="save">ยืนยันการเบิก</button>
    <a href="../equipments/index.php">ยกเลิก</a>

</form>

</body>
</html>
