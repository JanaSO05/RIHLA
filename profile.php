<?php
// My Profile page: view and edit the explorer profile
session_start();

require_once 'logic/explorer.php';

if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$childID = currentChildID();
$errors = [];
$typed = null;

if ($childID !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $typed = [
        'displayName' => $_POST['display_name'] ?? '',
        'avatar'      => $_POST['avatar'] ?? ''
    ];

    $errors = editExplorer($childID, $typed['displayName'], $typed['avatar']);

    if (count($errors) === 0) {
        $_SESSION['explorer_message'] = 'تم حفظ التعديلات.';
        header('Location: profile.php');
        exit;
    }
}

$message = $_SESSION['explorer_message'] ?? '';
unset($_SESSION['explorer_message']);

$explorer = $childID !== null ? findExplorer($childID) : null;
$rewards  = $childID !== null ? getChildRewards($childID) : null;
$editing  = isset($_GET['edit']) || count($errors) > 0;

$shown = $explorer;
if ($typed !== null) {
    $shown['displayName'] = $typed['displayName'];
    $shown['avatar'] = $typed['avatar'];
}
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">
<link rel="stylesheet" href="assets/css/explorer.css">

<a href="index.php">← الرئيسية</a>

<h1>ملفي</h1>

<?php if ($explorer === null): ?>

    <p class="quiz-message">صفحة الملف متاحة لحسابات الأطفال فقط.</p>

<?php else: ?>

    <?php if ($message !== ''): ?>
        <p class="quiz-feedback correct"><?= e($message) ?></p>
    <?php endif; ?>

    <div class="quiz-card profile-card">
        <img class="profile-avatar" src="<?= e(avatarPath($explorer['avatar'])) ?>" alt="صورتي الرمزية">
        <h2><?= e($explorer['displayName']) ?></h2>
        <p>نقاطي: <strong><?= (int)$rewards['totalPoints'] ?></strong></p>

        <h3 class="plain">أوسمتي</h3>
        <?php if (count($rewards['badges']) === 0): ?>
            <p>لم تحصل على وسام بعد. أكمل تحديًا لتحصل على أول وسام!</p>
        <?php else: ?>
            <div class="badge-list">
                <?php foreach ($rewards['badges'] as $badge): ?>
                    <div class="badge-item">
                        <img src="<?= e($badge['iconPath']) ?>" alt="<?= e($badge['title']) ?>">
                        <strong><?= e($badge['title']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$editing): ?>
            <p><a class="explorer-button" href="profile.php?edit=1">تعديل الملف</a></p>
        <?php endif; ?>
    </div>

    <?php if ($editing): ?>

        <h2>تعديل الملف</h2>

        <?php if (count($errors) > 0): ?>
            <div class="quiz-message">
                <p>لم يتم حفظ التعديلات:</p>
                <?php foreach ($errors as $error): ?>
                    <p>• <?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="admin-form explorer-form" method="POST" action="profile.php">
            <label>اسم المستكشف:</label>
            <input type="text" name="display_name" maxlength="30" value="<?= e($shown['displayName']) ?>">

            <label>الصورة الرمزية:</label>
            <div class="avatar-picker">
                <?php foreach (getAvatars() as $file => $label): ?>
                    <label class="avatar-option">
                        <input type="radio" name="avatar" value="<?= e($file) ?>" <?= $shown['avatar'] === $file ? 'checked' : '' ?>>
                        <img src="<?= e(avatarPath($file)) ?>" alt="<?= e($label) ?>">
                    </label>
                <?php endforeach; ?>
            </div>

            <button type="submit">حفظ</button>
            <a href="profile.php">إلغاء</a>
        </form>

    <?php endif; ?>

<?php endif; ?>

<?php include 'views/footer.php'; ?>
