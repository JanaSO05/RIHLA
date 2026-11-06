<?php
// Receives the question from the chat and returns the guide's answer
session_start();
require_once 'logic/ai_guide.php';

header('Content-Type: application/json; charset=utf-8');

// Only logged-in users can ask the guide
if (!isset($_SESSION['role'])) {
    echo json_encode(['answer' => 'سجّل الدخول أولًا', 'found' => false]);
    exit;
}

$question      = mb_substr(trim($_POST['question'] ?? ''), 0, 200);
$destinationID = (int)($_POST['destination'] ?? 0);
$context       = mb_substr($_POST['context'] ?? '', 0, 1500);

$answer = askAIGuide($destinationID, $question, $context);

echo json_encode($answer);
