<?php
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>

<?php include 'views/header.php'; ?>

<h1>لوحة تحكم المسؤول</h1>

<p>مرحبًا بك في لوحة تحكم المسؤول في رحلة!</p>

<h2>إدارة الموقع</h2>

<ul>
    <li>
        <a href="manage_destinations.php">إدارة الوجهات والمعالم</a>
    </li>

    <li>
        <a href="manage_content.php">إدارة المحتوى الثقافي والتاريخي</a>
    </li>

    <li>
        <a href="upload_images.php">رفع الصور</a>
    </li>
</ul>

<?php include 'views/footer.php'; ?>