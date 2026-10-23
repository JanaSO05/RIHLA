<?php

// The journey manager. It asks the data layer for the destinations and
// never reads the file itself.
require_once __DIR__ . '/../data/destinations.php';

// Available journeys
function getJourneys() {
    return [
        'land' => 'رحلة البر',
        'sea' => 'رحلة البحر'
    ];
}

// All destinations, including the hidden ones.
// The administrator pages use this one.
function getAllDestinations() {
    return readDestinations();
}

// Save the destinations after the administrator changes them
function saveAllDestinations($destinations) {
    writeDestinations($destinations);
}

// Only the destinations that children are allowed to see.
// A hidden destination stays in the data but is not returned here.
function getVisibleDestinations() {

    return array_filter(
        getAllDestinations(),
        function ($destination) {
            return ($destination['status'] ?? 'published') === 'published';
        }
    );
}

// Get the visible destinations of one journey
function getDestinationsByJourney($journeyKey) {

    return array_filter(
        getVisibleDestinations(),
        function ($destination) use ($journeyKey) {
            return $destination['journey'] === $journeyKey;
        }
    );
}

// Get one visible destination by ID
function getDestination($id) {

    $destinations = getVisibleDestinations();

    return $destinations[$id] ?? null;
}

// Get the landmarks of a destination
function getLandmarks($destinationId) {

    $destination = getDestination($destinationId);

    return $destination['landmarks'] ?? [];
}
