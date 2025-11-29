<?php
require_once dirname(dirname(__DIR__)) . '/includes/config.php';
require_once dirname(dirname(__DIR__)) . '/includes/auth.php';

// Require authentication
Auth::requireLogin();
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Rajdhani:wght@500;600;700&family=Orbitron:wght@700;800;900&display=swap">
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <span class="logo-text">PIT<span>WALL</span></span>
                <span class="logo-subtitle">Admin Panel</span>
            </div>

            <nav class="admin-nav">
                <a href="index.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="articles.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'articles.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">📝</span>
                    <span class="nav-text">Articles</span>
                </a>
                <a href="races.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'races.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🏁</span>
                    <span class="nav-text">Races</span>
                </a>
                <a href="standings.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'standings.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🏆</span>
                    <span class="nav-text">Standings</span>
                </a>
                <a href="drivers.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'drivers.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">👤</span>
                    <span class="nav-text">Drivers</span>
                </a>
                <a href="constructors.php" class="admin-nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'constructors.php' ? 'active' : ''; ?>">
                    <span class="nav-icon">🏎️</span>
                    <span class="nav-text">Teams</span>
                </a>
            </nav>

            <div class="admin-sidebar-footer">
                <div class="admin-user">
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars(Auth::getUsername()); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?></span>
                    </div>
                </div>
                <a href="logout.php" class="admin-logout">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1 class="admin-page-title"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                <div class="admin-header-actions">
                    <a href="../index.html" target="_blank" class="btn btn-secondary">
                        👁️ View Site
                    </a>
                </div>
            </header>

            <div class="admin-content">
