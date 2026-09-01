<?php
$file = 'nodeseek.txt';
$newText = $_POST['text'];

if (file_exists($file)) {
    file_put_contents($file, PHP_EOL . $newText, FILE_APPEND);
} else {
    file_put_contents($file, $newText);
}
?>