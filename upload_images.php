<?php
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$message = '';
$imagePath = '';

// Upload image
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $destination = $_POST['destination'] ?? '';
    $landmark = $_POST['landmark'] ?? '';

    if ($destination !== '' && $landmark !== '' && isset($_FILES['image'])) {

        $image = $_FILES['image'];

        if ($image['error'] === UPLOAD_ERR_OK) {

            // Check image size (maximum 5 MB)
            if ($image['size'] <= 5 * 1024 * 1024) {

                // Check actual image type
                $imageInfo = getimagesize($image['tmp_name']);
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

                if ($imageInfo !== false && in_array($imageInfo['mime'], $allowedTypes, true)) {

                    $extensions = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp'
                    ];

                    $extension = $extensions[$imageInfo['mime']];

                    // Create a unique filename
                    $fileName = bin2hex(random_bytes(12)) . '.' . $extension;

                    $uploadFolder = __DIR__ . '/assets/images/';
                    $savePath = $uploadFolder . $fileName;

                    if (move_uploaded_file($image['tmp_name'], $savePath)) {

                        $imagePath = 'assets/images/' . $fileName;

                        $_SESSION['upload_message'] = 'تم رفع الصورة بنجاح!';
                        $_SESSION['uploaded_image'] = $imagePath;

                    } else {
                        $_SESSION['upload_message'] = 'حدث خطأ أثناء حفظ الصورة.';
                    }

                } else {
                    $_SESSION['upload_message'] = 'يرجى اختيار صورة JPG أو PNG أو WEBP.';
                }

            } else {
                $_SESSION['upload_message'] = 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.';
            }

        } else {
            $_SESSION['upload_message'] = 'حدث خطأ أثناء رفع الصورة.';
        }

    } else {
        $_SESSION['upload_message'] = 'يرجى تعبئة جميع الحقول واختيار صورة.';
    }

    // Redirect to prevent uploading again when refreshing
    header('Location: upload_images.php');
    exit;
}

// Show message after redirect
if (isset($_SESSION['upload_message'])) {
    $message = $_SESSION['upload_message'];
    unset($_SESSION['upload_message']);
}

// Show uploaded image after redirect
if (isset($_SESSION['uploaded_image'])) {
    $imagePath = $_SESSION['uploaded_image'];
    unset($_SESSION['uploaded_image']);
}
?>

<?php include 'views/header.php'; ?>

<h1>رفع صور الوجهات والمعالم</h1>

<p>يمكنك رفع صور جديدة للوجهات والمعالم الموجودة في رحلة.</p>

<?php if ($message !== ''): ?>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<form method="POST" action="upload_images.php" enctype="multipart/form-data">

    <label>اختر الوجهة:</label>
    <br>

    <select name="destination" required>
        <option value="">اختر الوجهة</option>
        <option value="الدرعية">الدرعية</option>
        <option value="العلا">العلا</option>
        <option value="جدة التاريخية">جدة التاريخية</option>
        <option value="البحر الأحمر">البحر الأحمر</option>
    </select>

    <br><br>

    <label>اختر المعلم:</label>
    <br>

    <select name="landmark" required>
        <option value="">اختر المعلم</option>
        <option value="حي الطريف">حي الطريف</option>
        <option value="الحجر">الحِجر</option>
        <option value="بيت نصيف">بيت نصيف</option>
    </select>

    <br><br>

    <label>اختر صورة من جهازك:</label>
    <br>

    <input type="file"
           name="image"
           id="image"
           accept="image/jpeg,image/png,image/webp"
           required>

    <br><br>

    <h2>عرض الصورة</h2>

    <img id="preview"
         src=""
         alt="عرض الصورة"
         style="display:none; max-width:300px; border-radius:15px;">

    <br><br>

    <button type="submit">رفع الصورة</button>

</form>

<?php if ($imagePath !== ''): ?>

    <h2>الصورة المرفوعة</h2>

    <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>"
         alt="الصورة المرفوعة"
         style="max-width:300px; border-radius:15px;">

<?php endif; ?>

<br>

<a href="admin_dashboard.php">العودة إلى لوحة التحكم</a>

<script>
document.getElementById('image').addEventListener('change', function() {

    const file = this.files[0];
    const preview = document.getElementById('preview');

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    } else {
        preview.src = '';
        preview.style.display = 'none';
    }

});
</script>

<?php include 'views/footer.php'; ?>