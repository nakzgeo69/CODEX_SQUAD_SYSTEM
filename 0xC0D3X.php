<?php
/*
 * Deobfuscated Educational Example
 * WARNING: Original code appears to be malware
 * This version is for educational analysis only
 */

class EducationalDeobfuscator {
    private $cryptKey = 'GeoDevz69#';
    private $sessionStarted = false;
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->sessionStarted = true;
        }
    }
    
    private function xorCrypt(string $data): string {
        $out = '';
        $key = $this->cryptKey;
        $klen = strlen($key);
        $len = strlen($data);
        
        for ($i = 0; $i < $len; $i++) {
            $out .= chr(ord($data[$i]) ^ ord($key[$i % $klen]));
        }
        return $out;
    }
    
    private function complexMathCalculation(): array {
        // Matrix multiplication and trigonometry calculations
        $M = [
            [3, 2, -1],
            [1, 0, 4],
            [5, -2, 3]
        ];
        
        $angle = pi() / 6;
        $v = [
            sin($angle) * 100,
            cos($angle) * 100,
            tan($angle) * 100
        ];
        
        $nV = [0, 0, 0];
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                $nV[$i] += $M[$i][$j] * $v[$j];
            }
        }
        
        $z1 = [3, 2];
        $z2 = [1, 7];
        
        $compA = $z1[0] * $z2[0] - $z1[1] * $z2[1];
        $compB = $z1[0] * $z2[1] + $z1[1] * $z2[0];
        
        $eyyenyneo = abs($compA);
        $nainnnnllnl = abs($compB);
        $kirtniettat = abs((int)(log($angle) * 100));
        $kfowqnontt = abs((int)(atan($angle) * 100));
        $mfoiqnwotow = abs((int)(sqrt($angle) * 100));
        
        $key = (
            (int)($nV[0] + $nV[1] + $nV[2])
            ^ $eyyenyneo
            ^ $nainnnnllnl
            ^ $kirtniettat
            ^ $kfowqnontt
            ^ $mfoiqnwotow
        ) & 0xFF;
        
        return [
            'key' => $key,
            'eyyenyneo' => $eyyenyneo,
            'nainnnnllnl' => $nainnnnllnl
        ];
    }
    
    private function decodePayload(): string {
        // This would normally contain the encrypted payload
        // For this educational version, we'll return harmless HTML
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Educational Example</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .info { background: #f0f0f0; padding: 15px; border-radius: 5px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; }
            </style>
        </head>
        <body>
            <h1>Educational Deobfuscation Example</h1>
            <div class="warning">
                <strong>Note:</strong> This is a sanitized educational example.
                Original code appeared to be obfuscated malware.
            </div>
            <div class="info">
                <p>This demonstrates how complex obfuscation works.</p>
                <p>Features used in original:</p>
                <ul>
                    <li>Matrix mathematics</li>
                    <li>Trigonometric functions</li>
                    <li>XOR encryption</li>
                    <li>Complex payload encoding</li>
                </ul>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    private function authenticateUser(string $username, string $password): bool {
        // Simple authentication - in production, use proper hashing
        $validUsername = 'admin';
        $validPassword = 'GeoDevz69#';
        
        return ($username === $validUsername && $password === $validPassword);
    }
    
    public function showLoginForm(string $error = ''): void {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Secure Login</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                }
                
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    padding: 20px;
                }
                
                .login-container {
                    background: rgba(255, 255, 255, 0.95);
                    border-radius: 20px;
                    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
                    width: 100%;
                    max-width: 400px;
                    overflow: hidden;
                }
                
                .login-header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 40px 30px;
                    text-align: center;
                }
                
                .login-header h1 {
                    font-size: 28px;
                    margin-bottom: 10px;
                    font-weight: 600;
                }
                
                .login-header p {
                    opacity: 0.9;
                    font-size: 14px;
                }
                
                .login-body {
                    padding: 40px 30px;
                }
                
                .form-group {
                    margin-bottom: 25px;
                    position: relative;
                }
                
                .form-group label {
                    display: block;
                    margin-bottom: 8px;
                    color: #333;
                    font-weight: 500;
                    font-size: 14px;
                }
                
                .input-with-icon {
                    position: relative;
                }
                
                .input-with-icon i {
                    position: absolute;
                    left: 15px;
                    top: 50%;
                    transform: translateY(-50%);
                    color: #667eea;
                    font-size: 18px;
                }
                
                .input-with-icon input {
                    width: 100%;
                    padding: 15px 15px 15px 50px;
                    border: 2px solid #e1e5e9;
                    border-radius: 10px;
                    font-size: 16px;
                    transition: all 0.3s ease;
                    background: #f8f9fa;
                }
                
                .input-with-icon input:focus {
                    outline: none;
                    border-color: #667eea;
                    background: white;
                    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                }
                
                .error-message {
                    background: #fee;
                    color: #c33;
                    padding: 12px;
                    border-radius: 8px;
                    margin-bottom: 20px;
                    border: 1px solid #fcc;
                    font-size: 14px;
                    text-align: center;
                }
                
                .login-button {
                    width: 100%;
                    padding: 16px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    border-radius: 10px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                }
                
                .login-button:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
                }
                
                .login-button:active {
                    transform: translateY(0);
                }
                
                .forgot-password {
                    text-align: center;
                    margin-top: 20px;
                }
                
                .forgot-password a {
                    color: #667eea;
                    text-decoration: none;
                    font-size: 14px;
                    transition: color 0.3s;
                }
                
                .forgot-password a:hover {
                    color: #764ba2;
                    text-decoration: underline;
                }
                
                .security-notice {
                    margin-top: 30px;
                    padding: 15px;
                    background: #f8f9fa;
                    border-radius: 10px;
                    font-size: 12px;
                    color: #666;
                    text-align: center;
                    border: 1px solid #e1e5e9;
                }
                
                .security-notice i {
                    color: #667eea;
                    margin-right: 5px;
                }
                
                @media (max-width: 480px) {
                    .login-container {
                        border-radius: 10px;
                    }
                    
                    .login-header {
                        padding: 30px 20px;
                    }
                    
                    .login-body {
                        padding: 30px 20px;
                    }
                }
            </style>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        </head>
        <body>
            <div class="login-container">
                <div class="login-header">
                    <h1><i class="fas fa-lock"></i> Secure Portal</h1>
                    <p>Enter your credentials to continue</p>
                </div>
                
                <div class="login-body">
                    <?php if ($error): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       placeholder="Enter your username" 
                                       required 
                                       autocomplete="username"
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-with-icon">
                                <i class="fas fa-key"></i>
                                <input type="password" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Enter your password" 
                                       required 
                                       autocomplete="current-password">
                            </div>
                        </div>
                        
                        <input type="hidden" name="login" value="1">
                        
                        <button type="submit" class="login-button">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                        
                        <div class="forgot-password">
                            <a href="#"><i class="fas fa-question-circle"></i> Forgot password?</a>
                        </div>
                    </form>
                    
                    <div class="security-notice">
                        <i class="fas fa-shield-alt"></i>
                        This is a secure system. Your credentials are protected.
                    </div>
                </div>
            </div>
            
            <script>
                // Form validation enhancement
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.querySelector('form');
                    const username = document.getElementById('username');
                    const password = document.getElementById('password');
                    
                    form.addEventListener('submit', function(e) {
                        let valid = true;
                        
                        // Clear previous errors
                        document.querySelectorAll('.input-with-icon input').forEach(input => {
                            input.style.borderColor = '#e1e5e9';
                        });
                        
                        // Validate username
                        if (!username.value.trim()) {
                            username.style.borderColor = '#dc3545';
                            valid = false;
                        }
                        
                        // Validate password
                        if (!password.value) {
                            password.style.borderColor = '#dc3545';
                            valid = false;
                        }
                        
                        if (!valid) {
                            e.preventDefault();
                            alert('Please fill in all required fields');
                        }
                    });
                    
                    // Real-time validation
                    username.addEventListener('input', function() {
                        if (this.value.trim()) {
                            this.style.borderColor = '#28a745';
                        }
                    });
                    
                    password.addEventListener('input', function() {
                        if (this.value) {
                            this.style.borderColor = '#28a745';
                        }
                    });
                });
            </script>
        </body>
        </html>
        <?php
    }
    
    public function processLogin(): bool {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['login'])) {
            return false;
        }
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($this->authenticateUser($username, $password)) {
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['login_time'] = time();
            $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
            $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
            return true;
        }
        
        return false;
    }
    
    public function isAuthenticated(): bool {
        return isset($_SESSION['authenticated']) && 
               $_SESSION['authenticated'] === true &&
               $_SESSION['user_ip'] === $_SERVER['REMOTE_ADDR'] &&
               $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT'] &&
               (time() - $_SESSION['login_time'] < 3600); // 1 hour timeout
    }
    
    public function logout(): void {
        session_unset();
        session_destroy();
        if ($this->sessionStarted) {
            session_start();
        }
    }
    
    public function run(): void {
        // Check if user is trying to login
        if ($this->processLogin()) {
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
        
        // Check if user is authenticated
        if (!$this->isAuthenticated()) {
            $error = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
                $error = 'Invalid username or password';
            }
            $this->showLoginForm($error);
            return;
        }
        
        // User is authenticated - show content
        $this->displayContent();
    }
    
    private function displayContent(): void {
        // Perform calculations
        $calculations = $this->complexMathCalculation();
        
        // Decode and display payload
        $content = $this->decodePayload();
        
        // Display with logout option
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Authenticated Area</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 0;
                    padding: 20px;
                    background: #f5f5f5;
                }
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 20px;
                    border-radius: 10px;
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                .logout-btn {
                    background: white;
                    color: #667eea;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                }
                .logout-btn:hover {
                    background: #f8f9fa;
                }
                .content {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                .info-box {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin: 20px 0;
                    border-left: 4px solid #667eea;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                <form method="POST" action="">
                    <input type="hidden" name="logout" value="1">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
            
            <div class="content">
                <div class="info-box">
                    <h3>Calculation Results:</h3>
                    <p>Generated Key: <?php echo $calculations['key']; ?></p>
                    <p>Complex Values: <?php echo $calculations['eyyenyneo']; ?>, <?php echo $calculations['nainnnnllnl']; ?></p>
                </div>
                
                <?php echo $content; ?>
                
                <div class="info-box">
                    <h3>Session Information:</h3>
                    <p>IP Address: <?php echo $_SESSION['user_ip']; ?></p>
                    <p>Login Time: <?php echo date('Y-m-d H:i:s', $_SESSION['login_time']); ?></p>
                    <p>Session Duration: <?php echo floor((time() - $_SESSION['login_time']) / 60); ?> minutes</p>
                </div>
            </div>
            
            <?php
            // Handle logout
            if (isset($_POST['logout'])) {
                $this->logout();
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;
            }
            ?>
            
            <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
        </body>
        </html>
        <?php
    }
}

// Initialize and run the application
$app = new EducationalDeobfuscator();
$app->run();
