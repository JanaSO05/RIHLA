<?php
// Heritage Challenge page (the Heritage Quiz): one question at a time
require_once 'logic/quiz.php';

// The challenge is for logged-in users only
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$destinationID = (int)($_GET['destination'] ?? $_POST['destination'] ?? 0);
$childID = currentChildID();
$message = '';
$data = getPlayableQuiz($destinationID);
$pageURL = 'quiz.php?destination=' . $destinationID;

if ($childID !== null && $data['error'] === '') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $action = $_POST['action'] ?? '';

        if ($action === 'answer') {

            $message = answerCurrentQuestion($destinationID, (int)($_POST['choice'] ?? 0));

            if ($message === '') {
                header('Location: ' . $pageURL);
                exit;
            }

        } elseif ($action === 'next') {

            $step = goToNextQuestion($destinationID);

            if ($step === 'next') {
                header('Location: ' . $pageURL);
                exit;
            }

            if ($step === 'finished') {
                $message = finishQuiz($destinationID, $childID);

                if ($message === '') {
                    header('Location: quiz_result.php?destination=' . $destinationID);
                    exit;
                }
            } else {
                $message = $step;
            }
        }
    }

    // Start a new challenge when asked, or when there is none open
    $progress = getQuizProgress($destinationID);

    if (isset($_GET['start']) || $progress === null
        || $progress['quizID'] !== (int)$data['quiz']['quizID']) {

        startQuizProgress($destinationID, $data['quiz'], $data['questions']);
        $progress = getQuizProgress($destinationID);
    }

    // The question shown now (the administrator may have changed the quiz)
    $currentID = $progress['questionIDs'][$progress['index']] ?? 0;
    $question  = null;

    foreach ($data['questions'] as $q) {
        if ((int)$q['questionID'] === $currentID) {
            $question = $q;
        }
    }

    if ($question === null) {
        startQuizProgress($destinationID, $data['quiz'], $data['questions']);
        header('Location: ' . $pageURL);
        exit;
    }

    $questionNumber = $progress['index'] + 1;
    $questionCount  = count($progress['questionIDs']);
    $isLast         = $questionNumber === $questionCount;
    $letters        = ['أ', 'ب', 'ج', 'د'];
}
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/challenge.css">

<div class="ch-page">

<?php if ($childID === null || $data['error'] !== ''): ?>

    <div class="ch-card reward-card">
        <p class="reward-title">رحلة</p>
        <p class="score-text">
            <?php if ($childID === null): ?>
                <?= ($_SESSION['role'] ?? '') === 'admin' ? 'التحدي متاح لحسابات الأطفال فقط.' : 'سجّل الدخول مرة أخرى لتبدأ التحدي.' ?>
            <?php else: ?>
                <?= e($data['error']) ?>
            <?php endif; ?>
        </p>
        <div class="kid-actions">
            <a class="kid-btn btn-blue" href="index.php">الرئيسية</a>
        </div>
    </div>

<?php else: ?>

    <div class="ch-top">
        <a class="kid-btn btn-white btn-small" href="destination.php?id=<?= $destinationID ?>">رجوع</a>
        <h1 class="ch-title"><?= e($data['quiz']['title']) ?></h1>
    </div>

    <!-- one circle for each question -->
    <div class="ch-steps">
        <?php for ($i = 1; $i <= $questionCount; $i++): ?>
            <span class="<?= $i < $questionNumber ? 'done' : ($i === $questionNumber ? 'now' : '') ?>"><?= $i ?></span>
        <?php endfor; ?>
    </div>

    <div class="ch-card">

        <p class="ch-count">السؤال <?= $questionNumber ?> من <?= $questionCount ?></p>
        <h2 class="ch-question"><?= e($question['text']) ?></h2>
        <img class="ch-image" src="<?= e($question['imagePath']) ?>" alt="صورة السؤال">

        <?php if (!$progress['answered']): ?>

            <form method="POST" action="<?= e($pageURL) ?>">
                <input type="hidden" name="action" value="answer">
                <input type="hidden" name="destination" value="<?= $destinationID ?>">

                <div class="ch-choices">
                    <?php foreach ($question['choices'] as $i => $choice): ?>
                        <label class="ch-choice">
                            <input type="radio" name="choice" value="<?= (int)$choice['choiceID'] ?>">
                            <span class="ch-letter"><?= $letters[$i] ?? $i + 1 ?></span>
                            <?php if (!empty($choice['imagePath'])): ?>
                                <img src="<?= e($choice['imagePath']) ?>" alt="<?= e($choice['text'] ?? 'خيار') ?>">
                            <?php endif; ?>
                            <span class="ch-text"><?= e($choice['text'] ?? '') ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <?php if ($message !== ''): ?>
                    <p class="ch-alert"><?= e($message) ?></p>
                <?php endif; ?>

                <div class="kid-actions">
                    <button type="submit" class="kid-btn btn-yellow">تأكيد الإجابة</button>
                </div>
            </form>

        <?php else: ?>

            <?php
                $wasCorrect = $progress['correct'][$currentID] ?? false;
                $correctText = '';
                foreach ($question['choices'] as $choice) {
                    if ($choice['isCorrect']) {
                        $correctText = $choice['text'] ?? '';
                    }
                }
            ?>

            <div class="ch-choices">
                <?php foreach ($question['choices'] as $i => $choice): ?>
                    <?php
                        $class = 'ch-choice';
                        $letter = $letters[$i] ?? $i + 1;
                        if ($choice['isCorrect']) {
                            $class .= ' correct';
                            $letter = 'صح';
                        } elseif ((int)$choice['choiceID'] === $progress['chosenID']) {
                            $class .= ' wrong';
                            $letter = 'خطأ';
                        }
                    ?>
                    <div class="<?= $class ?>">
                        <span class="ch-letter"><?= $letter ?></span>
                        <?php if (!empty($choice['imagePath'])): ?>
                            <img src="<?= e($choice['imagePath']) ?>" alt="<?= e($choice['text'] ?? 'خيار') ?>">
                        <?php endif; ?>
                        <span class="ch-text"><?= e($choice['text'] ?? '') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($wasCorrect): ?>
                <p class="ch-feedback correct">إجابة صحيحة! أحسنت</p>
            <?php else: ?>
                <p class="ch-feedback wrong">
                    إجابة غير صحيحة، لا بأس! الإجابة الصحيحة باللون الأخضر<?= $correctText !== '' ? ': ' . e($correctText) : '' ?>
                </p>
            <?php endif; ?>

            <?php if ($message !== ''): ?>
                <p class="ch-alert"><?= e($message) ?></p>
            <?php endif; ?>

            <form method="POST" action="<?= e($pageURL) ?>">
                <input type="hidden" name="action" value="next">
                <input type="hidden" name="destination" value="<?= $destinationID ?>">
                <div class="kid-actions">
                    <?php if ($isLast): ?>
                        <button type="submit" class="kid-btn btn-green">أنهيت التحدي</button>
                    <?php else: ?>
                        <button type="submit" class="kid-btn btn-green">السؤال التالي</button>
                    <?php endif; ?>
                </div>
            </form>

        <?php endif; ?>

    </div>

<?php endif; ?>

</div>

<?php include 'views/footer.php'; ?>
