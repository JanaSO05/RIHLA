<?php


// The guide answers only from the RIHLA content.

require_once __DIR__ . '/journeys.php';
require_once __DIR__ . '/config.php';

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

function makePrompt($question, $sections, $context) {

    $prompt = "You are a cultural guide for Saudi children aged 7 to 11.\n";
    $prompt .= "Answer in short and simple Arabic.\n";
    $prompt .= "Use only the sections below.\n";
    $prompt .= "Write the numbers of the sections you used, then | then the answer. Example: 2,3|answer\n";
    $prompt .= "If the answer is not in the sections, write NOT_FOUND\n\n";

    $number = 1;
    foreach ($sections as $section) {
        $prompt .= $number . ') ' . $section . "\n";
        $number++;
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

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return null;
    }

    $reply = $data['candidates'][0]['content']['parts'][0]['text'];

    $reply = str_replace(['*', '`'], '', $reply);

    return trim($reply);
}


function isGrounded($reply, $sectionsCount) {

    if ($reply == '' || strpos($reply, 'NOT_FOUND') !== false) {
        return false;
    }

    if (strpos($reply, '|') === false) {
        return false;
    }

    $parts   = explode('|', $reply, 2);   
    $numbers = explode(',', $parts[0]);

    foreach ($numbers as $n) {
        $n = (int) trim($n);
        if ($n < 1 || $n > $sectionsCount) {
            return false;
        }
    }

    if (trim($parts[1]) == '') {
        return false;
    }

    return true;
}

// AskAIGuide
function askAIGuide($destinationID, $question, $context) {

    if ($question == '') {
        return 'اكتب سؤالك من فضلك ✏️';
    }

    $sections = getGuideSections($destinationID);

    if (count($sections) == 0) {
        return 'لا توجد معلومات عن هذا المكان حتى الآن';
    }

    $prompt = makePrompt($question, $sections, $context);
    $reply  = callAIService($prompt);

    if ($reply === null) {
        return 'المرشد لا يستطيع الإجابة الآن، حاول مرة أخرى 🙏';
    }

    if (!isGrounded($reply, count($sections))) {

        if ($destinationID == 0) {
            return 'أستطيع الإجابة فقط عن وجهات رحلة 😊';
        }

        $destination = getDestination($destinationID);
        return 'أستطيع الإجابة فقط عن أسئلة ' . $destination['name'] . ' 😊';
    }

    $parts = explode('|', $reply, 2);
    return trim($parts[1]);
}
