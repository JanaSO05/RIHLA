<?php
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'logic/quiz_admin.php';

$errors  = [];
$badgeID = (int)($_GET['badge'] ?? 0);
$typed   = null;   // the badge values typed, after an error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // Update the points of a quiz
    if ($action === 'points') {

        $error = changeQuizPoints((int)($_POST['quiz'] ?? 0), $_POST['points'] ?? '');

        if ($error === '') {
            $_SESSION['rewards_message'] = 'تم تحديث نقاط الاختبار. القيمة الجديدة تُطبّق على الاختبارات التي تُكمل من الآن.';
            header('Location: manage_rewards.php');
            exit;
        }

        $errors[] = $error;

    // Update the name and the description of a badge
    } elseif ($action === 'badge') {

        $badgeID = (int)($_POST['badge'] ?? 0);
        $typed = [
            'title'       => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];

        $errors = changeBadge($badgeID, $typed['title'], $typed['description']);

        if (count($errors) === 0) {
            $_SESSION['rewards_message'] = 'تم تحديث الوسام.';
            header('Location: manage_rewards.php');
            exit;
        }
    }
}

$message = $_SESSION['rewards_message'] ?? $_SESSION['delete_message'] ?? '';
unset($_SESSION['rewards_message'], $_SESSION['delete_message']);

$destinations = findDestinationsWithQuiz();
$badges = findBadges();
$selectedBadge = $badgeID > 0 ? findBadge($badgeID) : null;

if ($selectedBadge !== null && $typed !== null) {
    $selectedBadge['title'] = $typed['title'];
    $selectedBadge['description'] = $typed['description'];
}
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">

<a href="admin_dashboard.php">← لوحة التحكم</a>

<h1>إدارة النقاط والأوسمة</h1>

<p class="admin-hint">
    التغييرات تُطبّق على المكافآت التي يحصل عليها الأطفال بعد الحفظ فقط،
    ولا تتغير النقاط والأوسمة التي حصلوا عليها من قبل.
</p>

<?php if ($message !== ''): ?>
    <p class="quiz-feedback correct"><?= e($message) ?></p>
<?php endif; ?>

<?php if (count($errors) > 0): ?>
    <div class="quiz-message">
        <p>لم يتم الحفظ:</p>
        <?php foreach ($errors as $error): ?>
            <p>• <?= e($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h2>نقاط الاختبارات</h2>

<table class="admin-table">
    <tr>
        <th>الوجهة</th>
        <th>الاختبار</th>
        <th>عدد الأسئلة</th>
        <th>النقاط</th>
    </tr>
    <?php $hasQuiz = false; ?>
    <?php foreach ($destinations as $d): ?>
        <?php if (!$d['quizID']) { continue; } $hasQuiz = true; ?>
        <tr>
            <td><?= e($d['name']) ?></td>
            <td><?= e($d['title']) ?></td>
            <td><?= (int)$d['questionCount'] ?></td>
            <td>
                <form method="POST" action="manage_rewards.php" class="inline-form">
                    <input type="hidden" name="action" value="points">
                    <input type="hidden" name="quiz" value="<?= (int)$d['quizID'] ?>">
                    <input type="text" inputmode="numeric" name="points" class="points-input" value="<?= (int)$d['totalPoints'] ?>">
                    <button type="submit">حفظ</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php if (!$hasQuiz): ?>
    <p>لا توجد اختبارات بعد. أضف أسئلة من صفحة <a href="manage_quizzes.php">إدارة الاختبارات</a>.</p>
<?php endif; ?>

<h2>الأوسمة</h2>

<table class="admin-table">
    <tr>
        <th></th>
        <th>اسم الوسام</th>
        <th>الوصف</th>
        <th>النقاط المطلوبة</th>
        <th></th>
    </tr>
    <?php foreach ($badges as $badge): ?>
        <tr<?= $selectedBadge !== null && (int)$badge['badgeID'] === (int)$selectedBadge['badgeID'] ? ' class="selected"' : '' ?>>
            <td><img class="admin-icon" src="<?= e($badge['iconPath']) ?>" alt=""></td>
            <td><?= e($badge['title']) ?></td>
            <td><?= e($badge['description']) ?></td>
            <td><?= (int)$badge['requiredPoints'] ?></td>
            <td>
                <a href="manage_rewards.php?badge=<?= (int)$badge['badgeID'] ?>#badge-form">تعديل</a> |
                <a href="delete.php?type=badge&id=<?= (int)$badge['badgeID'] ?>&back=manage_rewards.php">حذف</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php if ($selectedBadge !== null): ?>

    <h2 id="badge-form">تعديل الوسام</h2>

    <form class="admin-form" method="POST" action="manage_rewards.php?badge=<?= (int)$selectedBadge['badgeID'] ?>#badge-form">
        <input type="hidden" name="action" value="badge">
        <input type="hidden" name="badge" value="<?= (int)$selectedBadge['badgeID'] ?>">

        <label>اسم الوسام:</label>
        <input type="text" name="title" maxlength="50" value="<?= e($selectedBadge['title']) ?>">

        <label>وصف الوسام:</label>
        <textarea name="description" rows="3" maxlength="255"><?= e($selectedBadge['description']) ?></textarea>

        <button type="submit">حفظ التعديلات</button>
        <a href="manage_rewards.php">إلغاء التعديل</a>
    </form>

<?php endif; ?>

<br>
<a href="admin_dashboard.php">العودة إلى لوحة التحكم</a>

<?php include 'views/footer.php'; ?>
