<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $role = $_POST['role'] ?? 'child';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Child login
    if ($role === 'child' && $username === 'child' && $password === '1234') {

        session_regenerate_id(true);

        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'child';
        $_SESSION['view'] = 'child';

        header('Location: index.php');
        exit;

    // Administrator login
    } elseif ($role === 'admin' && $username === 'admin' && $password === 'admin123') {

        session_regenerate_id(true);

        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'admin';
        $_SESSION['view'] = 'admin';

        header('Location: admin_dashboard.php');
        exit;

    // Administrator accessing the child interface
    } elseif ($role === 'child' && $username === 'admin' && $password === 'admin123') {

        session_regenerate_id(true);

        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'admin';
        $_SESSION['view'] = 'child';

        header('Location: index.php');
        exit;

    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
    }
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

</form>

<?php if ($error !== ''): ?>
    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<?php include __DIR__ . '/views/footer.php'; ?>
