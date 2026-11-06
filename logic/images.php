<?php

// Check and save images (JPG, PNG or WEBP, 5 MB at most)

// Was a file chosen in this file input?
function imageWasChosen($file) {
    return isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE;
}

// Returns '' if the image is fine, or the error message
function checkImage($file) {

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.';
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'حدث خطأ أثناء رفع الصورة.';
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.';
    }

    $info = getimagesize($file['tmp_name']);

    if ($info === false || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'])) {
        return 'الصيغ المقبولة: JPG أو PNG أو WEBP فقط.';
    }

    return '';
}

// Saves a checked image in assets/images and returns its path
function saveImage($file) {

    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = getimagesize($file['tmp_name'])['mime'];

    $fileName = 'img_' . time() . '_' . rand(1000, 9999) . '.' . $extensions[$mime];
    move_uploaded_file($file['tmp_name'], __DIR__ . '/../assets/images/' . $fileName);

    return 'assets/images/' . $fileName;
}

// Does the image of an item exist on the server?
function imageExists($imagePath) {
    return $imagePath !== '' && $imagePath !== null && file_exists(__DIR__ . '/../' . $imagePath);
}
