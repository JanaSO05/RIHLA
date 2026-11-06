<?php
session_start();

// Only logged-in users can open this page
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

require_once 'logic/journeys.php';
require_once 'logic/quiz.php';

$journeyKey = $_GET['journey'] ?? 'land';
$journeys   = getJourneys();

if (!isset($journeys[$journeyKey])) {
    $journeyKey = 'land';
}

$list = getDestinationsByJourney($journeyKey);

// The destinations whose challenge the child completed are marked
$childID   = currentChildID();
$completed = $childID !== null ? getCompletedDestinationIDs($childID) : [];
?>

<?php include 'views/header.php'; ?>

<a href="index.php">← الرئيسية</a>

<h1><?= e($journeys[$journeyKey]) ?></h1>
<p>اختر وجهة لتبدأ استكشافها</p>

<form method="get" action="search.php">
  <input type="text" name="q" placeholder="اكتب اسم وجهة أو معلم">
  <button type="submit">ابحث</button>
</form>

<ul>
  <?php foreach ($list as $id => $d): ?>
    <li>
      <a href="destination.php?id=<?= $id ?>"><?= e($d['name']) ?></a>
      <?php if (in_array($id, $completed)): ?>
        <p class="done-mark">مكتملة</p>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>

<?php include 'views/guide.php'; ?>

<?php include 'views/footer.php'; ?>