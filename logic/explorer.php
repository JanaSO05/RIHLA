<?php

// Login, explorer profile, discoveries and the notebook

require_once __DIR__ . '/../data/explorers.php';
require_once __DIR__ . '/quiz.php';   // currentChildID() and getChildRewards()

// The avatars a child can pick from (the file names stored in Child.avatar)
function getAvatars() {
    return [
        'avatar1.png' => 'المستكشف الأخضر',
        'avatar2.png' => 'المستكشف الأزرق',
        'avatar3.png' => 'المستكشف البرتقالي',
        'avatar4.png' => 'المستكشف الأصفر',
        'avatar5.png' => 'المستكشف البنفسجي',
        'avatar6.png' => 'المستكشف الأحمر'
    ];
}

function avatarPath($avatar) {
    $avatars = getAvatars();

    // if the avatar is not in the list we show the first one
    if (!isset($avatars[$avatar])) {
        $avatar = 'avatar1.png';
    }

    return 'assets/images/avatars/' . $avatar;
}

// Logging in

// Check the username and password in the database
function logIn($role, $username, $password) {

    if ($username === '' || $password === '') {
        return ['error' => 'يرجى إدخال اسم المستخدم وكلمة المرور.'];
    }

    $user = findUserForLogin($username);

    if ($user === null || !password_verify($password, $user['password'])) {
        return ['error' => 'اسم المستخدم أو كلمة المرور غير صحيحة.'];
    }

    if ($role === 'child' && $user['isChild']) {
        $sessionRole = 'child';
        $view = 'child';
        $page = 'index.php';
    } elseif ($role === 'admin' && $user['isAdmin']) {
        $sessionRole = 'admin';
        $view = 'admin';
        $page = 'admin_dashboard.php';
    } elseif ($role === 'child' && $user['isAdmin']) {
        // The administrator may look at the child interface
        $sessionRole = 'admin';
        $view = 'child';
        $page = 'index.php';
    } else {
        return ['error' => 'اسم المستخدم أو كلمة المرور غير صحيحة.'];
    }

    session_regenerate_id(true);

    $_SESSION['userID']   = (int)$user['userID'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $sessionRole;
    $_SESSION['view']     = $view;

    return ['error' => '', 'page' => $page];
}

// Creating and editing the explorer profile

// Checks the display name and the avatar. Returns the errors.
function checkProfileFields($displayName, $avatar) {

    $errors = [];

    if ($displayName === '') {
        $errors[] = 'اكتب اسم المستكشف.';
    } elseif (mb_strlen($displayName) > 30) {
        $errors[] = 'اسم المستكشف يجب ألا يزيد عن 30 حرفًا.';
    }

    if (!array_key_exists($avatar, getAvatars())) {
        $errors[] = 'اختر صورة رمزية.';
    }

    return $errors;
}

// Create a profile (no real name, age, email or photo)
function createExplorer($username, $password, $displayName, $avatar) {

    $username = trim($username);
    $displayName = trim($displayName);
    $errors = [];

    if ($username === '') {
        $errors[] = 'اكتب اسم المستخدم.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $errors[] = 'اسم المستخدم من 3 إلى 30 حرفًا إنجليزيًا أو رقمًا بدون مسافات.';
    } elseif (usernameExists($username)) {
        $errors[] = 'اسم المستخدم مستخدم من قبل، اختر اسمًا آخر.';
    }

    if ($password === '') {
        $errors[] = 'اكتب كلمة المرور.';
    } elseif (mb_strlen($password) < 4) {
        $errors[] = 'كلمة المرور يجب أن تكون 4 أحرف أو أكثر.';
    }

    $errors = array_merge($errors, checkProfileFields($displayName, $avatar));

    if (count($errors) > 0) {
        return ['errors' => $errors];
    }

    $userID = insertExplorer($username, password_hash($password, PASSWORD_DEFAULT), $displayName, $avatar);

    // The new explorer is logged in straight away
    session_regenerate_id(true);
    $_SESSION['userID']   = $userID;
    $_SESSION['username'] = $username;
    $_SESSION['role']     = 'child';
    $_SESSION['view']     = 'child';

    return ['errors' => []];
}

// Saves a new display name and avatar. Returns the errors.
function editExplorer($childID, $displayName, $avatar) {

    $displayName = trim($displayName);
    $errors = checkProfileFields($displayName, $avatar);

    if (count($errors) === 0) {
        updateExplorer($childID, $displayName, $avatar);
    }

    return $errors;
}

// Landmarks, discoveries, and the Explorer Notebook

function getVisibleLandmarks($destinationID) {
    return findVisibleLandmarks($destinationID);
}

// Open a landmark and save it as a discovery for the child
function openLandmark($landmarkID) {

    $landmark = findVisibleLandmark($landmarkID);

    if ($landmark === null) {
        return null;
    }

    $childID = currentChildID();
    $landmark['newDiscovery'] = $childID !== null && insertDiscovery($childID, $landmarkID);
    $landmark['sections'] = findContentSections($landmarkID);

    return $landmark;
}

// Everything the Explorer Notebook shows
function getNotebook($childID) {

    $destinations = findDestinationProgress($childID);
    // count the completed destinations
    $completed = 0;
    foreach ($destinations as $d) {
        if ($d['completed'] > 0) {
            $completed++;
        }
    }
    $rewards = getChildRewards($childID);

    return [
        'discoveries'  => findDiscoveries($childID),
        'destinations' => $destinations,
        'completed'    => $completed,
        'available'    => count($destinations),
        'totalPoints'  => $rewards['totalPoints'],
        'badges'       => $rewards['badges']
    ];
}
