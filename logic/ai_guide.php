<?php

// AI Cultural Guide (Section 4.4.1)
// The guide answers only from the RIHLA content.
// Only the question is sent to the AI, and nothing is saved.

require_once __DIR__ . '/journeys.php';
require_once __DIR__ . '/config.php';

// Get the content of one destination (or all destinations if id = 0)
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

// Write the message that we send to the AI
function makePrompt($question, $sections, $context) {

    $prompt = "You are a cultural guide for Saudi children aged 7 to 11.\n";
    $prompt .= "Answer in short and simple Arabic.\n";
    $prompt .= "Use only the sections below.\n";
    $prompt .= "Write the numbers of the sections you used, then | then the answer. Example: 2,3|answer\n";
    $prompt .= "If the answer is not in the sections, write NOT_FOUND\n\n";

    // number each section: 1, 2, 3, ...
    $number = 1;
    foreach ($sections as $section) {
        $prompt .= $number . ') ' . $section . "\n";
        $number++;
    }

    $prompt .= "\nLast questions and answers:\n" . $context . "\n";
    $prompt .= "Question: " . $question;

    return $prompt;
}

// Send the prompt to the AI service (Gemini) and return its reply
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
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);      // return the reply as text
    curl_setopt($ch, CURLOPT_TIMEOUT, $AI_TIME_LIMIT);   // do not wait more than the time limit
    $response = curl_exec($ch);
    curl_close($ch);

    // no reply (no internet or too late)
    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return null;
    }

    $reply = $data['candidates'][0]['content']['parts'][0]['text'];

    // the AI sometimes adds ** or ` for bold text, so we remove them
    $reply = str_replace(['*', '`'], '', $reply);

    return trim($reply);
}

// Check the reply before showing it (IsGrounded)
// A good reply looks like "2,3|answer"
function isGrounded($reply, $sectionsCount) {

    if ($reply == '' || strpos($reply, 'NOT_FOUND') !== false) {
        return false;
    }

    // the reply must have |
    if (strpos($reply, '|') === false) {
        return false;
    }

    $parts   = explode('|', $reply, 2);   // [0] = numbers, [1] = answer
    $numbers = explode(',', $parts[0]);

    // every number must be one of the sections we sent
    foreach ($numbers as $n) {
        $n = (int) trim($n);
        if ($n < 1 || $n > $sectionsCount) {
            return false;
        }
    }

    // the answer must not be empty
    if (trim($parts[1]) == '') {
        return false;
    }

    return true;
}

// AskAIGuide
function askAIGuide($destinationID, $question, $context) {

    // 1) empty question
    if ($question == '') {
        return 'اكتب سؤالك من فضلك ✏️';
    }

    // 2) get the approved content
    $sections = getGuideSections($destinationID);

    if (count($sections) == 0) {
        return 'لا توجد معلومات عن هذا المكان حتى الآن';
    }

    // 3) send the question to the AI
    $prompt = makePrompt($question, $sections, $context);
    $reply  = callAIService($prompt);

    // 4) no reply in time
    if ($reply === null) {
        return 'المرشد لا يستطيع الإجابة الآن، حاول مرة أخرى 🙏';
    }

    // 5) the reply is not from our content
    if (!isGrounded($reply, count($sections))) {

        if ($destinationID == 0) {
            return 'أستطيع الإجابة فقط عن وجهات رحلة 😊';
        }

        $destination = getDestination($destinationID);
        return 'أستطيع الإجابة فقط عن أسئلة ' . $destination['name'] . ' 😊';
    }

    // 6) show only the answer (the part after |)
    $parts = explode('|', $reply, 2);
    return trim($parts[1]);
}