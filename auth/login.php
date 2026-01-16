<?php session_start(); include '../layouts/auth/header.php'; ?>

<div class="container-fluid d-flex justify-content-center align-items-center vh-100">

    <div class="login-wrapper shadow-lg">

        <!-- ===== โลโก้ มุมบนซ้าย ===== -->
        <div class="brand-top-left">
            <img src="/asset_management/assets/images/logo.png" alt="Logo">
                <div class="brand-title">
                    <span class="brand-line brand-line-main">
                        โรงพยาบาลยางตลาด
                    </span>
                    <span class="brand-line brand-line-sub">
                        Yangtalad Hospital | yth.moph.go.th
                    </span>
                </div>
        </div>

        <div class="row h-100 g-0">

            <!-- ฝั่งซ้าย (เผื่อใส่ภาพ / background) -->
            <div class="col-md-6 login-left-container d-none d-md-flex justify-content-center align-items-center">
                <img src="/asset_management/assets/images/list_check.png"
                    alt="Checklist"
                    class="left-center-image">
            </div>

            <!-- ฝั่งขวา -->
            <div class="col-md-6 d-flex justify-content-center bg-white">
                <div class="login-form">

                    <?php
                    
                    if (isset($_SESSION['login_error'])) {
                        echo '<div class="alert alert-danger">';
                        echo $_SESSION['login_error'];
                        echo '</div>';
                        unset($_SESSION['login_error']);
                    }
                    ?>

                    <h4 class="fw-bold mb-3">เข้าสู่ระบบ</h4>
                    <p class="text-muted mb-4">
                        ระบบบริหารจัดการครุภัณคอมพิวเตอร์
                    </p>

                    <form method="post" action="check_login.php">
                        <div class="mb-3">
                            <label class="form-label">ชื่อผู้ใช้งาน</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">รหัสผ่าน</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-signin w-100 py-2">
                            Sign in
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

<?php include '../layouts/admin/footer.php'; ?>
