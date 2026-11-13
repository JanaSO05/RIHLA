<?php

function loadEnvFile($path) {

    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {

        $line = trim($line);

        if ($line == '' || $line[0] == '#' || strpos($line, '=') === false) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim(trim($value), "\"'");

        if (getenv($name) === false) {
            putenv($name . '=' . $value);
        }
    }
}

loadEnvFile(dirname(__DIR__) . '/.env');

$AI_API_KEY    = getenv('AI_API_KEY') ?: '';
$AI_MODEL      = getenv('AI_MODEL') ?: 'gemini-3.6-flash';
$AI_TIME_LIMIT = 15;
