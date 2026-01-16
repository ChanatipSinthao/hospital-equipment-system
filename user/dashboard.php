<?php
session_start();
if ($_SESSION['role'] != 'user') {
    header("Location: ../auth/login.php");
}
echo "User Dashboard";
