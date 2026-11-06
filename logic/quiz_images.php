<?php
// Images of quiz questions and choices: JPG, PNG or WEBP, 5 MB at most

// Did the admin choose a file?
function quizImageWasChosen($file) {
    if (!is_array($file) || !isset($file['error'])) {
        return false;
    }
    return $file['error'] != UPLOAD_ERR_NO_FILE;
}

// Returns '' if the image is fine, or the error message
function checkQuizImage($file) {

    if ($file['error'] == UPLOAD_ERR_INI_SIZE || $file['error'] == UPLOAD_ERR_FORM_SIZE) {
        return 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.';
    }

    if ($file['error'] != UPLOAD_ERR_OK) {
        return 'حدث خطأ أثناء رفع الصورة.';
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.';
    }

    $info = getimagesize($file['tmp_name']);
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];

    if ($info == false || !in_array($info['mime'], $allowed)) {
        return 'يرجى اختيار صورة JPG أو PNG أو WEBP.';
    }

    return '';
}

// Save the image in assets/images/quiz and return its path ('' if it fails)
function storeQuizImage($file) {

    $info = getimagesize($file['tmp_name']);

    if ($info['mime'] == 'image/png') {
        $extension = 'png';
    } elseif ($info['mime'] == 'image/webp') {
        $extension = 'webp';
    } else {
        $extension = 'jpg';
    }

    $fileName = 'quiz_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $saved = move_uploaded_file($file['tmp_name'], __DIR__ . '/../assets/images/quiz/' . $fileName);

    if (!$saved) {
        return '';
    }

    return 'assets/images/quiz/' . $fileName;
}
