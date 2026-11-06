<?php
session_start();

// Only logged-in users can open this page
if (!isset($_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

require_once 'logic/journeys.php';
$journeys = getJourneys();
?>

<?php include 'views/header.php'; ?>

<h1>مرحبًا بك في رحلة</h1>
<p>اختر رحلتك وابدأ الاستكشاف</p>

<img class="home-video" src="assets/images/rihla-home-poster.jpg" alt="رحلة">

<?php foreach ($journeys as $key => $name): ?>
  <h2>
    <a href="destinations.php?journey=<?= $key ?>"><?= $name ?></a>
  </h2>
<?php endforeach; ?>

<?php include 'views/guide.php'; ?>

<?php include 'views/footer.php'; ?>
