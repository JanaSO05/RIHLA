<?php
// Challenge result: first the points, then the new badge (step=badge)
require_once 'logic/quiz.php';

if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$destinationID = (int)($_GET['destination'] ?? 0);
$childID = currentChildID();
$result = $_SESSION['quiz_result'][$destinationID] ?? null;

// No result to show: go back to the challenge
if ($childID === null || $result === null) {
    header('Location: quiz.php?destination=' . $destinationID);
    exit;
}

$rewards   = getChildRewards($childID);
$showBadge = ($_GET['step'] ?? '') === 'badge' && count($result['newBadges']) > 0;

// The points coin of this destination
$coinPath = 'assets/images/points/points-' . $destinationID . '.png';
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/challenge.css">

<div class="ch-page">
    <div class="ch-card reward-card">

    <?php if (!$showBadge): ?>

        <!-- Screen 1: the score and the points -->
        <h1 class="reward-title"><?= $result['score'] === $result['questionCount'] ? 'رائع!' : 'أحسنت!' ?></h1>

        <!-- one star for each correct answer -->
        <p class="score-stars">
            <?php for ($i = 1; $i <= $result['questionCount']; $i++): ?>
                <img class="<?= $i <= $result['score'] ? 'star-on' : 'star-off' ?>" src="assets/images/icons/star.png" alt="نجمة">
            <?php endfor; ?>
        </p>

        <p class="score-text">
            أجبت إجابة صحيحة عن <b><?= (int)$result['score'] ?></b> من <b><?= (int)$result['questionCount'] ?></b>
        </p>

        <?php if ($result['firstTime']): ?>

            <?php if (file_exists($coinPath)): ?>
                <img class="coin-img" src="<?= $coinPath ?>" alt="النقاط">
            <?php endif; ?>

            <p class="points-plus">+<?= (int)$result['pointsEarned'] ?></p>
            <p class="points-label">نقطة</p>

        <?php else: ?>

            <p class="info-note">أكملت هذا التحدي من قبل، لذلك لا تحصل على نقاط جديدة.</p>

        <?php endif; ?>

        <p class="total-pill">مجموع نقاطك: <b><?= (int)$rewards['totalPoints'] ?></b></p>

        <div class="kid-actions">
            <?php if (count($result['newBadges']) > 0): ?>
                <a class="kid-btn btn-gift" href="quiz_result.php?destination=<?= $destinationID ?>&step=badge">افتح هديتك</a>
            <?php else: ?>
                <a class="kid-btn btn-green" href="destination.php?id=<?= $destinationID ?>">العودة إلى الوجهة</a>
                <a class="kid-btn btn-blue" href="quiz.php?destination=<?= $destinationID ?>&start=1">أعد التحدي</a>
            <?php endif; ?>
        </div>

    <?php else: ?>

        <!-- Screen 2: the new badges -->
        <h1 class="reward-title">حصلت على وسام جديد!</h1>

        <?php foreach ($result['newBadges'] as $badge): ?>
            <div class="badge-stage">
                <div class="badge-rays"></div>
                <img class="badge-img" src="<?= e($badge['iconPath']) ?>" alt="<?= e($badge['title']) ?>">
            </div>
            <p class="badge-name"><?= e($badge['title']) ?></p>
            <p class="badge-desc"><?= e($badge['description']) ?></p>
        <?php endforeach; ?>

        <p class="score-text">حفظنا الوسام في دفتر المستكشف.</p>

        <div class="kid-actions">
            <a class="kid-btn btn-green" href="destination.php?id=<?= $destinationID ?>">العودة إلى الوجهة</a>
            <a class="kid-btn btn-blue" href="notebook.php">دفتري</a>
        </div>

    <?php endif; ?>

    </div>
</div>

<?php include 'views/footer.php'; ?>
