<?php
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Temporary destinations
if (!isset($_SESSION['destinations'])) {
    $_SESSION['destinations'] = [
        ['name' => 'الدرعية', 'landmark' => 'حي الطريف'],
        ['name' => 'العلا', 'landmark' => 'الحِجر'],
        ['name' => 'جدة التاريخية', 'landmark' => 'بيت نصيف']
    ];
}

$message = '';
$editIndex = -1;

// Add, edit, or delete a destination
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // Delete destination
    if ($action === 'delete') {

        $index = $_POST['index'] ?? '';

        if (ctype_digit((string)$index) &&
            isset($_SESSION['destinations'][(int)$index])) {

            array_splice($_SESSION['destinations'], (int)$index, 1);

            $message = 'تم حذف الوجهة بنجاح!';
        }

    // Add or edit destination
    } elseif ($action === 'save') {

        $name = trim($_POST['destination_name'] ?? '');
        $landmark = trim($_POST['landmark_name'] ?? '');
        $index = $_POST['index'] ?? '';

        if ($name !== '' && $landmark !== '') {

            $isEditing = (
                $index !== '' &&
                ctype_digit((string)$index) &&
                isset($_SESSION['destinations'][(int)$index])
            );

            // Check if destination already exists
            $exists = false;

            foreach ($_SESSION['destinations'] as $i => $existingDestination) {

                if ($existingDestination['name'] === $name) {

                    // Allow editing the same destination
                    if (!$isEditing || $i !== (int)$index) {
                        $exists = true;
                        break;
                    }
                }
            }

            if ($exists) {

                $message = 'هذه الوجهة موجودة بالفعل!';

            } else {

                $destination = [
                    'name' => $name,
                    'landmark' => $landmark
                ];

                if ($isEditing) {

                    $_SESSION['destinations'][(int)$index] = $destination;

                    $message = 'تم تعديل الوجهة بنجاح!';

                } else {

                    $_SESSION['destinations'][] = $destination;

                    $message = 'تم إضافة الوجهة بنجاح!';
                }
            }

        } else {

            $message = 'يرجى تعبئة جميع الحقول.';
        }
    }
}

// Select destination to edit
if (isset($_GET['edit']) && ctype_digit((string)$_GET['edit'])) {

    $index = (int)$_GET['edit'];

    if (isset($_SESSION['destinations'][$index])) {
        $editIndex = $index;
    }
}

$selectedDestination = $editIndex >= 0
    ? $_SESSION['destinations'][$editIndex]
    : ['name' => '', 'landmark' => ''];
?>

<?php include 'views/header.php'; ?>

<h1>إدارة الوجهات والمعالم</h1>

<p>يمكنك إدارة الوجهات والمعالم الموجودة في رحلة.</p>

<?php if ($message !== ''): ?>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<h2>الوجهات الحالية</h2>

<ul>

    <?php foreach ($_SESSION['destinations'] as $index => $destination): ?>

        <li>

            <h2>
                <?= htmlspecialchars($destination['name'], ENT_QUOTES, 'UTF-8') ?>
            </h2>

            <p>
                المعلم:
                <?= htmlspecialchars($destination['landmark'], ENT_QUOTES, 'UTF-8') ?>
            </p>

            <!-- Edit button -->
            <a href="manage_destinations.php?edit=<?= $index ?>">
                <button type="button">تعديل</button>
            </a>

            <!-- Delete button -->
            <form method="POST"
                  action="manage_destinations.php"
                  style="display:inline;">

                <input type="hidden" name="action" value="delete">

                <input type="hidden"
                       name="index"
                       value="<?= $index ?>">

                <button type="submit"
                        onclick="return confirm('هل أنت متأكد من الحذف؟')">
                    حذف
                </button>

            </form>

        </li>

    <?php endforeach; ?>

</ul>

<h2>
    <?= $editIndex >= 0 ? 'تعديل الوجهة' : 'إضافة وجهة جديدة' ?>
</h2>

<form method="POST" action="manage_destinations.php">

    <input type="hidden" name="action" value="save">

    <input type="hidden"
           name="index"
           value="<?= $editIndex >= 0 ? $editIndex : '' ?>">

    <label>اسم الوجهة:</label>
    <br>

    <input type="text"
           name="destination_name"
           required
           value="<?= htmlspecialchars($selectedDestination['name'], ENT_QUOTES, 'UTF-8') ?>">

    <br><br>

    <label>اسم المعلم:</label>
    <br>

    <input type="text"
           name="landmark_name"
           required
           value="<?= htmlspecialchars($selectedDestination['landmark'], ENT_QUOTES, 'UTF-8') ?>">

    <br><br>

    <button type="submit">
        <?= $editIndex >= 0 ? 'حفظ التعديلات' : 'إضافة' ?>
    </button>

    <?php if ($editIndex >= 0): ?>
        <a href="manage_destinations.php">إلغاء التعديل</a>
    <?php endif; ?>

</form>

<br>

<a href="admin_dashboard.php">العودة إلى لوحة التحكم</a>

<?php include 'views/footer.php'; ?>