<?php

function e($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';

// Check if we are on the login page
$isLoginPage = basename($_SERVER['PHP_SELF']) === 'login.php';

// Check which interface the admin selected
$isAdminView = $role === 'admin' && ($_SESSION['view'] ?? 'admin') === 'admin';
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

                <a href="admin_dashboard.php">الرئيسية</a> |
                <a href="logout.php">تسجيل الخروج</a>

            <?php else: ?>

                <a href="index.php">الرئيسية</a> |
                <a href="search.php">البحث</a> |

                <?php if ($role === 'child' || $role === 'admin'): ?>

                    <a href="logout.php">تسجيل الخروج</a>

                <?php else: ?>

                    <a href="login.php">تسجيل الدخول</a>

                <?php endif; ?>

            <?php endif; ?>

        </nav>

    <?php endif; ?>

</header>

<hr>

<main>
