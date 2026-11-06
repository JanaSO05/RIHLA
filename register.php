<?php
// Register / Create Explorer Profile page
session_start();

require_once 'logic/explorer.php';

$errors = [];
$typed = ['username' => '', 'displayName' => '', 'avatar' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $typed = [
        'username'    => trim($_POST['username'] ?? ''),
        'displayName' => trim($_POST['display_name'] ?? ''),
        'avatar'      => $_POST['avatar'] ?? ''
    ];

    $result = createExplorer($typed['username'], $_POST['password'] ?? '', $typed['displayName'], $typed['avatar']);
    $errors = $result['errors'];

    if (count($errors) === 0) {
        $_SESSION['explorer_message'] = 'تم إنشاء ملف المستكشف! رحلتك وتقدّمك سيُحفظان فيه.';
        header('Location: profile.php');
        exit;
    }
}
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">
<link rel="stylesheet" href="assets/css/explorer.css">

<h1>أنشئ ملف المستكشف</h1>

<p>اختر اسمًا وصورة رمزية. لا نطلب اسمك الحقيقي أو عمرك أو أي معلومات شخصية.</p>

<?php if (count($errors) > 0): ?>
    <div class="quiz-message">
        <p>لم يتم إنشاء الملف:</p>
        <?php foreach ($errors as $error): ?>
            <p>• <?= e($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="admin-form explorer-form" method="POST" action="register.php">

    <label>اسم المستكشف (يظهر في الموقع):</label>
    <input type="text" name="display_name" maxlength="30" value="<?= e($typed['displayName']) ?>">

    <label>اختر صورتك الرمزية:</label>
    <div class="avatar-picker">
        <?php foreach (getAvatars() as $file => $label): ?>
            <label class="avatar-option">
                <input type="radio" name="avatar" value="<?= e($file) ?>" <?= $typed['avatar'] === $file ? 'checked' : '' ?>>
                <img src="<?= e(avatarPath($file)) ?>" alt="<?= e($label) ?>">
            </label>
        <?php endforeach; ?>
    </div>

    <label>اسم المستخدم (للدخول، بالإنجليزي):</label>
    <input type="text" name="username" maxlength="30" dir="ltr" value="<?= e($typed['username']) ?>">

    <label>كلمة المرور:</label>
    <input type="password" name="password" dir="ltr">

    <button type="submit">أنشئ الملف</button>

    <p>لديك حساب؟ <a href="login.php">سجّل الدخول</a></p>
</form>

<?php include 'views/footer.php'; ?>
