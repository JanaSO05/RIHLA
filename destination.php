<?php
require_once 'logic/journeys.php';

$id   = (int)($_GET['id'] ?? 0);
$dest = getDestination($id);

if ($dest === null) {
    include 'views/header.php';
    echo "<p>الوجهة غير موجودة.</p>";
    echo "<a href='index.php'>← الرئيسية</a>";
    include 'views/footer.php';
    exit;
}

$landmarkList = getLandmarks($id);
?>

<?php include 'views/header.php'; ?>

<a href="destinations.php?journey=<?= $dest['journey'] ?>">← رجوع</a>

<h1><?= $dest['name'] ?></h1>
<p><?= $dest['desc'] ?></p>

<h2>المعالم</h2>

<?php foreach ($landmarkList as $lm): ?>
  <h3><?= $lm['name'] ?></h3>
  <p><?= $lm['desc'] ?></p>
<?php endforeach; ?>

<?php $guideDestinationID = $id; include 'views/guide.php'; ?>

<?php include 'views/footer.php'; ?>