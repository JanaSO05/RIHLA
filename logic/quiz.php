<?php

// The quiz (called challenge for the child), points and badges

require_once __DIR__ . '/../data/quizzes.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// The userID of the child who is logged in, or null
function currentChildID() {

    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'child') {
        return null;
    }

    if (!isset($_SESSION['userID'])) {
        return null;
    }

    return (int)$_SESSION['userID'];
}

// Get the quiz of a destination with its questions and choices
function getPlayableQuiz($destinationID) {

    $destination = findDestination($destinationID);

    if ($destination === null || $destination['status'] !== 'published') {
        return ['error' => 'الوجهة غير موجودة.'];
    }

    $quiz = findQuizByDestination($destinationID);
    $questions = $quiz === null ? [] : findQuestions($quiz['quizID']);

    if (count($questions) === 0) {
        return ['error' => 'لا يوجد تحدٍّ لهذه الوجهة حتى الآن.', 'destination' => $destination];
    }

    foreach ($questions as $i => $question) {
        $questions[$i]['choices'] = findChoices($question['questionID']);
    }

    return [
        'error'       => '',
        'destination' => $destination,
        'quiz'        => $quiz,
        'questions'   => $questions
    ];
}

// Creates a new progress record for a quiz in the session
function startQuizProgress($destinationID, $quiz, $questions) {

    // the question IDs in order
    $questionIDs = [];
    foreach ($questions as $q) {
        $questionIDs[] = (int)$q['questionID'];
    }

    $_SESSION['quiz_progress'][$destinationID] = [
        'quizID'      => (int)$quiz['quizID'],
        'questionIDs' => $questionIDs,
        'index'       => 0,       // the question shown now
        'answered'    => false,   // true after the child answers it
        'chosenID'    => 0,       // the choice the child picked for it
        'correct'     => []       // questionID => true / false
    ];

    unset($_SESSION['quiz_result'][$destinationID]);
}

// The progress of the quiz of a destination, or null
function getQuizProgress($destinationID) {
    return $_SESSION['quiz_progress'][$destinationID] ?? null;
}

// Answering a question

// Save the answer of the current question. Returns an error or ''
function answerCurrentQuestion($destinationID, $choiceID) {

    $progress = getQuizProgress($destinationID);

    if ($progress === null) {
        return 'ابدأ التحدي أولًا.';
    }

    if ($progress['answered']) {
        return '';   // already answered, the result is already shown
    }

    if ($choiceID <= 0) {
        return 'اختر إجابة أولًا.';
    }

    $questionID = $progress['questionIDs'][$progress['index']];
    $chosen = null;

    foreach (findChoices($questionID) as $choice) {
        if ((int)$choice['choiceID'] === $choiceID) {
            $chosen = $choice;
        }
    }

    if ($chosen === null) {
        return 'اختر إجابة من الخيارات الموجودة.';
    }

    $progress['answered'] = true;
    $progress['chosenID'] = $choiceID;
    $progress['correct'][$questionID] = (bool)$chosen['isCorrect'];

    $_SESSION['quiz_progress'][$destinationID] = $progress;

    return '';
}

// Moves to the next question. Returns 'next', 'finished', or an error message.
function goToNextQuestion($destinationID) {

    $progress = getQuizProgress($destinationID);

    if ($progress === null) {
        return 'ابدأ التحدي أولًا.';
    }

    if (!$progress['answered']) {
        return 'اختر إجابة أولًا.';
    }

    $progress['index']++;
    $progress['answered'] = false;
    $progress['chosenID'] = 0;

    $_SESSION['quiz_progress'][$destinationID] = $progress;

    if ($progress['index'] >= count($progress['questionIDs'])) {
        return 'finished';
    }

    return 'next';
}

// Points and badges

// pointsEarned = FLOOR(score × totalPoints ÷ number of questions)
function calculatePointsEarned($score, $quizPoints, $questionCount) {

    if ($questionCount <= 0) {
        return 0;
    }

    return (int)floor($score * $quizPoints / $questionCount);
}

// After the last question: save the result (points only the first time)
function finishQuiz($destinationID, $childID) {

    $progress = getQuizProgress($destinationID);

    if ($progress === null || count($progress['correct']) < count($progress['questionIDs'])) {
        return 'أجب عن جميع الأسئلة أولًا.';
    }

    $quiz = findQuiz($progress['quizID']);

    if ($quiz === null) {
        unset($_SESSION['quiz_progress'][$destinationID]);
        return 'لا يوجد تحدٍّ لهذه الوجهة حتى الآن.';
    }

    $questionCount = count($progress['questionIDs']);
    // count the correct answers
    $score = 0;
    foreach ($progress['correct'] as $isCorrect) {
        if ($isCorrect) {
            $score++;
        }
    }

    $result = [
        'score'         => $score,
        'questionCount' => $questionCount,
        'pointsEarned'  => 0,
        'firstTime'     => false,
        'newBadges'     => []
    ];

    if (findQuizAttempt($childID, $quiz['quizID']) === null) {

        $points = calculatePointsEarned($score, (int)$quiz['totalPoints'], $questionCount);

        $result['newBadges']    = saveAttemptWithRewards($childID, $quiz['quizID'], $score, $points);
        $result['pointsEarned'] = $points;
        $result['firstTime']    = true;
    }

    $_SESSION['quiz_result'][$destinationID] = $result;
    unset($_SESSION['quiz_progress'][$destinationID]);

    return '';
}

// Total points and badges of a child
function getChildRewards($childID) {

    $child = findChild($childID);

    return [
        'totalPoints' => $child === null ? 0 : (int)$child['totalPoints'],
        'badges'      => findEarnedBadges($childID)
    ];
}

// IDs of the destinations the child completed
function getCompletedDestinationIDs($childID) {
    return findCompletedDestinationIDs($childID);
}
