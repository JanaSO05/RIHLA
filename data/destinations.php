<?php

// Data access layer.
// The destinations are stored in data/destinations.json so that every visitor
// sees the same content. In the next sprint this file is replaced by the
// MySQL database, and only the functions below have to change.

define('RIHLA_DATA_FILE', __DIR__ . '/destinations.json');

// The content RIHLA starts with, used the first time the file is created
function defaultDestinations() {

    return [

        1 => [
            'name'      => 'الدرعية',
            'desc'      => 'الدرعية موقع تاريخي مهم في المملكة العربية السعودية.',
            'journey'   => 'land',
            'status'    => 'published',
            'landmarks' => [
                [
                    'name' => 'حي الطريف',
                    'desc' => 'حي تاريخي يقع في الدرعية.'
                ]
            ]
        ],

        2 => [
            'name'      => 'العلا',
            'desc'      => 'العلا منطقة تتميز بتاريخها ومعالمها الأثرية.',
            'journey'   => 'land',
            'status'    => 'published',
            'landmarks' => [
                [
                    'name' => 'الحجر',
                    'desc' => 'موقع أثري مشهور في العلا.'
                ]
            ]
        ],

        3 => [
            'name'      => 'البحر الأحمر',
            'desc'      => 'البحر الأحمر يتميز ببيئته البحرية المتنوعة.',
            'journey'   => 'sea',
            'status'    => 'published',
            'landmarks' => [
                [
                    'name' => 'الشعاب المرجانية',
                    'desc' => 'تعيش في الشعاب المرجانية كائنات بحرية متنوعة.'
                ]
            ]
        ],

        4 => [
            'name'      => 'جدة التاريخية',
            'desc'      => 'جدة التاريخية منطقة تراثية تتميز بمبانيها القديمة.',
            'journey'   => 'land',
            'status'    => 'published',
            'landmarks' => [
                [
                    'name' => 'بيت نصيف',
                    'desc' => 'أحد المباني التاريخية في جدة.'
                ]
            ]
        ]
    ];
}

// Read every destination from the file
function readDestinations() {

    if (!file_exists(RIHLA_DATA_FILE)) {
        writeDestinations(defaultDestinations());
        return defaultDestinations();
    }

    $json = file_get_contents(RIHLA_DATA_FILE);
    $data = json_decode($json, true);

    if (!is_array($data)) {
        return defaultDestinations();
    }

    // json_decode turns the numeric keys into strings, so we put them back
    $destinations = [];

    foreach ($data as $id => $destination) {
        $destinations[(int)$id] = $destination;
    }

    return $destinations;
}

// Save every destination back to the file
function writeDestinations($destinations) {

    $json = json_encode(
        $destinations,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    file_put_contents(RIHLA_DATA_FILE, $json, LOCK_EX);
}