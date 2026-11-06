<?php
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'logic/content_admin.php';

$errors = [];
$target = $_POST['target'] ?? '';

// upload and attach the image
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // If the file is too large, PHP gives an empty POST
    if (empty($_POST)) {
        $errors = ['حجم الصورة يجب ألا يتجاوز 5 ميجابايت.'];
    } else {
        $errors = attachImage($target, $_FILES['image'] ?? null);
    }

    if (count($errors) === 0) {
        $_SESSION['upload_message'] = 'تم رفع الصورة وربطها، وهي تظهر الآن للأطفال في مكانها.';
        header('Location: upload_images.php');
        exit;
    }
}

$message = $_SESSION['upload_message'] ?? '';
unset($_SESSION['upload_message']);

$destinations = getAllDestinations();
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">

<a href="admin_dashboard.php">← لوحة التحكم</a>

<h1>رفع صور الوجهات والمعالم</h1>

<p class="admin-hint">صور أسئلة الاختبار وخياراتها تُرفع من صفحة إدارة الاختبارات.</p>

<?php if ($message !== ''): ?>
    <p class="quiz-feedback correct"><?= e($message) ?></p>
<?php endif; ?>

<?php if (count($errors) > 0): ?>
    <div class="quiz-message">
        <?php foreach ($errors as $error): ?>
            <p>• <?= e($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="admin-form" method="POST" action="upload_images.php" enctype="multipart/form-data">

    <label>اختر الوجهة أو المعلم:</label>
    <select name="target">
        <option value="">اختر</option>
        <?php foreach ($destinations as $id => $d): ?>
            <option value="destination-<?= $id ?>" <?= $target === 'destination-' . $id ? 'selected' : '' ?>>الوجهة: <?= e($d['name']) ?></option>
            <?php foreach ($d['landmarks'] as $l): ?>
                <option value="landmark-<?= $l['landmarkID'] ?>" <?= $target === 'landmark-' . $l['landmarkID'] ? 'selected' : '' ?>>&nbsp;&nbsp;— المعلم: <?= e($l['name']) ?></option>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </select>

    <label>اختر صورة من جهازك (JPG أو PNG أو WEBP، حتى 5 ميجابايت):</label>
    <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp">

    <h2>معاينة الصورة قبل الحفظ</h2>
    <img id="preview" class="admin-thumb" src="" alt="معاينة الصورة" style="display:none;">

    <button type="submit">حفظ الصورة</button>
</form>

<br>
<a href="admin_dashboard.php">العودة إلى لوحة التحكم</a>

<script>
// Show the chosen image before saving it
document.getElementById('image').addEventListener('change', function () {
    var preview = document.getElementById('preview');

    if (this.files[0]) {
        preview.src = URL.createObjectURL(this.files[0]);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php include 'views/footer.php'; ?>
