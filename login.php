<?php
session_start();

require_once __DIR__ . '/logic/explorer.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $role = ($_POST['role'] ?? 'child') === 'admin' ? 'admin' : 'child';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // The username and the hashed password are checked in the database
    $result = logIn($role, $username, $password);

    if ($result['error'] === '') {
        header('Location: ' . $result['page']);
        exit;
    }

    $error = $result['error'];
}

$selectedRole = $_POST['role'] ?? 'child';
?>

<?php include __DIR__ . '/views/header.php'; ?>

<h1>تسجيل الدخول</h1>

<p>اختر نوع الحساب لتسجيل الدخول إلى رحلة.</p>

<form method="POST" action="login.php">

    <h2>نوع الحساب</h2>

    <div class="account-options">

        <label class="account-option">

            <input type="radio"
                   name="role"
                   value="child"
                   <?= $selectedRole === 'child' ? 'checked' : '' ?>>

            <span>دخول الطفل</span>

        </label>

        <label class="account-option">

            <input type="radio"
                   name="role"
                   value="admin"
                   <?= $selectedRole === 'admin' ? 'checked' : '' ?>>

            <span>دخول المسؤول</span>

        </label>

    </div>

    <br>

    <label>اسم المستخدم:</label>
    <br>
    <input type="text" name="username" required>

    <br><br>

    <label>كلمة المرور:</label>
    <br>
    <input type="password" name="password" required>

    <br><br>

    <button type="submit">تسجيل الدخول</button>

    <p style="text-align:center;">ليس لديك حساب؟ <a href="register.php">أنشئ ملف المستكشف</a></p>

</form>

<?php if ($error !== ''): ?>
    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php include __DIR__ . '/views/footer.php'; ?>
