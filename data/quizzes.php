<?php

// SQL for quizzes, questions, choices, points and badges

require_once __DIR__ . '/db.php';

// Children

// The display name and total points of a child
function findChild($childID) {

    $statement = db()->prepare(
        'SELECT userID, displayName, avatar, totalPoints FROM child WHERE userID = ?'
    );
    $statement->execute([$childID]);

    $row = $statement->fetch();

    return $row === false ? null : $row;
}

// Destinations

// One destination with its status, or null
function findDestination($destinationID) {

    $statement = db()->prepare(
        'SELECT destinationID, name, status FROM destination WHERE destinationID = ?'
    );
    $statement->execute([$destinationID]);

    $row = $statement->fetch();

    return $row === false ? null : $row;
}

// Every destination with its quiz, if it has one (for the admin pages)
function findDestinationsWithQuiz() {

    $sql = 'SELECT d.destinationID, d.name, d.status,
                   q.quizID, q.title, q.totalPoints,
                   (SELECT COUNT(*) FROM question qs WHERE qs.quizID = q.quizID) AS questionCount
              FROM destination d
              LEFT JOIN quiz q ON q.destinationID = d.destinationID
             ORDER BY d.destinationID';

    return db()->query($sql)->fetchAll();
}

// Quizzes, questions, and choices

// The quiz of a destination, or null (a destination has at most one quiz)
function findQuizByDestination($destinationID) {

    $statement = db()->prepare(
        'SELECT quizID, title, totalPoints, destinationID FROM quiz WHERE destinationID = ?'
    );
    $statement->execute([$destinationID]);

    $row = $statement->fetch();

    return $row === false ? null : $row;
}

// One quiz by its ID, or null
function findQuiz($quizID) {

    $statement = db()->prepare(
        'SELECT quizID, title, totalPoints, destinationID FROM quiz WHERE quizID = ?'
    );
    $statement->execute([$quizID]);

    $row = $statement->fetch();

    return $row === false ? null : $row;
}

// The questions of a quiz in the order they were added
function findQuestions($quizID) {

    $statement = db()->prepare(
        'SELECT questionID, text, imagePath, quizID
           FROM question
          WHERE quizID = ?
          ORDER BY questionID'
    );
    $statement->execute([$quizID]);

    return $statement->fetchAll();
}

// One question, or null
function findQuestion($questionID) {

    $statement = db()->prepare(
        'SELECT questionID, text, imagePath, quizID FROM question WHERE questionID = ?'
    );
    $statement->execute([$questionID]);

    $row = $statement->fetch();

    return $row === false ? null : $row;
}

// The answer choices of a question in the order they were added
function findChoices($questionID) {

    $statement = db()->prepare(
        'SELECT choiceID, text, imagePath, isCorrect, questionID
           FROM choice
          WHERE questionID = ?
          ORDER BY choiceID'
    );
    $statement->execute([$questionID]);

    return $statement->fetchAll();
}

// Add a question and its choices (creates the quiz if it is the first question)
function insertQuestion($destinationID, $quizTitle, $quizPoints, $text, $imagePath, $choices) {

    $pdo = db();
    $pdo->beginTransaction();

    try {

        $quiz = findQuizByDestination($destinationID);

        if ($quiz === null) {
            $statement = $pdo->prepare(
                'INSERT INTO quiz (title, totalPoints, destinationID) VALUES (?, ?, ?)'
            );
            $statement->execute([$quizTitle, $quizPoints, $destinationID]);
            $quizID = (int)$pdo->lastInsertId();
        } else {
            $quizID = (int)$quiz['quizID'];
        }

        $statement = $pdo->prepare(
            'INSERT INTO question (text, imagePath, quizID) VALUES (?, ?, ?)'
        );
        $statement->execute([$text, $imagePath, $quizID]);
        $questionID = (int)$pdo->lastInsertId();

        insertChoices($pdo, $questionID, $choices);

        $pdo->commit();
        return $questionID;

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Update a question and replace its choices
function updateQuestion($questionID, $text, $imagePath, $choices) {

    $pdo = db();
    $pdo->beginTransaction();

    try {

        $statement = $pdo->prepare(
            'UPDATE question SET text = ?, imagePath = ? WHERE questionID = ?'
        );
        $statement->execute([$text, $imagePath, $questionID]);

        $statement = $pdo->prepare('DELETE FROM choice WHERE questionID = ?');
        $statement->execute([$questionID]);

        insertChoices($pdo, $questionID, $choices);

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Each choice is ['text' => ..., 'imagePath' => ..., 'isCorrect' => bool]
function insertChoices($pdo, $questionID, $choices) {

    $statement = $pdo->prepare(
        'INSERT INTO choice (text, imagePath, isCorrect, questionID) VALUES (?, ?, ?, ?)'
    );

    foreach ($choices as $choice) {
        $statement->execute([
            $choice['text'] === '' ? null : $choice['text'],
            $choice['imagePath'] === '' ? null : $choice['imagePath'],
            $choice['isCorrect'] ? 1 : 0,
            $questionID
        ]);
    }
}

// Change the points of a quiz (points already earned are not changed)
function updateQuizPoints($quizID, $points) {

    $statement = db()->prepare('UPDATE quiz SET totalPoints = ? WHERE quizID = ?');
    $statement->execute([$points, $quizID]);
}

// Quiz attempts, points, and badges

// The saved completion of a quiz by a child, or null
function findQuizAttempt($childID, $quizID) {

    $statement = db()->prepare(
        'SELECT childID, quizID, score, pointsEarned, completedDate
           FROM quizattempt
          WHERE childID = ? AND quizID = ?'
    );
    $statement->execute([$childID, $quizID]);

    $row = $statement->fetch();

    return $row === false ? null : $row;
}

// Save the first attempt, add the points and give new badges (one transaction)
function saveAttemptWithRewards($childID, $quizID, $score, $pointsEarned) {

    $pdo = db();
    $pdo->beginTransaction();

    try {

        $statement = $pdo->prepare(
            'INSERT INTO quizattempt (childID, quizID, score, pointsEarned, completedDate)
             VALUES (?, ?, ?, ?, NOW())'
        );
        $statement->execute([$childID, $quizID, $score, $pointsEarned]);

        $statement = $pdo->prepare(
            'UPDATE child SET totalPoints = totalPoints + ? WHERE userID = ?'
        );
        $statement->execute([$pointsEarned, $childID]);

        $statement = $pdo->prepare('SELECT totalPoints FROM child WHERE userID = ?');
        $statement->execute([$childID]);
        $newTotal = (int)$statement->fetchColumn();

        $statement = $pdo->prepare(
            'SELECT badgeID, title, description, iconPath, requiredPoints
               FROM badge
              WHERE requiredPoints <= ?
                AND badgeID NOT IN (SELECT badgeID FROM badgeaward WHERE childID = ?)
              ORDER BY requiredPoints'
        );
        $statement->execute([$newTotal, $childID]);
        $newBadges = $statement->fetchAll();

        $award = $pdo->prepare(
            'INSERT INTO badgeaward (childID, badgeID, earnedDate) VALUES (?, ?, NOW())'
        );

        foreach ($newBadges as $badge) {
            $award->execute([$childID, $badge['badgeID']]);
        }

        $pdo->commit();
        return $newBadges;

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Every badge
function findBadges() {

    return db()->query(
        'SELECT badgeID, title, description, iconPath, requiredPoints
           FROM badge
          ORDER BY requiredPoints'
    )->fetchAll();
}

// One badge, or null
function findBadge($badgeID) {

    $statement = db()->prepare(
        'SELECT badgeID, title, description, iconPath, requiredPoints FROM badge WHERE badgeID = ?'
    );
    $statement->execute([$badgeID]);

    $row = $statement->fetch();

    return $row === false ? null : $row;
}

// Is a badge title already used by another badge?
function badgeTitleExists($title, $exceptBadgeID) {

    $statement = db()->prepare(
        'SELECT COUNT(*) FROM badge WHERE title = ? AND badgeID <> ?'
    );
    $statement->execute([$title, $exceptBadgeID]);

    return (int)$statement->fetchColumn() > 0;
}

// Change the name and the description of a badge
function updateBadgeText($badgeID, $title, $description) {

    $statement = db()->prepare(
        'UPDATE badge SET title = ?, description = ? WHERE badgeID = ?'
    );
    $statement->execute([$title, $description, $badgeID]);
}

// The badges a child has earned, with the date
function findEarnedBadges($childID) {

    $statement = db()->prepare(
        'SELECT b.badgeID, b.title, b.description, b.iconPath, ba.earnedDate
           FROM badgeaward ba
           JOIN badge b ON b.badgeID = ba.badgeID
          WHERE ba.childID = ?
          ORDER BY ba.earnedDate, b.requiredPoints'
    );
    $statement->execute([$childID]);

    return $statement->fetchAll();
}

// The IDs of the destinations whose quiz the child has completed
function findCompletedDestinationIDs($childID) {

    $statement = db()->prepare(
        'SELECT q.destinationID
           FROM quizattempt qa
           JOIN quiz q ON q.quizID = qa.quizID
          WHERE qa.childID = ?'
    );
    $statement->execute([$childID]);

    $ids = [];
    foreach ($statement->fetchAll() as $row) {
        $ids[] = (int)$row['destinationID'];
    }

    return $ids;
}
