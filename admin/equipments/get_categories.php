<?php
include '../../config/db.php';

$type_id = (int)($_GET['type_id'] ?? 0);

echo '<option value="">-- เลือกยี่ห้อ --</option>';
echo '<option value="0">ไม่มี</option>';

if ($type_id > 0) {
    $result = mysqli_query($conn, "
        SELECT id, brand
        FROM equipment_categories
        WHERE type_id = $type_id
        ORDER BY brand
    ");

    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="'.$row['id'].'">'.
             htmlspecialchars($row['brand']).
             '</option>';
    }
}

