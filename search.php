<?php
require_once 'logic/search.php';

$term    = $_GET['q'] ?? '';
$results = searchByName($term);
?>

<?php include 'views/header.php'; ?>

<a href="index.php">← الرئيسية</a>

<h1>البحث</h1>

<form method="get" action="search.php">
  <input type="text" name="q" value="<?= htmlspecialchars($term) ?>" placeholder="اكتب اسم وجهة أو معلم">
  <button type="submit">ابحث</button>
</form>

<?php if ($term !== ''): ?>

  <?php if (count($results) === 0): ?>
    <p>لا توجد نتائج مطابقة.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($results as $r): ?>
        <li>
          <a href="<?= $r['link'] ?>"><?= $r['name'] ?></a>
          <small>(<?= $r['type'] ?>)</small>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

<?php endif; ?>

<?php include 'views/guide.php'; ?>

<?php include 'views/footer.php'; ?>