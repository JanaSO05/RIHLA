<?php
// Receives the question from the chat and returns the guide's answer
require_once 'logic/ai_guide.php';

$question      = mb_substr(trim($_POST['question'] ?? ''), 0, 200);
$destinationID = (int)($_POST['destination'] ?? 0);
$context       = mb_substr($_POST['context'] ?? '', 0, 1500); 

$answer = askAIGuide($destinationID, $question, $context);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['answer' => $answer]);
