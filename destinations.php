<?php
require_once 'logic/journeys.php';

$journeyKey = $_GET['journey'] ?? 'land';
$journeys   = getJourneys();

if (!isset($journeys[$journeyKey])) {
    $journeyKey = 'land';
}

$list = getDestinationsByJourney($journeyKey);
?>

<?php include 'views/header.php'; ?>

<a href="index.php">← الرئيسية</a>

<h1><?= $journeys[$journeyKey] ?></h1>
<p>اختر وجهة لتبدأ استكشافها</p>

<ul>
  <?php foreach ($list as $id => $d): ?>
    <li>
      <a href="destination.php?id=<?= $id ?>"><?= $d['name'] ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<?php include 'views/footer.php'; ?>