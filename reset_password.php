<?php
// Temporary password reset script
// DELETE THIS FILE after resetting your password!

require_once 'includes/config.php';
require_once 'includes/db.php';

$newPassword = 'admin123';
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hashedPassword]);

    echo "<h2>Password Reset Successful!</h2>";
    echo "<p>Username: <strong>admin</strong></p>";
    echo "<p>Password: <strong>admin123</strong></p>";
    echo "<p><a href='admin/login.php'>Go to Login Page</a></p>";
    echo "<hr>";
    echo "<p style='color: red;'><strong>IMPORTANT: Delete this file (reset_password.php) immediately for security!</strong></p>";
    echo "<p>New password hash: " . htmlspecialchars($hashedPassword) . "</p>";

} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<hr>";
    echo "<p>Database connection settings:</p>";
    echo "<pre>";
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    echo "DB_USER: " . DB_USER . "\n";
    echo "</pre>";
}
?>
