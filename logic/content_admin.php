<?php

// Admin: destinations, landmarks, content and images

require_once __DIR__ . '/journeys.php';
require_once __DIR__ . '/images.php';

// Destinations

// Add a new destination (id 0) or save the edit. Returns the errors
function saveDestinationForm($destinationID, $post, $files, $adminID) {

    $name        = trim($post['name'] ?? '');
    $description = trim($post['description'] ?? '');
    $journey     = $post['journey'] ?? '';
    $image       = $files['image'] ?? null;
    $errors      = [];

    $all = getAllDestinations();
    $old = $all[$destinationID] ?? null;

    if ($name === '') {
        $errors[] = 'اكتب اسم الوجهة.';
    } elseif (mb_strlen($name) > 50) {
        $errors[] = 'اسم الوجهة يجب ألا يزيد عن 50 حرفًا.';
    } elseif (destinationNameExists($name, $destinationID)) {
        $errors[] = 'هذه الوجهة موجودة بالفعل.';
    }

    if ($description === '') {
        $errors[] = 'اكتب وصفًا قصيرًا للوجهة.';
    }

    $journeyID = findJourneyID($journey);
    if ($journeyID === null) {
        $errors[] = 'اختر نوع الرحلة (بر أو بحر).';
    }

    if (imageWasChosen($image)) {
        $problem = checkImage($image);
        if ($problem !== '') {
            $errors[] = $problem;
        }
    } elseif ($old === null) {
        $errors[] = 'ارفع صورة للوجهة.';
    }

    if (count($errors) > 0) {
        return $errors;
    }

    $imagePath = imageWasChosen($image) ? saveImage($image) : $old['imagePath'];

    if ($old === null) {
        insertDestination($name, $description, $imagePath, $journeyID, $adminID);
    } else {
        updateDestination($destinationID, $name, $description, $imagePath, $journeyID);
    }

    return [];
}

// Publish a destination if it has an image and its quiz is not empty
function publishDestination($destinationID) {

    $all = getAllDestinations();

    if (!isset($all[$destinationID])) {
        return 'الوجهة غير موجودة.';
    }

    if (!imageExists($all[$destinationID]['imagePath'])) {
        return 'لا يمكن النشر: ارفع صورة صحيحة للوجهة أولًا.';
    }

    if (destinationHasEmptyQuiz($destinationID)) {
        return 'لا يمكن النشر: أضف سؤالًا واحدًا على الأقل لاختبار الوجهة أولًا.';
    }

    updateDestinationStatus($destinationID, 'published');
    return '';
}

function hideDestination($destinationID) {
    hideDestinationRow($destinationID);
}

// Landmarks

// Adds a landmark ($landmarkID = 0) or saves an edited one. Returns the errors.
function saveLandmarkForm($landmarkID, $post, $files) {

    $name          = trim($post['name'] ?? '');
    $description   = trim($post['description'] ?? '');
    $destinationID = (int)($post['destination'] ?? 0);
    $image         = $files['image'] ?? null;
    $errors        = [];

    $old = $landmarkID > 0 ? findLandmarkByID($landmarkID) : null;
    $all = getAllDestinations();

    if (!isset($all[$destinationID])) {
        $errors[] = 'اختر الوجهة التي يتبع لها المعلم.';
    }

    if ($name === '') {
        $errors[] = 'اكتب اسم المعلم.';
    } elseif (mb_strlen($name) > 80) {
        $errors[] = 'اسم المعلم يجب ألا يزيد عن 80 حرفًا.';
    }

    if ($description === '') {
        $errors[] = 'اكتب وصفًا قصيرًا للمعلم.';
    }

    if (imageWasChosen($image)) {
        $problem = checkImage($image);
        if ($problem !== '') {
            $errors[] = $problem;
        }
    } elseif ($old === null) {
        $errors[] = 'ارفع صورة للمعلم.';
    }

    if (count($errors) > 0) {
        return $errors;
    }

    $imagePath = imageWasChosen($image) ? saveImage($image) : $old['imagePath'];

    if ($old === null) {
        insertLandmark($destinationID, $name, $description, $imagePath);
    } else {
        updateLandmark($landmarkID, $name, $description, $imagePath, $destinationID);
    }

    return [];
}

// Publish a landmark (we only check the image)
function publishLandmark($landmarkID) {

    $landmark = findLandmarkByID($landmarkID);

    if ($landmark === null) {
        return 'المعلم غير موجود.';
    }

    if (!imageExists($landmark['imagePath'])) {
        return 'لا يمكن النشر: ارفع صورة صحيحة للمعلم أولًا.';
    }

    updateLandmarkStatus($landmarkID, 'published');
    return '';
}

function hideLandmark($landmarkID) {
    hideLandmarkRow($landmarkID);
}

// Cultural and historical content

// Add a content section (id 0) or save the edit. Returns the errors
function saveContentForm($sectionID, $post, $files, $adminID) {

    $landmarkID = (int)($post['landmark'] ?? 0);
    $title      = trim($post['title'] ?? '');
    $text       = trim($post['text'] ?? '');
    $source     = trim($post['source'] ?? '');
    $image      = $files['image'] ?? null;
    $errors     = [];

    $old = $sectionID > 0 ? findContentSection($sectionID) : null;

    if (findLandmarkByID($landmarkID) === null) {
        $errors[] = 'اختر المعلم الذي يتبع له المحتوى.';
    }

    if ($title === '') {
        $errors[] = 'اكتب عنوان المحتوى.';
    } elseif (mb_strlen($title) > 100) {
        $errors[] = 'العنوان يجب ألا يزيد عن 100 حرف.';
    }

    // NFR: each content section is 80 words at most
    $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

    if ($text === '') {
        $errors[] = 'اكتب نص المحتوى.';
    } elseif (count($words) > 80) {
        $errors[] = 'النص فيه ' . count($words) . ' كلمة، والحد الأعلى 80 كلمة.';
    }

    if ($source === '') {
        $errors[] = 'اكتب المصدر الرسمي للمعلومة.';
    }

    if (imageWasChosen($image)) {
        $problem = checkImage($image);
        if ($problem !== '') {
            $errors[] = $problem;
        }
    }

    if (count($errors) > 0) {
        return $errors;
    }

    $imagePath = $old !== null ? $old['imagePath'] : null;

    if (imageWasChosen($image)) {
        $imagePath = saveImage($image);
    }

    if ($old === null) {
        insertContentSection($landmarkID, $title, $text, $imagePath, $source, $adminID);
    } else {
        updateContentSection($sectionID, $landmarkID, $title, $text, $imagePath, $source);
    }

    return [];
}

// Uploading images

// Attach an image to a destination or landmark ($target = 'destination-1' or 'landmark-3')
function attachImage($target, $file) {

    $parts = explode('-', $target);
    $type  = $parts[0] ?? '';
    $id    = (int)($parts[1] ?? 0);

    $all = getAllDestinations();

    if ($type === 'destination' && !isset($all[$id])) {
        return ['اختر الوجهة أو المعلم.'];
    }

    if ($type === 'landmark' && findLandmarkByID($id) === null) {
        return ['اختر الوجهة أو المعلم.'];
    }

    if ($type !== 'destination' && $type !== 'landmark') {
        return ['اختر الوجهة أو المعلم.'];
    }

    if (!imageWasChosen($file)) {
        return ['اختر صورة من جهازك.'];
    }

    $problem = checkImage($file);

    if ($problem !== '') {
        return [$problem];
    }

    $imagePath = saveImage($file);

    if ($type === 'destination') {
        updateDestinationImage($id, $imagePath);
    } else {
        updateLandmarkImage($id, $imagePath);
    }

    return [];
}
