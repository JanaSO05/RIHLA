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

// the administrator pressed a button
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    $done   = '';

    if ($action === 'save_destination') {
        $errors = saveDestinationForm($id, $_POST, $_FILES, $adminID);
        $done = $id > 0 ? 'تم حفظ تعديلات الوجهة.' : 'تم حفظ الوجهة كمسودة. انشرها لتظهر للأطفال.';

    } elseif ($action === 'publish_destination') {
        $error = publishDestination($id);
        $errors = $error === '' ? [] : [$error];
        $done = 'تم نشر الوجهة، وهي تظهر الآن للأطفال.';

    } elseif ($action === 'hide_destination') {
        hideDestination($id);
        $done = 'تم إخفاء الوجهة عن الأطفال.';

    } elseif ($action === 'save_landmark') {
        $errors = saveLandmarkForm($id, $_POST, $_FILES);
        $done = $id > 0 ? 'تم حفظ تعديلات المعلم.' : 'تم حفظ المعلم كمسودة. انشره ليظهر للأطفال.';

    } elseif ($action === 'publish_landmark') {
        $error = publishLandmark($id);
        $errors = $error === '' ? [] : [$error];
        $done = 'تم نشر المعلم.';

    } elseif ($action === 'hide_landmark') {
        hideLandmark($id);
        $done = 'تم إخفاء المعلم عن الأطفال.';
    }

    if (count($errors) === 0 && $done !== '') {
        $_SESSION['destinations_message'] = $done;
        header('Location: manage_destinations.php');
        exit;
    }
}

$message = $_SESSION['destinations_message'] ?? $_SESSION['delete_message'] ?? '';
unset($_SESSION['destinations_message'], $_SESSION['delete_message']);

$destinations = getAllDestinations();
$journeys     = getJourneys();
$statusNames  = ['draft' => 'مسودة', 'published' => 'منشورة', 'hidden' => 'مخفية'];

// The destination or landmark being edited
$editDestinationID = (int)($_GET['edit_destination'] ?? 0);
$editDestination   = $destinations[$editDestinationID] ?? null;

$editLandmarkID = (int)($_GET['edit_landmark'] ?? 0);
$editLandmark   = $editLandmarkID > 0 ? findLandmarkByID($editLandmarkID) : null;

// The values shown in the destination form
$destinationForm = [
    'name'        => $_POST['name'] ?? ($editDestination['name'] ?? ''),
    'description' => $_POST['description'] ?? ($editDestination['desc'] ?? ''),
    'journey'     => $_POST['journey'] ?? ($editDestination['journey'] ?? 'land')
];

// The values shown in the landmark form
$landmarkForm = [
    'name'        => $_POST['name'] ?? ($editLandmark['name'] ?? ''),
    'description' => $_POST['description'] ?? ($editLandmark['description'] ?? ''),
    'destination' => $_POST['destination'] ?? ($editLandmark['destinationID'] ?? '')
];

$postedAction = $_POST['action'] ?? '';
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">

<a href="admin_dashboard.php">← لوحة التحكم</a>

<h1>إدارة الوجهات والمعالم</h1>

<p class="admin-hint">الوجهة والمعلم الجديدان يُحفظان كمسودة، ولا يظهران للأطفال إلا بعد النشر.</p>

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

<h2>الوجهات ومعالمها</h2>

<table class="admin-table">
    <tr>
        <th>الصورة</th>
        <th>الاسم</th>
        <th>الرحلة</th>
        <th>الحالة</th>
        <th></th>
    </tr>

    <?php foreach ($destinations as $id => $d): ?>

        <tr>
            <td><?php if (imageExists($d['imagePath'])): ?><img class="admin-icon" src="<?= e($d['imagePath']) ?>" alt=""><?php endif; ?></td>
            <td><strong><?= e($d['name']) ?></strong></td>
            <td><?= e($journeys[$d['journey']] ?? '') ?></td>
            <td><?= e($statusNames[$d['status']] ?? $d['status']) ?></td>
            <td>
                <a href="manage_destinations.php?edit_destination=<?= $id ?>#destination-form">تعديل</a> |
                <form method="POST" action="manage_destinations.php" class="inline-form">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <?php if ($d['status'] === 'published'): ?>
                        <input type="hidden" name="action" value="hide_destination">
                        <button type="submit">إخفاء</button>
                    <?php else: ?>
                        <input type="hidden" name="action" value="publish_destination">
                        <button type="submit">نشر</button>
                    <?php endif; ?>
                </form>
                | <a href="delete.php?type=destination&id=<?= $id ?>&back=manage_destinations.php">حذف</a>
            </td>
        </tr>

        <?php foreach ($d['landmarks'] as $l): ?>
            <tr>
                <td><?php if (imageExists($l['imagePath'])): ?><img class="admin-icon" src="<?= e($l['imagePath']) ?>" alt=""><?php endif; ?></td>
                <td>— <?= e($l['name']) ?> <small>(معلم)</small></td>
                <td></td>
                <td><?= e($statusNames[$l['status']] ?? $l['status']) ?></td>
                <td>
                    <a href="manage_destinations.php?edit_landmark=<?= $l['landmarkID'] ?>#landmark-form">تعديل</a> |
                    <form method="POST" action="manage_destinations.php" class="inline-form">
                        <input type="hidden" name="id" value="<?= $l['landmarkID'] ?>">
                        <?php if ($l['status'] === 'published'): ?>
                            <input type="hidden" name="action" value="hide_landmark">
                            <button type="submit">إخفاء</button>
                        <?php else: ?>
                            <input type="hidden" name="action" value="publish_landmark">
                            <button type="submit">نشر</button>
                        <?php endif; ?>
                    </form>
                    | <a href="delete.php?type=landmark&id=<?= $l['landmarkID'] ?>&back=manage_destinations.php">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>

    <?php endforeach; ?>
</table>

<!-- ---------- add or edit a destination ---------- -->
<h2 id="destination-form"><?= $editDestination ? 'تعديل الوجهة' : 'إضافة وجهة جديدة' ?></h2>

<form class="admin-form" method="POST" enctype="multipart/form-data"
      action="manage_destinations.php<?= $editDestination ? '?edit_destination=' . $editDestinationID : '' ?>#destination-form">

    <input type="hidden" name="action" value="save_destination">
    <input type="hidden" name="id" value="<?= $editDestination ? $editDestinationID : 0 ?>">

    <?php $useForm = $postedAction === 'save_destination' || $editDestination; ?>

    <label>اسم الوجهة:</label>
    <input type="text" name="name" maxlength="50" value="<?= e($useForm ? $destinationForm['name'] : '') ?>">

    <label>وصف قصير:</label>
    <textarea name="description" rows="3"><?= e($useForm ? $destinationForm['description'] : '') ?></textarea>

    <label>الرحلة:</label>
    <select name="journey">
        <?php foreach ($journeys as $key => $label): ?>
            <option value="<?= $key ?>" <?= $destinationForm['journey'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
    </select>

    <label>صورة الوجهة (JPG أو PNG أو WEBP، حتى 5 ميجابايت)<?= $editDestination ? ' - اتركها فارغة لإبقاء الصورة الحالية' : '' ?>:</label>
    <?php if ($editDestination && imageExists($editDestination['imagePath'])): ?>
        <img class="admin-thumb" src="<?= e($editDestination['imagePath']) ?>" alt="الصورة الحالية">
    <?php endif; ?>
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
    <img class="admin-thumb image-preview" src="" alt="معاينة الصورة" style="display:none;">

    <button type="submit">حفظ الوجهة</button>
    <?php if ($editDestination): ?>
        <a href="manage_destinations.php">إلغاء التعديل</a>
    <?php endif; ?>
</form>

<!-- ---------- add or edit a landmark ---------- -->
<h2 id="landmark-form"><?= $editLandmark ? 'تعديل المعلم' : 'إضافة معلم جديد' ?></h2>

<form class="admin-form" method="POST" enctype="multipart/form-data"
      action="manage_destinations.php<?= $editLandmark ? '?edit_landmark=' . $editLandmarkID : '' ?>#landmark-form">

    <input type="hidden" name="action" value="save_landmark">
    <input type="hidden" name="id" value="<?= $editLandmark ? $editLandmarkID : 0 ?>">

    <?php $useForm = $postedAction === 'save_landmark' || $editLandmark; ?>

    <label>الوجهة التي يتبع لها المعلم:</label>
    <select name="destination">
        <option value="">اختر الوجهة</option>
        <?php foreach ($destinations as $id => $d): ?>
            <option value="<?= $id ?>" <?= $useForm && $landmarkForm['destination'] == $id ? 'selected' : '' ?>><?= e($d['name']) ?></option>
        <?php endforeach; ?>
    </select>

    <label>اسم المعلم:</label>
    <input type="text" name="name" maxlength="80" value="<?= e($useForm ? $landmarkForm['name'] : '') ?>">

    <label>وصف قصير:</label>
    <textarea name="description" rows="3"><?= e($useForm ? $landmarkForm['description'] : '') ?></textarea>

    <label>صورة المعلم<?= $editLandmark ? ' - اتركها فارغة لإبقاء الصورة الحالية' : '' ?>:</label>
    <?php if ($editLandmark && imageExists($editLandmark['imagePath'])): ?>
        <img class="admin-thumb" src="<?= e($editLandmark['imagePath']) ?>" alt="الصورة الحالية">
    <?php endif; ?>
    <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
    <img class="admin-thumb image-preview" src="" alt="معاينة الصورة" style="display:none;">

    <button type="submit">حفظ المعلم</button>
    <?php if ($editLandmark): ?>
        <a href="manage_destinations.php">إلغاء التعديل</a>
    <?php endif; ?>
</form>

<br>
<a href="admin_dashboard.php">العودة إلى لوحة التحكم</a>


<script>
// Show each chosen image before saving it
var fileInputs = document.querySelectorAll('input[type=file]');

for (var i = 0; i < fileInputs.length; i++) {
    fileInputs[i].addEventListener('change', function () {
        var preview = this.nextElementSibling;

        if (this.files[0]) {
            preview.src = URL.createObjectURL(this.files[0]);
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    });
}
</script>

<?php include 'views/footer.php'; ?>
