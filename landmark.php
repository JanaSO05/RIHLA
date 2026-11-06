<?php
// Landmark page: the first time a child opens it, it is saved in the notebook
session_start();

// Only logged-in users can open this page
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

require_once 'logic/journeys.php';
require_once 'logic/explorer.php';
require_once 'logic/images.php';

$id = (int)($_GET['id'] ?? 0);
$landmark = openLandmark($id);
?>

<?php include 'views/header.php'; ?>
<link rel="stylesheet" href="assets/css/quiz.css">
<link rel="stylesheet" href="assets/css/explorer.css">

<?php if ($landmark === null): ?>

    <a href="index.php">← الرئيسية</a>
    <p>المعلم غير موجود.</p>

<?php else: ?>

    <a href="destination.php?id=<?= (int)$landmark['destinationID'] ?>">← <?= e($landmark['destinationName']) ?></a>

    <h1><?= e($landmark['name']) ?></h1>

    <?php if ($landmark['newDiscovery']): ?>
        <p class="quiz-feedback correct">اكتشاف جديد! حُفظ هذا المعلم في دفتر المستكشف.</p>
    <?php endif; ?>

    <?php if (imageExists($landmark['imagePath'])): ?>
        <img class="landmark-image" src="<?= e($landmark['imagePath']) ?>" alt="<?= e($landmark['name']) ?>">
    <?php endif; ?>

    <p class="landmark-description"><?= e($landmark['description']) ?></p>

    <?php foreach ($landmark['sections'] as $section): ?>
        <div class="content-section">
            <h2><?= e($section['title']) ?></h2>
            <?php if (imageExists($section['imagePath'])): ?>
                <img src="<?= e($section['imagePath']) ?>" alt="<?= e($section['title']) ?>">
            <?php endif; ?>
            <p><?= e($section['text']) ?></p>
            <small>المصدر: <?= e($section['source']) ?></small>
        </div>
    <?php endforeach; ?>

    <?php $guideDestinationID = (int)$landmark['destinationID']; include 'views/guide.php'; ?>

<?php endif; ?>

<?php include 'views/footer.php'; ?>
