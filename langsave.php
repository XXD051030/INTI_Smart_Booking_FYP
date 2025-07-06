<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['language'] ?? 'en';
    $_SESSION['language'] = $selected;
    header('Location: index.php');
    exit;
}

$current = $_SESSION['language'] ?? 'en';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Language Settings</title>
</head>
<body>
    <h2>Choose Your Language</h2>
    <form method="post">
        <select name="language">
            <option value="en" <?php if($current === 'en') echo 'selected'; ?>>English</option>
            <option value="ms" <?php if($current === 'ms') echo 'selected'; ?>>Bahasa Melayu</option>
            <option value="ta" <?php if($current === 'ta') echo 'selected'; ?>>தமிழ் (Tamil)</option>
            <option value="zh" <?php if($current === 'zh') echo 'selected'; ?>>中文 (Chinese)</option>
        </select>
        <button type="submit">Save Language</button>
    </form>
</body>
</html>
