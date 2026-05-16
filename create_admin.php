<?php
// Temporary script to create admin user
require_once 'config/config.php';

// Create new password hash for 'admin123'
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Update admin user
$stmt = $db->prepare("UPDATE users SET password = ? WHERE email = 'admin@marketplace.com'");
$stmt->execute([$hashedPassword]);

echo "Admin password reset successfully!<br>";
echo "Email: admin@marketplace.com<br>";
echo "Password: admin123<br>";
echo "<br><a href='login.php'>Go to Login</a>";
?>
