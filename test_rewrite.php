<?php
// Test file to check Apache configuration
echo "<h2>Apache Configuration Test</h2>";

// Check if mod_rewrite is loaded
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<h3>Apache Modules:</h3>";
    if (in_array('mod_rewrite', $modules)) {
        echo "<span style='color: green;'>✓ mod_rewrite is ENABLED</span><br>";
    } else {
        echo "<span style='color: red;'>✗ mod_rewrite is NOT ENABLED</span><br>";
    }
} else {
    echo "<h3>Apache Modules:</h3>";
    echo "<span style='color: orange;'>⚠ Cannot check Apache modules (function not available)</span><br>";
}

// Check PHP version
echo "<h3>PHP Configuration:</h3>";
echo "PHP Version: " . phpversion() . "<br>";

// Check if .htaccess is being read
echo "<h3>.htaccess Test:</h3>";
if (isset($_GET['test'])) {
    echo "<span style='color: green;'>✓ .htaccess rewrite is working!</span><br>";
} else {
    echo "<span style='color: orange;'>⚠ Access this file as: test_rewrite.php?test=1</span><br>";
}

// Check server variables
echo "<h3>Server Information:</h3>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// Check if .htaccess file exists and is readable
$htaccess_path = dirname(__FILE__) . '/.htaccess';
if (file_exists($htaccess_path)) {
    echo "<span style='color: green;'>✓ .htaccess file exists</span><br>";
    if (is_readable($htaccess_path)) {
        echo "<span style='color: green;'>✓ .htaccess file is readable</span><br>";
        echo "<h4>.htaccess content:</h4>";
        echo "<pre>" . htmlspecialchars(file_get_contents($htaccess_path)) . "</pre>";
    } else {
        echo "<span style='color: red;'>✗ .htaccess file is NOT readable</span><br>";
    }
} else {
    echo "<span style='color: red;'>✗ .htaccess file does NOT exist</span><br>";
}
?> 