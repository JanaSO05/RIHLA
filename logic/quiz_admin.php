<?php

// Admin: add and update quiz questions, quiz points and badges

require_once __DIR__ . '/../data/quizzes.php';
require_once __DIR__ . '/quiz_images.php';

// Is it a positive whole number? (max 1000000 so it fits in INT)
function isPositiveWholeNumber($value) {
    $value = trim($value);

    if (!ctype_digit($value)) {
        return false;
    }

    return (int)$value > 0 && (int)$value <= 1000000;
}

// Adding and updating a question

// Reads the question form. $files is $_FILES.
function readQuestionForm($post, $files) {

    // the number of the correct choice (-1 if nothing was chosen)
    $correct = -1;
    if (isset($post['correct']) && ctype_digit($post['correct'])) {
        $correct = (int)$post['correct'];
    }

    $form = [
        'quizTitle'  => trim($post['quiz_title'] ?? ''),
        'quizPoints' => trim($post['quiz_points'] ?? ''),
        'text'       => trim($post['question_text'] ?? ''),
        'image'      => $files['question_image'] ?? null,
        'correct'    => $correct,
        'choices'    => []
    ];

    // the form has 4 choices
    for ($i = 0; $i < 4; $i++) {

        // PHP puts the 4 choice images in one array, so we take choice $i from it
        $image = null;

        if (isset($files['choice_image']['error'][$i])) {
            $image = [
                'name'     => $files['choice_image']['name'][$i],
                'type'     => $files['choice_image']['type'][$i],
                'tmp_name' => $files['choice_image']['tmp_name'][$i],
                'error'    => $files['choice_image']['error'][$i],
                'size'     => $files['choice_image']['size'][$i]
            ];
        }

        $form['choices'][$i] = [
            'text'        => trim($post['choice_text'][$i] ?? ''),
            'image'       => $image,
            'removeImage' => isset($post['remove_choice_image'][$i])
        ];
    }

    return $form;
}

// Check the question form and save it ($question is null when adding)
function saveQuestionForm($destinationID, $question, $form) {

    $errors = [];
    $isNew = $question === null;
    $quiz = findQuizByDestination($destinationID);

    if (findDestination($destinationID) === null) {
        return ['errors' => ['اختر وجهة موجودة.']];
    }

    // The quiz itself is created with its first question
    if ($isNew && $quiz === null) {

        if ($form['quizTitle'] === '') {
            $errors[] = 'اكتب عنوان الاختبار.';
        } elseif (mb_strlen($form['quizTitle']) > 80) {
            $errors[] = 'عنوان الاختبار يجب ألا يزيد عن 80 حرفًا.';
        }

        if (!isPositiveWholeNumber($form['quizPoints'])) {
            $errors[] = 'نقاط الاختبار يجب أن تكون عددًا صحيحًا موجبًا.';
        }
    }

    // The short text prompt
    if ($form['text'] === '') {
        $errors[] = 'اكتب نص السؤال.';
    } elseif (mb_strlen($form['text']) > 255) {
        $errors[] = 'نص السؤال يجب أن يكون قصيرًا (255 حرفًا أو أقل).';
    }

    // The visual of the question: required when adding, optional when editing
    $newQuestionImage = quizImageWasChosen($form['image']);

    if ($newQuestionImage) {
        $problem = checkQuizImage($form['image']);
        if ($problem !== '') {
            $errors[] = 'صورة السؤال: ' . $problem;
        }
    } elseif ($isNew) {
        $errors[] = 'أضف صورة للسؤال.';
    }

    // The answer choices: a choice is used if it has a text or an image
    $oldChoices = $isNew ? [] : findChoices($question['questionID']);
    $usedRows = [];

    foreach ($form['choices'] as $i => $choice) {

        $hasNewImage = quizImageWasChosen($choice['image']);
        $oldImage = $oldChoices[$i]['imagePath'] ?? '';
        $keepsOldImage = false;
        if ($oldImage != '' && !$choice['removeImage']) {
            $keepsOldImage = true;
        }

        if ($hasNewImage) {
            $problem = checkQuizImage($choice['image']);
            if ($problem !== '') {
                $errors[] = 'صورة الخيار ' . ($i + 1) . ': ' . $problem;
            }
        }

        if (mb_strlen($choice['text']) > 100) {
            $errors[] = 'نص الخيار ' . ($i + 1) . ' يجب ألا يزيد عن 100 حرف.';
        }

        if ($choice['text'] !== '' || $hasNewImage || $keepsOldImage) {
            $usedRows[$i] = [
                'hasNewImage' => $hasNewImage,
                'oldImage'    => $keepsOldImage ? $oldImage : ''
            ];
        }
    }

    if (count($usedRows) < 2) {
        $errors[] = 'أضف خيارين على الأقل (نص أو صورة لكل خيار).';
    }

    // Exactly one correct answer, and it must be one of the used choices
    if ($form['correct'] < 0) {
        $errors[] = 'حدد الإجابة الصحيحة.';
    } elseif (!isset($usedRows[$form['correct']])) {
        $errors[] = 'الإجابة الصحيحة يجب أن تكون خيارًا فيه نص أو صورة.';
    }

    if (count($errors) > 0) {
        return ['errors' => $errors];
    }

    // Everything is fine: store the images, then save
    $questionImage = $isNew ? '' : $question['imagePath'];

    if ($newQuestionImage) {
        $questionImage = storeQuizImage($form['image']);
    }

    $choices = [];

    foreach ($usedRows as $i => $row) {

        $imagePath = $row['oldImage'];

        if ($row['hasNewImage']) {
            $imagePath = storeQuizImage($form['choices'][$i]['image']);

            if ($imagePath === '') {
                return ['errors' => ['حدث خطأ أثناء حفظ الصورة.']];
            }
        }

        $choices[] = [
            'text'      => $form['choices'][$i]['text'],
            'imagePath' => $imagePath,
            'isCorrect' => $i === $form['correct']
        ];
    }

    if ($questionImage === '') {
        return ['errors' => ['حدث خطأ أثناء حفظ الصورة.']];
    }

    if ($isNew) {
        $questionID = insertQuestion(
            $destinationID,
            $form['quizTitle'],
            (int)$form['quizPoints'],
            $form['text'],
            $questionImage,
            $choices
        );
    } else {
        $questionID = (int)$question['questionID'];
        updateQuestion($questionID, $form['text'], $questionImage, $choices);
    }

    return ['errors' => [], 'questionID' => $questionID];
}

// Quiz points and badges

// Changes the points of a quiz. Returns an error message or ''.
function changeQuizPoints($quizID, $value) {

    if (findQuiz($quizID) === null) {
        return 'الاختبار غير موجود.';
    }

    if (!isPositiveWholeNumber($value)) {
        return 'نقاط الاختبار يجب أن تكون عددًا صحيحًا موجبًا.';
    }

    updateQuizPoints($quizID, (int)trim($value));
    return '';
}

// Changes the name and the description of a badge. Returns the errors.
function changeBadge($badgeID, $title, $description) {

    $title = trim($title);
    $description = trim($description);
    $errors = [];

    if (findBadge($badgeID) === null) {
        return ['الوسام غير موجود.'];
    }

    if ($title === '') {
        $errors[] = 'اكتب اسم الوسام.';
    } elseif (mb_strlen($title) > 50) {
        $errors[] = 'اسم الوسام يجب ألا يزيد عن 50 حرفًا.';
    } elseif (badgeTitleExists($title, $badgeID)) {
        $errors[] = 'يوجد وسام آخر بهذا الاسم.';
    }

    if ($description === '') {
        $errors[] = 'اكتب وصف الوسام.';
    } elseif (mb_strlen($description) > 255) {
        $errors[] = 'وصف الوسام يجب ألا يزيد عن 255 حرفًا.';
    }

    if (count($errors) === 0) {
        updateBadgeText($badgeID, $title, $description);
    }

    return $errors;
}
