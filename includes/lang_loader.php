<?php
session_start();

$current = $_SESSION['language'] ?? 'en';

$langFile = __DIR__ . "/lang/$current.php";
if (file_exists($langFile)) {
    $text = include $langFile;
} else {
    $text = include __DIR__ . "/lang/en.php";
}
