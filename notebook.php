<?php
// Explorer Notebook page: the child's discoveries, progress, and achievements
session_start();

require_once 'logic/explorer.php';

if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$childID = currentChildID();
$notebook = $childID !== null ? getNotebook($childID) : null;
$isEmpty = $notebook !== null
    && count($notebook['discoveries']) === 0
    && $notebook['completed'] === 0
    && $notebook['totalPoints'] === 0
    && count($notebook['badges']) === 0;
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">
<link rel="stylesheet" href="assets/css/explorer.css">

<a href="index.php">← الرئيسية</a>

<h1>دفتر المستكشف</h1>

<?php if ($notebook === null): ?>

    <p class="quiz-message">الدفتر متاح لحسابات الأطفال فقط.</p>

<?php elseif ($isEmpty): ?>

    <div class="quiz-card notebook-empty">
        <p>دفترك ما زال فارغًا! اختر رحلة وافتح أول معلم لتبدأ الاستكشاف.</p>
        <p><a class="explorer-button" href="index.php">ابدأ الاستكشاف</a></p>
    </div>

<?php else: ?>

    <div class="notebook-summary">
        <div><strong><?= (int)$notebook['totalPoints'] ?></strong><span>نقطة</span></div>
        <div><strong><?= (int)$notebook['completed'] ?> / <?= (int)$notebook['available'] ?></strong><span>وجهات مكتملة</span></div>
        <div><strong><?= count($notebook['discoveries']) ?></strong><span>اكتشافات</span></div>
        <div><strong><?= count($notebook['badges']) ?></strong><span>أوسمة</span></div>
    </div>

    <h2>الوجهات</h2>
    <div class="notebook-destinations">
        <?php foreach ($notebook['destinations'] as $d): ?>
            <span class="<?= $d['completed'] > 0 ? 'done' : '' ?>">
                <?= e($d['name']) ?><?= $d['completed'] > 0 ? ' (مكتملة)' : '' ?>
            </span>
        <?php endforeach; ?>
    </div>

    <h2>أوسمتي</h2>
    <?php if (count($notebook['badges']) === 0): ?>
        <p>أكمل تحديًا لتحصل على أول وسام!</p>
    <?php else: ?>
        <div class="badge-list">
            <?php foreach ($notebook['badges'] as $badge): ?>
                <div class="badge-item">
                    <img src="<?= e($badge['iconPath']) ?>" alt="<?= e($badge['title']) ?>">
                    <strong><?= e($badge['title']) ?></strong>
                    <small><?= e($badge['description']) ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h2>اكتشافاتي</h2>
    <?php if (count($notebook['discoveries']) === 0): ?>
        <p>افتح صفحة أي معلم ليُحفظ هنا.</p>
    <?php else: ?>
        <div class="discovery-list">
            <?php foreach ($notebook['discoveries'] as $discovery): ?>
                <a class="discovery-item" href="landmark.php?id=<?= (int)$discovery['landmarkID'] ?>">
                    <strong><?= e($discovery['name']) ?></strong>
                    <small><?= e($discovery['destinationName']) ?> · <?= e(substr($discovery['savedDate'], 0, 10)) ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php include 'views/footer.php'; ?>
