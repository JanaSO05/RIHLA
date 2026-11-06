<?php

// Journeys and the destinations children can see
require_once __DIR__ . '/../data/destinations.php';

// Available journeys
function getJourneys() {
    return [
        'land' => 'رحلة البر',
        'sea'  => 'رحلة البحر'
    ];
}

// All destinations, also draft and hidden (for the admin pages)
function getAllDestinations() {
    return readDestinations();
}

// Only published destinations with their published landmarks
function getVisibleDestinations() {

    $visible = [];

    foreach (getAllDestinations() as $id => $destination) {

        if ($destination['status'] !== 'published') {
            continue;
        }

        $landmarks = [];
        foreach ($destination['landmarks'] as $landmark) {
            if ($landmark['status'] === 'published') {
                $landmarks[] = $landmark;
            }
        }

        $destination['landmarks'] = $landmarks;
        $visible[$id] = $destination;
    }

    return $visible;
}

// The visible destinations of one journey
function getDestinationsByJourney($journeyKey) {

    $list = [];

    foreach (getVisibleDestinations() as $id => $destination) {
        if ($destination['journey'] === $journeyKey) {
            $list[$id] = $destination;
        }
    }

    return $list;
}

// One visible destination by ID, or null
function getDestination($id) {

    $destinations = getVisibleDestinations();

    return $destinations[$id] ?? null;
}
