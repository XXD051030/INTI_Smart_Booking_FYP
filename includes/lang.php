<?php
session_start();

// 默认语言
$language = $_SESSION['language'] ?? 'en';

// 加载对应语言文件
$lang = include "lang/$language.php";
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo $lang['settings']; ?></title>
</head>
<body>
    <h1><?php echo $lang['greeting']; ?></h1>
    <a href="language.php"><?php echo $lang['settings']; ?></a>
</body>
</html>
