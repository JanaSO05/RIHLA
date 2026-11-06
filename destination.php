<?php
session_start();

// Only logged-in users can open this page
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

require_once 'logic/journeys.php';
require_once 'logic/explorer.php';
require_once 'logic/images.php';

$id   = (int)($_GET['id'] ?? 0);
$dest = getDestination($id);

if ($dest === null) {
    include 'views/header.php';
    echo "<p>الوجهة غير موجودة.</p>";
    echo "<a href='index.php'>← الرئيسية</a>";
    include 'views/footer.php';
    exit;
}

// Only the published landmarks
$landmarkList = getVisibleLandmarks($id);
?>

<?php include 'views/header.php'; ?>

<a href="destinations.php?journey=<?= $dest['journey'] ?>">← رجوع</a>

<h1><?= e($dest['name']) ?></h1>

<?php if (imageExists($dest['imagePath'])): ?>
  <img class="destination-image" src="<?= e($dest['imagePath']) ?>" alt="<?= e($dest['name']) ?>">
<?php endif; ?>

<p><?= e($dest['desc']) ?></p>

<h2>المعالم</h2>

<?php foreach ($landmarkList as $lm): ?>
  <h3><a href="landmark.php?id=<?= (int)$lm['landmarkID'] ?>"><?= e($lm['name']) ?></a></h3>
  <?php if (imageExists($lm['imagePath'])): ?>
    <a href="landmark.php?id=<?= (int)$lm['landmarkID'] ?>">
      <img class="landmark-thumb" src="<?= e($lm['imagePath']) ?>" alt="<?= e($lm['name']) ?>">
    </a>
  <?php endif; ?>
  <p><?= e($lm['description']) ?></p>
<?php endforeach; ?>

<!-- the heritage quiz of this destination (shown to the child as a challenge) -->
<link rel="stylesheet" href="assets/css/challenge.css">
<div class="kid-actions">
  <a class="kid-btn btn-yellow quiz-start" href="quiz.php?destination=<?= $id ?>&start=1">جاهز للتحدي</a>
</div>

<?php $guideDestinationID = $id; include 'views/guide.php'; ?>

<?php include 'views/footer.php'; ?>