<?php

// Available journeys
function getJourneys() {
    return [
        'land' => 'رحلة البر',
        'sea' => 'رحلة البحر'
    ];
}

// Destinations and landmarks
function getAllDestinations() {
    return [
        1 => [
            'name' => 'الدرعية',
            'desc' => 'الدرعية موقع تاريخي مهم في المملكة العربية السعودية.',
            'journey' => 'land',
            'landmarks' => [
                [
                    'name' => 'حي الطريف',
                    'desc' => 'حي تاريخي يقع في الدرعية.'
                ]
            ]
        ],

        2 => [
            'name' => 'العلا',
            'desc' => 'العلا منطقة تتميز بتاريخها ومعالمها الأثرية.',
            'journey' => 'land',
            'landmarks' => [
                [
                    'name' => 'الحجر',
                    'desc' => 'موقع أثري مشهور في العلا.'
                ]
            ]
        ],

        3 => [
            'name' => 'البحر الأحمر',
            'desc' => 'البحر الأحمر يتميز ببيئته البحرية المتنوعة.',
            'journey' => 'sea',
            'landmarks' => [
                [
                    'name' => 'الشعاب المرجانية',
                    'desc' => 'تعيش في الشعاب المرجانية كائنات بحرية متنوعة.'
                ]
            ]
        ],

        4 => [
            'name' => 'جدة التاريخية',
            'desc' => 'جدة التاريخية منطقة تراثية تتميز بمبانيها القديمة.',
            'journey' => 'sea',
            'landmarks' => [
                [
                    'name' => 'بيت نصيف',
                    'desc' => 'أحد المباني التاريخية في جدة.'
                ]
            ]
        ]
    ];
}

// Get destinations belonging to a journey
function getDestinationsByJourney($journeyKey) {
    return array_filter(
        getAllDestinations(),
        function($destination) use ($journeyKey) {
            return $destination['journey'] === $journeyKey;
        }
    );
}

// Get one destination by ID
function getDestination($id) {
    $destinations = getAllDestinations();

    return $destinations[$id] ?? null;
}

// Get landmarks for a destination
function getLandmarks($destinationId) {
    $destination = getDestination($destinationId);

    return $destination['landmarks'] ?? [];
}