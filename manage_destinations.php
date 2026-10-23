<?php
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// The destinations come from the same place the children's pages read from
require_once 'logic/journeys.php';

$destinations = getAllDestinations();

$message   = '';
$editIndex = -1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $index  = $_POST['index'] ?? '';

    $indexIsValid = ctype_digit((string)$index)
        && isset($destinations[(int)$index]);

    // Delete a destination
    if ($action === 'delete') {

        if ($indexIsValid) {
            unset($destinations[(int)$index]);
            saveAllDestinations($destinations);
            $message = 'تم حذف الوجهة بنجاح!';
        }

    // Hide a destination from children, or show it again
    } elseif ($action === 'toggle') {

        if ($indexIsValid) {

            $current = $destinations[(int)$index]['status'] ?? 'published';

            if ($current === 'published') {
                $destinations[(int)$index]['status'] = 'hidden';
                saveAllDestinations($destinations);
                $message = 'تم إخفاء الوجهة عن الأطفال.';
            } else {
                $destinations[(int)$index]['status'] = 'published';
                saveAllDestinations($destinations);
                $message = 'تم إظهار الوجهة للأطفال.';
            }
        }

    // Add a new destination or save an edited one
    } elseif ($action === 'save') {

        $name     = trim($_POST['destination_name'] ?? '');
        $landmark = trim($_POST['landmark_name'] ?? '');
        $journey  = $_POST['journey'] ?? 'land';

        if (!array_key_exists($journey, getJourneys())) {
            $journey = 'land';
        }

        if ($name !== '' && $landmark !== '') {

            // Check for another destination with the same name
            $exists = false;

            foreach ($destinations as $i => $existingDestination) {
                if ($existingDestination['name'] === $name) {
                    if (!$indexIsValid || $i !== (int)$index) {
                        $exists = true;
                        break;
                    }
                }
            }

            if ($exists) {

                $message = 'هذه الوجهة موجودة بالفعل!';

            } elseif ($indexIsValid) {

                // Edit: keep the description and the status as they are
                $id = (int)$index;

                $destinations[$id]['name']      = $name;
                $destinations[$id]['journey']   = $journey;
                $destinations[$id]['landmarks'] = [
                    [
                        'name' => $landmark,
                        'desc' => $destinations[$id]['landmarks'][0]['desc'] ?? ''
                    ]
                ];

                saveAllDestinations($destinations);
                $message = 'تم تعديل الوجهة بنجاح!';

            } else {

                // Add: give the new destination the next free ID
                $newId = $destinations
                    ? max(array_keys($destinations)) + 1
                    : 1;

                $destinations[$newId] = [
                    'name'      => $name,
                    'desc'      => '',
                    'journey'   => $journey,
                    'status'    => 'published',
                    'landmarks' => [
                        [
                            'name' => $landmark,
                            'desc' => ''
                        ]
                    ]
                ];

                saveAllDestinations($destinations);
                $message = 'تم إضافة الوجهة بنجاح!';
            }

        } else {
            $message = 'يرجى تعبئة جميع الحقول.';
        }
    }
}

// Select a destination to edit
if (isset($_GET['edit']) && ctype_digit((string)$_GET['edit'])) {

    $requested = (int)$_GET['edit'];

    if (isset($destinations[$requested])) {
        $editIndex = $requested;
    }
}

$selected = $editIndex >= 0
    ? $destinations[$editIndex]
    : ['name' => '', 'journey' => 'land', 'landmarks' => [['name' => '']]];

$selectedName     = $selected['name'] ?? '';
$selectedJourney  = $selected['journey'] ?? 'land';
$selectedLandmark = $selected['landmarks'][0]['name'] ?? '';
?>

<?php include 'views/header.php'; ?>

<h1>إدارة الوجهات والمعالم</h1>

<p>يمكنك إدارة الوجهات والمعالم الموجودة في رحلة.</p>

<?php if ($message !== ''): ?>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<h2>الوجهات الحالية</h2>

<ul>

    <?php foreach ($destinations as $index => $destination): ?>

        <?php
            $status    = $destination['status'] ?? 'published';
            $isHidden  = $status === 'hidden';
            $journeys  = getJourneys();
            $journeyAr = $journeys[$destination['journey']] ?? '';
        ?>

        <li>

            <h3>
                <?= htmlspecialchars($destination['name'], ENT_QUOTES, 'UTF-8') ?>
                <?php if ($isHidden): ?>
                    (مخفية عن الأطفال)
                <?php endif; ?>
            </h3>

            <p>
                الرحلة: <?= htmlspecialchars($journeyAr, ENT_QUOTES, 'UTF-8') ?>
            </p>

            <p>
                المعلم:
                <?= htmlspecialchars($destination['landmarks'][0]['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>

            <!-- Edit button -->
            <a href="manage_destinations.php?edit=<?= $index ?>">
                <button type="button">تعديل</button>
            </a>

            <!-- Hide or show button -->
            <form method="POST" action="manage_destinations.php" style="display:inline;">
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="index" value="<?= $index ?>">
                <button type="submit">
                    <?= $isHidden ? 'إظهار للأطفال' : 'إخفاء عن الأطفال' ?>
                </button>
            </form>

            <!-- Delete button -->
            <form method="POST" action="manage_destinations.php" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="index" value="<?= $index ?>">
                <button type="submit" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                    حذف
                </button>
            </form>

        </li>

    <?php endforeach; ?>

</ul>

<h2><?= $editIndex >= 0 ? 'تعديل الوجهة' : 'إضافة وجهة جديدة' ?></h2>

<form method="POST" action="manage_destinations.php">

    <input type="hidden" name="action" value="save">
    <input type="hidden" name="index" value="<?= $editIndex >= 0 ? $editIndex : '' ?>">

    <label>اسم الوجهة:</label>
    <br>
    <input type="text"
           name="destination_name"
           required
           value="<?= htmlspecialchars($selectedName, ENT_QUOTES, 'UTF-8') ?>">

    <br><br>

    <label>الرحلة:</label>
    <br>
    <select name="journey">
        <?php foreach (getJourneys() as $key => $label): ?>
            <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
                <?= $selectedJourney === $key ? 'selected' : '' ?>>
                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>

    <label>اسم المعلم:</label>
    <br>
    <input type="text"
           name="landmark_name"
           required
           value="<?= htmlspecialchars($selectedLandmark, ENT_QUOTES, 'UTF-8') ?>">

    <br><br>

    <button type="submit"><?= $editIndex >= 0 ? 'حفظ التعديلات' : 'إضافة' ?></button>

    <?php if ($editIndex >= 0): ?>
        <a href="manage_destinations.php">إلغاء التعديل</a>
    <?php endif; ?>

</form>

<br>

<a href="admin_dashboard.php">العودة إلى لوحة التحكم</a>

<?php include 'views/footer.php'; ?>
