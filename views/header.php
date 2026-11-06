<?php

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';

// Check if we are on the login page
$isLoginPage = in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'register.php'], true);

// Check which interface the admin selected
$isAdminView = $role === 'admin' && ($_SESSION['view'] ?? 'admin') === 'admin';

// The name and avatar of the child who is logged in, shown on every page
$headerExplorer = null;
if ($role === 'child' && isset($_SESSION['userID'])) {
    require_once __DIR__ . '/../logic/explorer.php';
    $headerExplorer = findExplorer((int)$_SESSION['userID']);
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رحلة | RIHLA</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<header <?= $isLoginPage ? 'style="justify-content: flex-start;"' : '' ?>>

    <h1>
        <?php if ($isLoginPage): ?>

            <a style="pointer-events: none; cursor: default;">
                رحلة
            </a>

        <?php else: ?>

            <a href="<?= $isAdminView ? 'admin_dashboard.php' : 'index.php' ?>">
                رحلة
            </a>

        <?php endif; ?>
    </h1>

    <?php if (!$isLoginPage): ?>

        <nav>

            <?php if ($isAdminView): ?>

                <a href="admin_dashboard.php"><img class="nav-icon" src="assets/images/icons/home.png" alt="">الرئيسية</a> |
                <a href="logout.php"><img class="nav-icon" src="assets/images/icons/logout.png" alt="">خروج</a>

            <?php else: ?>

                <a href="index.php"><img class="nav-icon" src="assets/images/icons/home.png" alt="">الرئيسية</a> |
                <a href="search.php"><img class="nav-icon" src="assets/images/icons/search.png" alt="">البحث</a> |

                <?php if ($headerExplorer !== null): ?>
                    <a href="notebook.php"><img class="nav-icon" src="assets/images/icons/notebook.png" alt="">دفتري</a> |
                    <a href="profile.php"><img class="nav-icon" src="assets/images/icons/profile.png" alt="">ملفي</a> |
                <?php endif; ?>

                <?php if ($role === 'child' || $role === 'admin'): ?>

                    <a href="logout.php"><img class="nav-icon" src="assets/images/icons/logout.png" alt="">خروج</a>

                <?php else: ?>

                    <a href="login.php"><img class="nav-icon" src="assets/images/icons/login.png" alt="">دخول</a>

                <?php endif; ?>

            <?php endif; ?>

        </nav>

    <?php endif; ?>

    <?php if ($headerExplorer !== null): ?>
        <a class="header-explorer" href="profile.php"
           style="display:flex;align-items:center;gap:8px;text-decoration:none;font-weight:bold;color:#4a3420;">
            <img src="<?= e(avatarPath($headerExplorer['avatar'])) ?>" alt="" style="width:42px;height:42px;border-radius:50%;">
            <?= e($headerExplorer['displayName']) ?>
        </a>
    <?php endif; ?>

</header>

<hr>

<main>
