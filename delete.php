<?php
// Delete an item after the administrator confirms
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'logic/explorer.php';

$type = $_GET['type'] ?? $_POST['type'] ?? '';
$id   = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

// The page to go back to after deleting or cancelling
$backPages = ['delete_items.php', 'manage_quizzes.php', 'manage_rewards.php', 'manage_destinations.php', 'manage_content.php'];
$back = $_GET['back'] ?? $_POST['back'] ?? 'delete_items.php';
$backParts = explode('?', $back);
if (!in_array($backParts[0], $backPages)) {
    $back = 'delete_items.php';
}

$typeNames = [
    'destination' => 'الوجهة',
    'landmark'    => 'المعلم',
    'content'     => 'المحتوى الثقافي',
    'question'    => 'سؤال الاختبار',
    'badge'       => 'الوسام'
];

$item = isset($typeNames[$type]) ? findDeletableItem($type, $id) : null;

if ($item === null) {
    $_SESSION['delete_message'] = 'العنصر غير موجود.';
    header('Location: ' . $back);
    exit;
}

// The administrator confirmed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === 'yes') {
    deleteItem($type, $id);
    $_SESSION['delete_message'] = 'تم حذف ' . $typeNames[$type] . ' "' . $item['label'] . '" نهائيًا.';
    header('Location: ' . $back);
    exit;
}

$dependents = countDependents($type, $id);
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">
<link rel="stylesheet" href="assets/css/explorer.css">

<h1>تأكيد الحذف</h1>

<div class="quiz-card delete-confirm">

    <p class="quiz-message">
        هل أنت متأكد من حذف <?= e($typeNames[$type]) ?>: <strong><?= e($item['label']) ?></strong>؟
        لا يمكن التراجع عن الحذف.
    </p>

    <?php if ($type === 'destination'): ?>
        <p>سيُحذف معها أيضًا:
            <?= $dependents['landmarks'] ?> معلم،
            <?= $dependents['sections'] ?> قسم محتوى،
            واختبارها (<?= $dependents['questions'] ?> سؤال).</p>
    <?php elseif ($type === 'landmark'): ?>
        <p>سيُحذف معه أيضًا <?= $dependents['sections'] ?> قسم محتوى، واكتشافات الأطفال لهذا المعلم.</p>
    <?php elseif ($type === 'question'): ?>
        <p>سيختفي هذا السؤال من الاختبار مباشرة.</p>
    <?php elseif ($type === 'badge'): ?>
        <p>سيختفي هذا الوسام من دفاتر الأطفال الذين حصلوا عليه.</p>
    <?php endif; ?>

    <form method="POST" action="delete.php" class="inline-form">
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="back" value="<?= e($back) ?>">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" class="danger">نعم، احذف</button>
        <a class="explorer-button light" href="<?= e($back) ?>">إلغاء</a>
    </form>

</div>

<?php include 'views/footer.php'; ?>
