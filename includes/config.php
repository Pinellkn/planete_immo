<?php
// ============================================================
// CONFIGURATION PRINCIPALE - BÉNIN IMMO
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'benin_immo');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('SITE_URL', 'http://localhost/planete-immo');
define('SITE_NAME', 'PlanèteImmo');
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Connexion PDO
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:30px;background:#fee;border:2px solid #f00;border-radius:8px;margin:20px">
                <h2>❌ Erreur de connexion à la base de données</h2>
                <p>Vérifiez que XAMPP est démarré et que la base <strong>benin_immo</strong> existe.</p>
                <p style="color:#900">Détail : ' . htmlspecialchars($e->getMessage()) . '</p>
            </div>');
        }
    }
    return $pdo;
}

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fonctions utilitaires
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getRole() {
    return $_SESSION['role'] ?? null;
}

function isAdmin() {
    return getRole() === 'admin';
}

function isAgent() {
    return getRole() === 'agent' || getRole() === 'admin';
}

function isLocataire() {
    return getRole() === 'locataire';
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect(SITE_URL . '/index.php?erreur=acces_refuse');
    }
}

function requireAgent() {
    requireLogin();
    if (!isAgent()) {
        redirect(SITE_URL . '/index.php?erreur=acces_refuse');
    }
}

function flashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function formatPrix($prix) {
    return number_format($prix, 0, ',', ' ') . ' FCFA';
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function countNotifsNonLues($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND lu = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function countMessagesNonLus($user_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE destinataire_id = ? AND lu = 0");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function getParam($cle) {
    $db = getDB();
    $stmt = $db->prepare("SELECT valeur FROM parametres WHERE cle = ?");
    $stmt->execute([$cle]);
    $row = $stmt->fetch();
    return $row ? $row['valeur'] : null;
}

function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->d === 0) {
        if ($diff->h === 0) return "il y a {$diff->i} min";
        return "il y a {$diff->h}h";
    }
    if ($diff->d === 1) return "hier";
    if ($diff->d < 7) return "il y a {$diff->d} jours";
    return $ago->format('d/m/Y');
}
?>
