<?php
// The guide answers only from the RIHLA content.

require_once __DIR__ . '/journeys.php';
require_once __DIR__ . '/config.php';

// Get the content of the destination and its landmarks
function getGuideSections($destinationID) {

    $sections = [];

    foreach (getAllDestinations() as $id => $destination) {

        if ($destinationID == 0 || $destinationID == $id) {

            $sections[] = $destination['name'] . ': ' . $destination['desc'];

            foreach ($destination['landmarks'] as $landmark) {
                $sections[] = $landmark['name'] . ': ' . $landmark['desc'];
            }
        }
    }

    return $sections;
}

// Build the text that we send to the AI service
function makePrompt($question, $sections, $context) {

    $prompt = "You are a cultural guide for Saudi children aged 7 to 11.\n";
    $prompt .= "Answer in short and simple Arabic.\n";
    $prompt .= "Use only the sections below.\n";
    $prompt .= "If the answer is not in the sections, write NOT_FOUND\n\n";

    foreach ($sections as $section) {
        $prompt .= "- " . $section . "\n";
    }

    $prompt .= "\nLast questions and answers:\n" . $context . "\n";
    $prompt .= "Question: " . $question;

    return $prompt;
}

function callAIService($prompt) {

    global $AI_API_KEY, $AI_MODEL, $AI_TIME_LIMIT;

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $AI_MODEL . ':generateContent?key=' . $AI_API_KEY;

    $request = [
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $AI_TIME_LIMIT);
    $response = curl_exec($ch);
    curl_close($ch);

    // no reply in time
    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return null;
    }

    $reply = $data['candidates'][0]['content']['parts'][0]['text'];

    $reply = str_replace('*', '', $reply);

    return trim($reply);
}

// AskAIGuide: returns the answer and if it was found in the content
function askAIGuide($destinationID, $question, $context) {

    if ($question == '') {
        return ['answer' => 'اكتب سؤالك من فضلك', 'found' => false];
    }

    $sections = getGuideSections($destinationID);

    if (count($sections) == 0) {
        return ['answer' => 'لا توجد معلومات عن هذا المكان حتى الآن', 'found' => false];
    }

    $prompt = makePrompt($question, $sections, $context);
    $reply  = callAIService($prompt);

    if ($reply === null) {
        return ['answer' => 'المرشد لا يستطيع الإجابة الآن، حاول مرة أخرى', 'found' => false];
    }

    if ($reply == '' || strpos($reply, 'NOT_FOUND') !== false) {

        if ($destinationID == 0) {
            return ['answer' => 'أستطيع الإجابة فقط عن وجهات رحلة', 'found' => false];
        }

        $destination = getDestination($destinationID);
        return ['answer' => 'أستطيع الإجابة فقط عن أسئلة ' . $destination['name'], 'found' => false];
    }

    return ['answer' => $reply, 'found' => true];
}
