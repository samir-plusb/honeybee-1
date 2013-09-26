<?php
error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 'On');

$default_context = 'console';
$environment_modifier = '';

$root_dir = dirname(dirname(__FILE__));
require  $root_dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';
unset($root_dir);

//AgaviContext::getInstance()->getDatabaseConnection();

$prompt = '> ';

// shell with history support

if (is_callable('readline_add_history')) {
    while ($line = readLine($prompt)) {
        try {
            eval($line);
            readline_add_history($line);
        } catch (Exception $exception) {
            echo (string) $exception;
        }
    }
}

// fallback shell without history when readline is not available

function readLineFromStdin()
{
    $out = fgets(STDIN);
    return rtrim($out);
}

echo $prompt;

while ($line = readLineFromStdin()) {
    try {
        eval($line);
    } catch (Exception $exception) {
        echo (string) $exception;
    }

    echo $prompt;
}

