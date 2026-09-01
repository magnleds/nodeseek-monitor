<?php
$file = 'nodeseek.txt';
$textToDelete = $_POST['text'];

if (file_exists($file)) {
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    $newLines = array_filter($lines, function($line) use ($textToDelete) {
        return $line !== $textToDelete;
    });
    file_put_contents($file, implode(PHP_EOL, $newLines));
}
?>