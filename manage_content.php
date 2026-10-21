<?php
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Temporary cultural content
if (!isset($_SESSION['cultural_content'])) {
    $_SESSION['cultural_content'] = [
        [
            'destination' => 'الدرعية',
            'landmark' => 'حي الطريف',
            'description' => 'حي الطريف موقع تاريخي في الدرعية، وكان مركزًا للدولة السعودية الأولى.'
        ],
        [
            'destination' => 'العلا',
            'landmark' => 'الحِجر',
            'description' => 'الحِجر موقع أثري في العلا، ويشتهر بالمقابر المنحوتة في الصخور.'
        ]
    ];
}
// Remove existing duplicate content
$uniqueContent = [];

foreach ($_SESSION['cultural_content'] as $content) {

    $exists = false;

    foreach ($uniqueContent as $existingContent) {

        if ($existingContent['destination'] === $content['destination'] &&
            $existingContent['landmark'] === $content['landmark']) {

            $exists = true;
            break;
        }
    }

    if (!$exists) {
        $uniqueContent[] = $content;
    }
}

$_SESSION['cultural_content'] = $uniqueContent;
$message = '';
$editIndex = -1;

// Add or update content
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $destination = trim($_POST['destination'] ?? '');
    $landmark = trim($_POST['landmark'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $index = $_POST['index'] ?? '';

    if ($destination !== '' && $landmark !== '' && $description !== '') {

        $isEditing = (
            $index !== '' &&
            ctype_digit((string)$index) &&
            isset($_SESSION['cultural_content'][(int)$index])
        );

        // Check for duplicate content
        $exists = false;

        foreach ($_SESSION['cultural_content'] as $i => $existingContent) {

            if ($existingContent['destination'] === $destination &&
                $existingContent['landmark'] === $landmark) {

                if (!$isEditing || $i !== (int)$index) {
                    $exists = true;
                    break;
                }
            }
        }

        if ($exists) {

            $_SESSION['message'] = 'هذا المحتوى موجود بالفعل!';

        } else {

            $content = [
                'destination' => $destination,
                'landmark' => $landmark,
                'description' => $description
            ];

            if ($isEditing) {

                $_SESSION['cultural_content'][(int)$index] = $content;
                $_SESSION['message'] = 'تم تعديل المحتوى بنجاح!';

            } else {

                $_SESSION['cultural_content'][] = $content;
                $_SESSION['message'] = 'تم إضافة المحتوى بنجاح!';
            }
        }

    } else {

        $_SESSION['message'] = 'يرجى تعبئة جميع الحقول.';
    }

    // Redirect to prevent adding again when refreshing
    header('Location: manage_content.php');
    exit;
}

// Show message after redirect
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Select content to edit
if (isset($_GET['edit']) && ctype_digit((string)$_GET['edit'])) {

    $requestedIndex = (int)$_GET['edit'];

    if (isset($_SESSION['cultural_content'][$requestedIndex])) {
        $editIndex = $requestedIndex;
    }
}

$selectedContent = $editIndex >= 0
    ? $_SESSION['cultural_content'][$editIndex]
    : ['destination' => '', 'landmark' => '', 'description' => ''];
?>

<?php include 'views/header.php'; ?>

<h1>إدارة المحتوى الثقافي والتاريخي</h1>

<p>يمكنك إضافة معلومات ثقافية وتاريخية جديدة أو تعديل المعلومات الموجودة.</p>

<?php if ($message !== ''): ?>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<h2>المحتوى الحالي</h2>

<ul>
    <?php foreach ($_SESSION['cultural_content'] as $index => $content): ?>
        <li>
            <h2><?= htmlspecialchars($content['destination'], ENT_QUOTES, 'UTF-8') ?></h2>

            <p>
                <strong>المعلم:</strong>
                <?= htmlspecialchars($content['landmark'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p><?= htmlspecialchars($content['description'], ENT_QUOTES, 'UTF-8') ?></p>

            <a href="manage_content.php?edit=<?= $index ?>">تعديل المحتوى</a>
        </li>
    <?php endforeach; ?>
</ul>

<h2><?= $editIndex >= 0 ? 'تعديل المحتوى' : 'إضافة محتوى جديد' ?></h2>

<form method="POST" action="manage_content.php">

    <input type="hidden" name="index" value="<?= $editIndex >= 0 ? $editIndex : '' ?>">

    <label>اسم الوجهة:</label>
    <br>
    <input type="text" name="destination" required
           value="<?= htmlspecialchars($selectedContent['destination'], ENT_QUOTES, 'UTF-8') ?>">

    <br><br>

    <label>اسم المعلم:</label>
    <br>
    <input type="text" name="landmark" required
           value="<?= htmlspecialchars($selectedContent['landmark'], ENT_QUOTES, 'UTF-8') ?>">

    <br><br>

    <label>المحتوى الثقافي والتاريخي:</label>
    <br>
    <textarea name="description" rows="5" cols="50" required><?= htmlspecialchars($selectedContent['description'], ENT_QUOTES, 'UTF-8') ?></textarea>

    <br><br>

    <button type="submit">
        <?= $editIndex >= 0 ? 'حفظ التعديلات' : 'إضافة المحتوى' ?>
    </button>

    <?php if ($editIndex >= 0): ?>
        <a href="manage_content.php">إلغاء التعديل</a>
    <?php endif; ?>

</form>

<br>

<a href="admin_dashboard.php">العودة إلى لوحة التحكم</a>

<?php include 'views/footer.php'; ?>