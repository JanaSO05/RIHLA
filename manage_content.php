<?php
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'logic/content_admin.php';

$adminID = (int)($_SESSION['userID'] ?? 0);
$errors  = [];

$editID  = (int)($_GET['edit'] ?? 0);
$editing = $editID > 0 ? findContentSection($editID) : null;

// save the content
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $errors = saveContentForm($editing ? $editID : 0, $_POST, $_FILES, $adminID);

    if (count($errors) === 0) {
        $_SESSION['content_message'] = $editing
            ? 'تم تحديث المحتوى، ويظهر الآن للأطفال.'
            : 'تم حفظ المحتوى، ويظهر الآن في صفحة المعلم.';
        header('Location: manage_content.php');
        exit;
    }
}

$message = $_SESSION['content_message'] ?? $_SESSION['delete_message'] ?? '';
unset($_SESSION['content_message'], $_SESSION['delete_message']);

$sections     = findAllContentSections();
$destinations = getAllDestinations();

// The values shown in the form: what was typed, or the saved section
$form = [
    'landmark' => $_POST['landmark'] ?? ($editing['landmarkID'] ?? ''),
    'title'    => $_POST['title'] ?? ($editing['title'] ?? ''),
    'text'     => $_POST['text'] ?? ($editing['text'] ?? ''),
    'source'   => $_POST['source'] ?? ($editing['source'] ?? '')
];
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">

<a href="admin_dashboard.php">← لوحة التحكم</a>

<h1>إدارة المحتوى الثقافي والتاريخي</h1>

<p class="admin-hint">كل قسم محتوى يتبع لمعلم، ونصه 80 كلمة كحد أعلى، ومعلوماته من مصدر رسمي. المرشد الثقافي يجيب من هذا المحتوى فقط.</p>

<?php if ($message !== ''): ?>
    <p class="quiz-feedback correct"><?= e($message) ?></p>
<?php endif; ?>

<h2>المحتوى الحالي</h2>

<?php if (count($sections) === 0): ?>
    <p>لا يوجد محتوى بعد.</p>
<?php else: ?>
    <table class="admin-table">
        <tr>
            <th>الوجهة / المعلم</th>
            <th>العنوان</th>
            <th>المصدر</th>
            <th></th>
        </tr>
        <?php foreach ($sections as $s): ?>
            <tr>
                <td><?= e($s['destinationName']) ?> / <?= e($s['landmarkName']) ?></td>
                <td><?= e($s['title']) ?></td>
                <td><?= e($s['source']) ?></td>
                <td>
                    <a href="manage_content.php?edit=<?= (int)$s['sectionID'] ?>#content-form">تعديل</a> |
                    <a href="delete.php?type=content&id=<?= (int)$s['sectionID'] ?>&back=manage_content.php">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<h2 id="content-form"><?= $editing ? 'تحديث المحتوى' : 'إضافة محتوى جديد' ?></h2>

<?php if (count($errors) > 0): ?>
    <div class="quiz-message">
        <p>لم يتم الحفظ<?= $editing ? '، وبقيت النسخة السابقة كما هي' : '' ?>:</p>
        <?php foreach ($errors as $error): ?>
            <p>• <?= e($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form class="admin-form" method="POST" enctype="multipart/form-data"
      action="manage_content.php<?= $editing ? '?edit=' . $editID : '' ?>#content-form">

    <label>المعلم:</label>
    <select name="landmark">
        <option value="">اختر المعلم</option>
        <?php foreach ($destinations as $d): ?>
            <optgroup label="<?= e($d['name']) ?>">
                <?php foreach ($d['landmarks'] as $l): ?>
                    <option value="<?= $l['landmarkID'] ?>" <?= $form['landmark'] == $l['landmarkID'] ? 'selected' : '' ?>><?= e($l['name']) ?></option>
                <?php endforeach; ?>
            </optgroup>
        <?php endforeach; ?>
    </select>

    <label>العنوان:</label>
    <input type="text" name="title" maxlength="100" value="<?= e($form['title']) ?>">

    <label>النص (80 كلمة كحد أعلى):</label>
    <textarea name="text" rows="5"><?= e($form['text']) ?></textarea>

    <label>المصدر الرسمي:</label>
    <input type="text" name="source" maxlength="255" value="<?= e($form['source']) ?>">

    <label>صورة (اختيارية، JPG أو PNG أو WEBP حتى 5 ميجابايت):</label>
    <?php if ($editing && imageExists($editing['imagePath'])): ?>
        <img class="admin-thumb" src="<?= e($editing['imagePath']) ?>" alt="الصورة الحالية">
    <?php endif; ?>
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">

    <button type="submit"><?= $editing ? 'حفظ التحديث' : 'حفظ المحتوى' ?></button>
    <?php if ($editing): ?>
        <a href="manage_content.php">إلغاء</a>
    <?php endif; ?>
</form>

<br>
<a href="admin_dashboard.php">العودة إلى لوحة التحكم</a>

<?php include 'views/footer.php'; ?>
