<?php
require_once 'logic/journeys.php';
$journeys = getJourneys();
?>

<?php include 'views/header.php'; ?>

<h1>مرحبًا بك في رحلة</h1>
<p>اختر رحلتك وابدأ الاستكشاف</p>

<video class="home-video" autoplay muted loop playsinline poster="assets/images/rihla-home-poster.jpg">
  <source src="assets/videos/rihla-home.mp4" type="video/mp4">
</video>

<?php foreach ($journeys as $key => $name): ?>
  <h2>
    <a href="destinations.php?journey=<?= $key ?>"><?= $name ?></a>
  </h2>
<?php endforeach; ?>

<?php include 'views/guide.php'; ?>

<?php include 'views/footer.php'; ?>