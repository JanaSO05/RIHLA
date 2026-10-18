<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Temporary accounts for testing
    if ($username === 'child' && $password === '1234') {

        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'child';

        header('Location: index.php');
        exit;

    } elseif ($username === 'admin' && $password === 'admin123') {

        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'admin';

        header('Location: index.php');
        exit;

    } else {
        $error = 'اسم المستخدم أو كلمة المرور غير صحيحة.';
    }
}
?>

<?php include __DIR__ . '/views/header.php'; ?>

<h1>تسجيل الدخول</h1>

<form method="POST" action="login.php">

    <label>اسم المستخدم:</label>
    <input type="text" name="username" required>

    <br><br>

    <label>كلمة المرور:</label>
    <input type="password" name="password" required>

    <br><br>

    <button type="submit">تسجيل الدخول</button>

</form>

<?php if ($error !== ''): ?>
    <p><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php include __DIR__ . '/views/footer.php'; ?>