<?php
// AI Cultural Guide: the button and the chat.
// On a destination page write $guideDestinationID = $id; before including this file.
$guideDestinationID = $guideDestinationID ?? 0;
$guideName = 'رحلة';
if ($guideDestinationID != 0) {
    $guideName = getDestination($guideDestinationID)['name'];
}
?>
<link rel="stylesheet" href="assets/css/guide.css">

<div id="guideHint">اسألني! 👋</div>
<button id="guideButton" title="اسأل المرشد الثقافي">
  <img src="assets/images/guide-button.png" alt="المرشد الثقافي">
</button>

<div id="guideChat">
  <div class="guide-header">
    <img src="assets/images/guide-avatar.png" alt="صورة المرشد">
    <b>المرشد الثقافي <small><?= htmlspecialchars($guideName) ?></small></b>
    <button id="guideClose" title="إغلاق">✕</button>
  </div>
  <div class="guide-line"></div>

  <div id="guideMessages">
    <p class="guide">أهلًا يا مستكشف! 👋 أنا مرشدك في <?= htmlspecialchars($guideName) ?>، اسألني أي سؤال.</p>
  </div>

  <form id="guideForm">
    <input id="guideQuestion" type="text" maxlength="200" placeholder="اكتب سؤالك هنا">
    <button type="submit">إرسال</button>
  </form>
</div>

<script>
  let guideContext = []; // last 5 questions and answers, only in this page

  function showMessage(text, sender) {
    const p = document.createElement('p');
    p.textContent = text;
    p.className = sender; // 'guide' or 'child'
    document.getElementById('guideMessages').appendChild(p);
    document.getElementById('guideMessages').scrollTop = 100000;
    return p;
  }

  document.getElementById('guideButton').onclick = function () {
    document.getElementById('guideChat').style.display = 'block';
    document.getElementById('guideHint').style.display = 'none';
    this.style.display = 'none';
  };

  document.getElementById('guideClose').onclick = function () {
    document.getElementById('guideChat').style.display = 'none';
    document.getElementById('guideButton').style.display = 'block';
  };

  document.getElementById('guideForm').onsubmit = function (event) {
    event.preventDefault();
    const question = document.getElementById('guideQuestion').value.trim();
    if (question == '') {
      showMessage('اكتب سؤالك من فضلك ✏️', 'guide');
      return;
    }
    showMessage(question, 'child');
    document.getElementById('guideQuestion').value = '';
    const waiting = showMessage('المرشد يفكر... 🤔', 'guide');

    const data = new FormData();
    data.append('question', question);
    data.append('destination', '<?= (int)$guideDestinationID ?>');
    data.append('context', guideContext.join('\n'));

    fetch('ask_guide.php', { method: 'POST', body: data })
      .then(function (response) { return response.json(); })
      .then(function (result) {
        waiting.textContent = result.answer;
        guideContext.push('Child: ' + question, 'Guide: ' + result.answer);
        guideContext = guideContext.slice(-10); // keep the last 5 questions
      })
      .catch(function () {
        waiting.textContent = 'المرشد لا يستطيع الإجابة الآن، حاول مرة أخرى 🙏';
      });
  };
</script>
