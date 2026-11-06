<?php

// SQL for users, explorer profiles, the notebook and deleting items

require_once __DIR__ . '/db.php';

// Users and explorer profiles

// A user with the role it can log in as, or null
function findUserForLogin($username) {

    $statement = db()->prepare(
        'SELECT u.userID, u.username, u.password,
                (SELECT COUNT(*) FROM child c WHERE c.userID = u.userID) AS isChild,
                (SELECT COUNT(*) FROM administrator a WHERE a.userID = u.userID) AS isAdmin
           FROM user u
          WHERE u.username = ?'
    );
    $statement->execute([$username]);

    $row = $statement->fetch();

    return $row === false ? null : $row;
}

// Is a username already taken?
function usernameExists($username) {

    $statement = db()->prepare('SELECT COUNT(*) FROM user WHERE username = ?');
    $statement->execute([$username]);

    return (int)$statement->fetchColumn() > 0;
}

// Save the user, the child and the notebook together. Returns the new userID
function insertExplorer($username, $passwordHash, $displayName, $avatar) {

    $pdo = db();
    $pdo->beginTransaction();

    try {

        $statement = $pdo->prepare('INSERT INTO user (username, password) VALUES (?, ?)');
        $statement->execute([$username, $passwordHash]);
        $userID = (int)$pdo->lastInsertId();

        $statement = $pdo->prepare(
            'INSERT INTO child (userID, displayName, avatar, totalPoints) VALUES (?, ?, ?, 0)'
        );
        $statement->execute([$userID, $displayName, $avatar]);

        $statement = $pdo->prepare('INSERT INTO explorernotebook (childID) VALUES (?)');
        $statement->execute([$userID]);

        $pdo->commit();
        return $userID;

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// The explorer profile of a child, or null
function findExplorer($childID) {

    $statement = db()->prepare(
        'SELECT c.userID, u.username, c.displayName, c.avatar, c.totalPoints
           FROM child c
           JOIN user u ON u.userID = c.userID
          WHERE c.userID = ?'
    );
    $statement->execute([$childID]);

    $row = $statement->fetch();

    return $row === false ? null : $row;
}

// Changes the display name and the avatar of a child
function updateExplorer($childID, $displayName, $avatar) {

    $statement = db()->prepare(
        'UPDATE child SET displayName = ?, avatar = ? WHERE userID = ?'
    );
    $statement->execute([$displayName, $avatar, $childID]);
}

// Landmarks and discoveries

// Landmarks children can see: the landmark and its destination are published
function findVisibleLandmarks($destinationID) {

    $statement = db()->prepare(
        'SELECT l.landmarkID, l.name, l.description, l.imagePath
           FROM landmark l
           JOIN destination d ON d.destinationID = l.destinationID
          WHERE l.destinationID = ?
            AND l.status = \'published\' AND d.status = \'published\'
          ORDER BY l.landmarkID'
    );
    $statement->execute([$destinationID]);

    return $statement->fetchAll();
}

// One landmark children can see, with its destination, or null
function findVisibleLandmark($landmarkID) {

    $statement = db()->prepare(
        'SELECT l.landmarkID, l.name, l.description, l.imagePath,
                d.destinationID, d.name AS destinationName
           FROM landmark l
           JOIN destination d ON d.destinationID = l.destinationID
          WHERE l.landmarkID = ?
            AND l.status = \'published\' AND d.status = \'published\''
    );
    $statement->execute([$landmarkID]);

    $row = $statement->fetch();

    return $row === false ? null : $row;
}

// The content sections of a landmark
function findContentSections($landmarkID) {

    $statement = db()->prepare(
        'SELECT sectionID, title, text, imagePath, source
           FROM contentsection
          WHERE landmarkID = ?
          ORDER BY sectionID'
    );
    $statement->execute([$landmarkID]);

    return $statement->fetchAll();
}

// Save the discovery the first time only. Returns true if it is new
function insertDiscovery($childID, $landmarkID) {

    $pdo = db();

    // the child's notebook
    $statement = $pdo->prepare('SELECT notebookID FROM explorernotebook WHERE childID = ?');
    $statement->execute([$childID]);
    $notebookID = $statement->fetchColumn();

    if ($notebookID === false) {
        return false;
    }

    // was this landmark saved before?
    $statement = $pdo->prepare('SELECT COUNT(*) FROM discovery WHERE notebookID = ? AND landmarkID = ?');
    $statement->execute([$notebookID, $landmarkID]);

    if ($statement->fetchColumn() > 0) {
        return false;
    }

    $statement = $pdo->prepare('INSERT INTO discovery (notebookID, landmarkID, savedDate) VALUES (?, ?, NOW())');
    $statement->execute([$notebookID, $landmarkID]);

    return true;
}

// The discoveries in a child's notebook, newest first
function findDiscoveries($childID) {

    $statement = db()->prepare(
        'SELECT l.landmarkID, l.name, l.imagePath, d.name AS destinationName, ds.savedDate
           FROM discovery ds
           JOIN explorernotebook n ON n.notebookID = ds.notebookID
           JOIN landmark l ON l.landmarkID = ds.landmarkID
           JOIN destination d ON d.destinationID = l.destinationID
          WHERE n.childID = ?
          ORDER BY ds.savedDate DESC, ds.discoveryID DESC'
    );
    $statement->execute([$childID]);

    return $statement->fetchAll();
}

// The names of the published destinations and whether the child completed each
function findDestinationProgress($childID) {

    $statement = db()->prepare(
        'SELECT d.destinationID, d.name,
                (SELECT COUNT(*) FROM quizattempt qa
                   JOIN quiz q ON q.quizID = qa.quizID
                  WHERE q.destinationID = d.destinationID AND qa.childID = ?) AS completed
           FROM destination d
          WHERE d.status = \'published\'
          ORDER BY d.destinationID'
    );
    $statement->execute([$childID]);

    return $statement->fetchAll();
}

// Deleting items (administrator)

// Every item the administrator can delete, grouped by type
function findDeletableItems() {

    $pdo = db();

    return [
        'destination' => $pdo->query(
            'SELECT destinationID AS id, name AS label FROM destination ORDER BY destinationID'
        )->fetchAll(),

        'landmark' => $pdo->query(
            'SELECT l.landmarkID AS id, CONCAT(l.name, \' - \', d.name) AS label
               FROM landmark l JOIN destination d ON d.destinationID = l.destinationID
              ORDER BY l.landmarkID'
        )->fetchAll(),

        'content' => $pdo->query(
            'SELECT cs.sectionID AS id, CONCAT(cs.title, \' - \', l.name) AS label
               FROM contentsection cs JOIN landmark l ON l.landmarkID = cs.landmarkID
              ORDER BY cs.sectionID'
        )->fetchAll(),

        'question' => $pdo->query(
            'SELECT qs.questionID AS id, CONCAT(qs.text, \' - \', d.name) AS label
               FROM question qs
               JOIN quiz q ON q.quizID = qs.quizID
               JOIN destination d ON d.destinationID = q.destinationID
              ORDER BY qs.questionID'
        )->fetchAll(),

        'badge' => $pdo->query(
            'SELECT badgeID AS id, title AS label FROM badge ORDER BY requiredPoints'
        )->fetchAll()
    ];
}

// One item by its type and ID, with a label to show, or null
function findDeletableItem($type, $id) {

    foreach (findDeletableItems()[$type] ?? [] as $item) {
        if ((int)$item['id'] === $id) {
            return $item;
        }
    }

    return null;
}

// What else is deleted with an item (shown in the confirmation message)
function countDependents($type, $id) {

    $pdo = db();
    $counts = [];

    if ($type == 'destination') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM landmark WHERE destinationID = ?');
        $statement->execute([$id]);
        $counts['landmarks'] = $statement->fetchColumn();

        $statement = $pdo->prepare('SELECT COUNT(*) FROM contentsection cs JOIN landmark l ON l.landmarkID = cs.landmarkID WHERE l.destinationID = ?');
        $statement->execute([$id]);
        $counts['sections'] = $statement->fetchColumn();

        $statement = $pdo->prepare('SELECT COUNT(*) FROM question qs JOIN quiz q ON q.quizID = qs.quizID WHERE q.destinationID = ?');
        $statement->execute([$id]);
        $counts['questions'] = $statement->fetchColumn();
    }

    if ($type == 'landmark') {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM contentsection WHERE landmarkID = ?');
        $statement->execute([$id]);
        $counts['sections'] = $statement->fetchColumn();
    }

    return $counts;
}

// Delete an item. ON DELETE CASCADE deletes what belongs to it
function deleteItem($type, $id) {

    if ($type == 'destination') {
        $sql = 'DELETE FROM destination WHERE destinationID = ?';
    } elseif ($type == 'landmark') {
        $sql = 'DELETE FROM landmark WHERE landmarkID = ?';
    } elseif ($type == 'content') {
        $sql = 'DELETE FROM contentsection WHERE sectionID = ?';
    } elseif ($type == 'question') {
        $sql = 'DELETE FROM question WHERE questionID = ?';
    } elseif ($type == 'badge') {
        $sql = 'DELETE FROM badge WHERE badgeID = ?';
    } else {
        return;
    }

    $statement = db()->prepare($sql);
    $statement->execute([$id]);
}
