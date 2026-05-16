<?php
// Application Configuration
session_start();

// Directory Paths
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('PRODUCTS_PATH', UPLOAD_PATH . 'products/');
define('SCREENSHOTS_PATH', UPLOAD_PATH . 'screenshots/');
define('TEMP_PATH', UPLOAD_PATH . 'temp/');

// Environment and Base URL Configuration
// Vercel/production values can be configured with environment variables.
define('SITE_ENV', getenv('SITE_ENV') ?: (getenv('VERCEL') ? 'production' : 'development'));

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || getenv('VERCEL') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (getenv('APP_URL')) {
    $configuredBaseUrl = rtrim(getenv('APP_URL'), '/') . '/';
} elseif (getenv('VERCEL_URL')) {
    $configuredBaseUrl = 'https://' . rtrim(getenv('VERCEL_URL'), '/') . '/';
} else {
    $configuredBaseUrl = null;
}
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$basePath = preg_replace('#/admin$#', '', $scriptDir);
$basePath = rtrim($basePath, '/');
define('BASE_URL', $configuredBaseUrl ?: ($protocol . '://' . $host . ($basePath ? $basePath . '/' : '/')));

// Disable error display in production
if (defined('SITE_ENV') && SITE_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
}
define('SITE_NAME', 'Digital Marketplace');

// Database Configuration - UPDATE THESE AFTER CREATING DATABASE
require_once ROOT_PATH . '/config/database.php';

// Create directories if they don't exist
$directories = [UPLOAD_PATH, PRODUCTS_PATH, SCREENSHOTS_PATH, TEMP_PATH];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        // Serverless platforms like Vercel have a read-only project filesystem.
        // Uploaded files should be stored externally in production.
        @mkdir($dir, 0755, true);
    }
}

// Timezone
date_default_timezone_set('UTC');

// Error Reporting
if (defined('SITE_ENV') && SITE_ENV === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Helper Functions
function redirect($url) {
    $path = ltrim($url, '/');

    // Normalize legacy relative paths used in admin pages (e.g. ../login.php)
    while (strpos($path, '../') === 0) {
        $path = substr($path, 3);
    }
    if (strpos($path, './') === 0) {
        $path = substr($path, 2);
    }

    header("Location: " . BASE_URL . $path);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'super_admin']);
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function formatPrice($price) {
    $currency = getSetting('currency_symbol') ?? '$';
    return $currency . number_format($price, 2);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}

function getSetting($key) {
    global $db;
    if (!isset($db)) {
        $database = new Database();
        $db = $database->getConnection();
    }
    
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : null;
}

function updateSetting($key, $value) {
    global $db;
    if (!isset($db)) {
        $database = new Database();
        $db = $database->getConnection();
    }
    
    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    return $stmt->execute([$value, $key]);
}

function sendEmail($to, $subject, $body) {
    // Email configuration from settings
    $smtp_host = getSetting('smtp_host');
    $smtp_port = getSetting('smtp_port');
    $smtp_username = getSetting('smtp_username');
    $smtp_password = getSetting('smtp_password');
    
    // For now, using PHP mail() - integrate PHPMailer for production
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: " . getSetting('site_email') . "\r\n";
    
    return mail($to, $subject, $body, $headers);
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function generateSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    return $slug;
}

function uploadFile($file, $destination) {
    $targetFile = $destination . basename($file["name"]);
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return basename($file["name"]);
    }
    return false;
}

// Initialize Database Connection
$database = new Database();
$db = $database->getConnection();
?>
