<?php
session_start();

// Only administrators can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once 'logic/quiz_admin.php';

$destinationID = (int)($_GET['destination'] ?? $_POST['destination'] ?? 0);
$editID        = (int)($_GET['edit'] ?? $_POST['edit'] ?? 0);
$errors        = [];
$form          = null;   // the values the administrator typed, after an error

$destination = $destinationID > 0 ? findDestination($destinationID) : null;
$quiz        = $destination !== null ? findQuizByDestination($destinationID) : null;
$editing     = null;

// The question being edited must belong to the quiz of this destination
if ($editID > 0 && $quiz !== null) {
    $found = findQuestion($editID);
    if ($found !== null && (int)$found['quizID'] === (int)$quiz['quizID']) {
        $editing = $found;
    }
}

// Save a new or an edited question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $destination !== null) {

    $form = readQuestionForm($_POST, $_FILES);

    // If the form is too large, PHP gives an empty POST
    if (empty($_POST)) {
        $errors[] = 'حجم الصور كبير جدًا. اختر صورًا أصغر.';
    } else {
        $result = saveQuestionForm($destinationID, $editing, $form);
        $errors = $result['errors'];
    }

    if (count($errors) === 0) {
        $_SESSION['quiz_admin_message'] = $editing === null
            ? 'تم حفظ السؤال، وهو يظهر الآن في اختبار الوجهة.'
            : 'تم تحديث السؤال، ويظهر التعديل الآن في الاختبار.';
        header('Location: manage_quizzes.php?destination=' . $destinationID);
        exit;
    }
}

$message = $_SESSION['quiz_admin_message'] ?? $_SESSION['delete_message'] ?? '';
unset($_SESSION['quiz_admin_message'], $_SESSION['delete_message']);

$destinations = findDestinationsWithQuiz();
$questions = $quiz !== null ? findQuestions($quiz['quizID']) : [];

// The values shown in the form
$shown = [
    'quizTitle'  => $destination !== null ? 'تحدي ' . $destination['name'] : '',
    'quizPoints' => '50',
    'text'       => '',
    'correct'    => -1,
    'choices'    => []
];

// four empty choices in the form
for ($i = 0; $i < 4; $i++) {
    $shown['choices'][$i] = ['text' => '', 'imagePath' => ''];
}

if ($editing !== null) {
    $shown['text'] = $editing['text'];
    foreach (findChoices($editing['questionID']) as $i => $choice) {
        if ($i < 4) {
            $shown['choices'][$i] = ['text' => $choice['text'] ?? '', 'imagePath' => $choice['imagePath'] ?? ''];
            if ($choice['isCorrect']) {
                $shown['correct'] = $i;
            }
        }
    }
}

if ($form !== null) {
    $shown['quizTitle']  = $form['quizTitle'];
    $shown['quizPoints'] = $form['quizPoints'];
    $shown['text']       = $form['text'];
    $shown['correct']    = $form['correct'];
    foreach ($form['choices'] as $i => $choice) {
        $shown['choices'][$i]['text'] = $choice['text'];
    }
}

$statusNames = ['draft' => 'مسودة', 'published' => 'منشورة', 'hidden' => 'مخفية'];
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">

<a href="admin_dashboard.php">← لوحة التحكم</a>

<h1>إدارة الاختبارات</h1>

<p>اختر وجهة لإضافة أسئلة اختبارها أو تعديلها.</p>

<?php if ($message !== ''): ?>
    <p class="quiz-feedback correct"><?= e($message) ?></p>
<?php endif; ?>

<table class="admin-table">
    <tr>
        <th>الوجهة</th>
        <th>الحالة</th>
        <th>الاختبار</th>
        <th>النقاط</th>
        <th>عدد الأسئلة</th>
        <th></th>
    </tr>
    <?php foreach ($destinations as $d): ?>
        <tr<?= (int)$d['destinationID'] === $destinationID ? ' class="selected"' : '' ?>>
            <td><?= e($d['name']) ?></td>
            <td><?= e($statusNames[$d['status']] ?? $d['status']) ?></td>
            <td><?= $d['quizID'] ? e($d['title']) : 'لا يوجد اختبار' ?></td>
            <td><?= $d['quizID'] ? (int)$d['totalPoints'] : '-' ?></td>
            <td><?= (int)$d['questionCount'] ?></td>
            <td><a href="manage_quizzes.php?destination=<?= (int)$d['destinationID'] ?>">عرض الأسئلة</a></td>
        </tr>
    <?php endforeach; ?>
</table>

<?php if ($destination !== null): ?>

    <h2>أسئلة اختبار <?= e($destination['name']) ?></h2>

    <?php if (count($questions) === 0): ?>
        <p>لا توجد أسئلة بعد. أضف أول سؤال من النموذج بالأسفل، وسيُنشأ الاختبار معه.</p>
    <?php endif; ?>

    <?php foreach ($questions as $number => $q): ?>
        <div class="admin-question<?= $editing !== null && (int)$q['questionID'] === (int)$editing['questionID'] ? ' selected' : '' ?>">
            <img src="<?= e($q['imagePath']) ?>" alt="صورة السؤال">
            <div>
                <strong><?= $number + 1 ?>. <?= e($q['text']) ?></strong>
                <ul class="admin-choices">
                    <?php foreach (findChoices($q['questionID']) as $choice): ?>
                        <li class="<?= $choice['isCorrect'] ? 'is-correct' : '' ?>">
                            <?php if (!empty($choice['imagePath'])): ?>
                                <img src="<?= e($choice['imagePath']) ?>" alt="">
                            <?php endif; ?>
                            <?= e($choice['text'] ?? '') ?>
                            <?= $choice['isCorrect'] ? ' (الإجابة الصحيحة)' : '' ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="manage_quizzes.php?destination=<?= $destinationID ?>&edit=<?= (int)$q['questionID'] ?>">
                    <button type="button">تعديل</button>
                </a>
                <a href="delete.php?type=question&id=<?= (int)$q['questionID'] ?>&back=<?= urlencode('manage_quizzes.php?destination=' . $destinationID) ?>">
                    <button type="button">حذف</button>
                </a>
            </div>
        </div>
    <?php endforeach; ?>

    <h2 id="question-form"><?= $editing !== null ? 'تعديل السؤال' : 'إضافة سؤال جديد' ?></h2>

    <?php if (count($errors) > 0): ?>
        <div class="quiz-message">
            <p>لم يتم حفظ السؤال:</p>
            <?php foreach ($errors as $error): ?>
                <p>• <?= e($error) ?></p>
            <?php endforeach; ?>
            <?php if ($form !== null): ?>
                <p>أعد اختيار الصور قبل الحفظ.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form class="admin-form" method="POST" enctype="multipart/form-data"
          action="manage_quizzes.php?destination=<?= $destinationID ?><?= $editing !== null ? '&edit=' . (int)$editing['questionID'] : '' ?>#question-form">

        <input type="hidden" name="destination" value="<?= $destinationID ?>">
        <input type="hidden" name="edit" value="<?= $editing !== null ? (int)$editing['questionID'] : 0 ?>">

        <?php if ($quiz === null): ?>
            <label>عنوان الاختبار (يظهر للطفل، مثل: تحدي الدرعية):</label>
            <input type="text" name="quiz_title" maxlength="80" value="<?= e($shown['quizTitle']) ?>">

            <label>نقاط الاختبار (عند الإجابة عن كل الأسئلة بشكل صحيح):</label>
            <input type="text" inputmode="numeric" name="quiz_points" value="<?= e($shown['quizPoints']) ?>">
        <?php endif; ?>

        <label>نص السؤال (قصير):</label>
        <input type="text" name="question_text" maxlength="255" value="<?= e($shown['text']) ?>">

        <label>صورة السؤال<?= $editing !== null ? ' (اتركها فارغة لإبقاء الصورة الحالية)' : '' ?>:</label>
        <?php if ($editing !== null): ?>
            <img class="admin-thumb" src="<?= e($editing['imagePath']) ?>" alt="صورة السؤال الحالية">
        <?php endif; ?>
        <input type="file" name="question_image" accept="image/jpeg,image/png,image/webp">
        <img class="admin-thumb image-preview" src="" alt="معاينة الصورة" style="display:none;">

        <h3>خيارات الإجابة</h3>
        <p class="admin-hint">اكتب نصًا أو اختر صورة لكل خيار (خياران على الأقل)، ثم حدد الإجابة الصحيحة.</p>

        <?php foreach ($shown['choices'] as $i => $choice): ?>
            <fieldset class="admin-choice-row">
                <legend>الخيار <?= $i + 1 ?></legend>

                <input type="text" name="choice_text[<?= $i ?>]" maxlength="100"
                       placeholder="نص الخيار" value="<?= e($choice['text']) ?>">

                <?php if ($editing !== null && $choice['imagePath'] !== ''): ?>
                    <img class="admin-thumb" src="<?= e($choice['imagePath']) ?>" alt="صورة الخيار الحالية">
                    <label class="inline">
                        <input type="checkbox" name="remove_choice_image[<?= $i ?>]" value="1"> حذف الصورة
                    </label>
                <?php endif; ?>

                <input type="file" name="choice_image[<?= $i ?>]" accept="image/jpeg,image/png,image/webp">
                <img class="admin-thumb image-preview" src="" alt="معاينة الصورة" style="display:none;">

                <label class="inline correct-pick">
                    <input type="radio" name="correct" value="<?= $i ?>" <?= $shown['correct'] === $i ? 'checked' : '' ?>>
                    الإجابة الصحيحة
                </label>
            </fieldset>
        <?php endforeach; ?>

        <button type="submit"><?= $editing !== null ? 'حفظ التعديلات' : 'حفظ السؤال' ?></button>

        <?php if ($editing !== null): ?>
            <a href="manage_quizzes.php?destination=<?= $destinationID ?>">إلغاء التعديل</a>
        <?php endif; ?>
    </form>

<?php endif; ?>

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
