<?php
// The administrator chooses an item to delete
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'logic/explorer.php';

$message = $_SESSION['delete_message'] ?? '';
unset($_SESSION['delete_message']);

$items = findDeletableItems();

$groups = [
    'destination' => 'الوجهات',
    'landmark'    => 'المعالم',
    'content'     => 'المحتوى الثقافي والتاريخي',
    'question'    => 'أسئلة الاختبارات',
    'badge'       => 'الأوسمة'
];
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">
<link rel="stylesheet" href="assets/css/explorer.css">

<a href="admin_dashboard.php">← لوحة التحكم</a>

<h1>حذف العناصر</h1>

<p class="admin-hint">احذف العناصر القديمة أو غير الصحيحة. سيُطلب منك التأكيد قبل الحذف.</p>

<?php if ($message !== ''): ?>
    <p class="quiz-feedback correct"><?= e($message) ?></p>
<?php endif; ?>

<?php foreach ($groups as $type => $title): ?>

    <h2><?= e($title) ?></h2>

    <?php if (count($items[$type]) === 0): ?>
        <p>لا توجد عناصر.</p>
    <?php else: ?>
        <table class="admin-table">
            <?php foreach ($items[$type] as $item): ?>
                <tr>
                    <td><?= e($item['label']) ?></td>
                    <td style="width:90px;">
                        <a href="delete.php?type=<?= $type ?>&id=<?= (int)$item['id'] ?>">حذف</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

<?php endforeach; ?>

<br>
<a href="admin_dashboard.php">العودة إلى لوحة التحكم</a>

<?php include 'views/footer.php'; ?>
