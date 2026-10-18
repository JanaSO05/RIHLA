<?php

require_once __DIR__ . '/journeys.php';

// Normalize Arabic text
function normalizeArabic($text) {

    $text = trim($text);

    // Remove Arabic diacritics
    $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $text);

    // Normalize different forms of Alef
    $text = str_replace(['أ', 'إ', 'آ'], 'ا', $text);

    // Treat ة and ه as equivalent
    $text = str_replace('ة', 'ه', $text);

    return $text;
}

// Search destinations and landmarks
function searchByName($term) {

    $results = [];

    $term = normalizeArabic($term);

    if ($term === '') {
        return $results;
    }

    $destinations = getAllDestinations();

    foreach ($destinations as $id => $destination) {

        $destinationName = normalizeArabic($destination['name']);

        // Search destination names
        if (mb_stripos($destinationName, $term) !== false) {

            $results[] = [
                'name' => $destination['name'],
                'type' => 'وجهة',
                'link' => 'destination.php?id=' . $id
            ];
        }

        // Search landmark names
        foreach ($destination['landmarks'] as $landmark) {

            $landmarkName = normalizeArabic($landmark['name']);

            if (mb_stripos($landmarkName, $term) !== false) {

                $results[] = [
                    'name' => $landmark['name'],
                    'type' => 'معلم',
                    'link' => 'destination.php?id=' . $id
                ];
            }
        }
    }

    return $results;
}