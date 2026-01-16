<nav class="admin-navbar">
    <div class="navbar-left">
        <span class="navbar-title">Admin Dashboard</span>
    </div>

    <div class="navbar-right">
        <span class="navbar-user">
            👤 <?= $_SESSION['user_id']; ?>
        </span>

        <a href="/asset_management/auth/login.php"
           class="btn btn-sm btn-outline-danger ms-2">
            Logout
        </a>
    </div>
</nav>
