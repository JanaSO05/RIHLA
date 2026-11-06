<?php

// SQL for journeys, destinations, landmarks and content sections

require_once __DIR__ . '/db.php';

// Reading

// Every destination (any status), each with its landmarks (any status)
function readDestinations() {

    $pdo = db();
    $destinations = [];

    $sql = 'SELECT d.destinationID, d.name, d.description, d.imagePath, d.status, j.type
              FROM destination d
              JOIN journey j ON d.journeyID = j.journeyID
             ORDER BY d.destinationID';

    foreach ($pdo->query($sql) as $row) {
        $destinations[(int)$row['destinationID']] = [
            'name'      => $row['name'],
            'desc'      => $row['description'],
            'imagePath' => $row['imagePath'],
            'journey'   => $row['type'],
            'status'    => $row['status'],
            'landmarks' => []
        ];
    }

    $sql = 'SELECT landmarkID, name, description, imagePath, status, destinationID
              FROM landmark
             ORDER BY landmarkID';

    foreach ($pdo->query($sql) as $row) {
        $id = (int)$row['destinationID'];
        if (isset($destinations[$id])) {
            $destinations[$id]['landmarks'][] = [
                'landmarkID' => (int)$row['landmarkID'],
                'name'       => $row['name'],
                'desc'       => $row['description'],
                'imagePath'  => $row['imagePath'],
                'status'     => $row['status']
            ];
        }
    }

    return $destinations;
}

// The journeyID of 'land' or 'sea'
function findJourneyID($type) {
    $statement = db()->prepare('SELECT journeyID FROM journey WHERE type = ?');
    $statement->execute([$type]);
    $id = $statement->fetchColumn();
    return $id === false ? null : (int)$id;
}

// One landmark (any status), or null
function findLandmarkByID($landmarkID) {
    $statement = db()->prepare('SELECT * FROM landmark WHERE landmarkID = ?');
    $statement->execute([$landmarkID]);
    $row = $statement->fetch();
    return $row === false ? null : $row;
}

// Is the destination name used by another destination? (name is UNIQUE)
function destinationNameExists($name, $exceptID) {
    $statement = db()->prepare('SELECT COUNT(*) FROM destination WHERE name = ? AND destinationID <> ?');
    $statement->execute([$name, $exceptID]);
    return (int)$statement->fetchColumn() > 0;
}

// Does the destination have a quiz with no questions? (checked before publishing)
function destinationHasEmptyQuiz($destinationID) {
    $statement = db()->prepare(
        'SELECT COUNT(*) FROM quiz q
          WHERE q.destinationID = ?
            AND NOT EXISTS (SELECT 1 FROM question qs WHERE qs.quizID = q.quizID)'
    );
    $statement->execute([$destinationID]);
    return (int)$statement->fetchColumn() > 0;
}

// Destinations (administrator)

// A new destination is saved as a draft
function insertDestination($name, $description, $imagePath, $journeyID, $adminID) {
    $statement = db()->prepare(
        "INSERT INTO destination (name, description, imagePath, status, journeyID, adminID)
         VALUES (?, ?, ?, 'draft', ?, ?)"
    );
    $statement->execute([$name, $description, $imagePath, $journeyID, $adminID]);
}

function updateDestination($destinationID, $name, $description, $imagePath, $journeyID) {
    $statement = db()->prepare(
        'UPDATE destination SET name = ?, description = ?, imagePath = ?, journeyID = ?
          WHERE destinationID = ?'
    );
    $statement->execute([$name, $description, $imagePath, $journeyID, $destinationID]);
}

// Hide a destination (only a published one can be hidden)
function hideDestinationRow($destinationID) {
    $statement = db()->prepare("UPDATE destination SET status = 'hidden' WHERE destinationID = ? AND status = 'published'");
    $statement->execute([$destinationID]);
}

function hideLandmarkRow($landmarkID) {
    $statement = db()->prepare("UPDATE landmark SET status = 'hidden' WHERE landmarkID = ? AND status = 'published'");
    $statement->execute([$landmarkID]);
}

function updateDestinationStatus($destinationID, $status) {
    $statement = db()->prepare('UPDATE destination SET status = ? WHERE destinationID = ?');
    $statement->execute([$status, $destinationID]);
}

function updateDestinationImage($destinationID, $imagePath) {
    $statement = db()->prepare('UPDATE destination SET imagePath = ? WHERE destinationID = ?');
    $statement->execute([$imagePath, $destinationID]);
}

// Landmarks (administrator)

// A new landmark is saved as a draft
function insertLandmark($destinationID, $name, $description, $imagePath) {
    $statement = db()->prepare(
        "INSERT INTO landmark (name, description, imagePath, status, destinationID)
         VALUES (?, ?, ?, 'draft', ?)"
    );
    $statement->execute([$name, $description, $imagePath, $destinationID]);
}

function updateLandmark($landmarkID, $name, $description, $imagePath, $destinationID) {
    $statement = db()->prepare(
        'UPDATE landmark SET name = ?, description = ?, imagePath = ?, destinationID = ?
          WHERE landmarkID = ?'
    );
    $statement->execute([$name, $description, $imagePath, $destinationID, $landmarkID]);
}

function updateLandmarkStatus($landmarkID, $status) {
    $statement = db()->prepare('UPDATE landmark SET status = ? WHERE landmarkID = ?');
    $statement->execute([$status, $landmarkID]);
}

function updateLandmarkImage($landmarkID, $imagePath) {
    $statement = db()->prepare('UPDATE landmark SET imagePath = ? WHERE landmarkID = ?');
    $statement->execute([$imagePath, $landmarkID]);
}

// Cultural and historical content (administrator)

// Every content section with the names of its landmark and destination
function findAllContentSections() {
    $sql = 'SELECT cs.*, l.name AS landmarkName, d.name AS destinationName
              FROM contentsection cs
              JOIN landmark l ON l.landmarkID = cs.landmarkID
              JOIN destination d ON d.destinationID = l.destinationID
             ORDER BY d.destinationID, l.landmarkID, cs.sectionID';
    return db()->query($sql)->fetchAll();
}

function findContentSection($sectionID) {
    $statement = db()->prepare('SELECT * FROM contentsection WHERE sectionID = ?');
    $statement->execute([$sectionID]);
    $row = $statement->fetch();
    return $row === false ? null : $row;
}

function insertContentSection($landmarkID, $title, $text, $imagePath, $source, $adminID) {
    $statement = db()->prepare(
        'INSERT INTO contentsection (title, text, imagePath, source, landmarkID, adminID)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([$title, $text, $imagePath, $source, $landmarkID, $adminID]);
}

function updateContentSection($sectionID, $landmarkID, $title, $text, $imagePath, $source) {
    $statement = db()->prepare(
        'UPDATE contentsection SET title = ?, text = ?, imagePath = ?, source = ?, landmarkID = ?
          WHERE sectionID = ?'
    );
    $statement->execute([$title, $text, $imagePath, $source, $landmarkID, $sectionID]);
}

// Content sections of the published landmarks (used by the AI guide)
function findGuideSections($destinationID) {
    $statement = db()->prepare(
        "SELECT cs.title, cs.text
           FROM contentsection cs
           JOIN landmark l ON cs.landmarkID = l.landmarkID
           JOIN destination d ON l.destinationID = d.destinationID
          WHERE d.destinationID = ?
            AND d.status = 'published' AND l.status = 'published'
          ORDER BY cs.sectionID"
    );
    $statement->execute([$destinationID]);
    return $statement->fetchAll();
}
