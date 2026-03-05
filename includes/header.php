<?php
// Header commun pour toutes les pages publiques
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$notifs = isLoggedIn() ? countNotifsNonLues($_SESSION['user_id']) : 0;
$msgs = isLoggedIn() ? countMessagesNonLus($_SESSION['user_id']) : 0;
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | ' : '' ?>PlanèteImmo</title>
    <meta name="description" content="PlanèteImmo - Location de maisons et appartements au Bénin. Trouvez votre logement idéal à Cotonou, Porto-Novo, Parakou et partout au Bénin.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>

<?php if ($flash): ?>
<div class="flash-message flash-<?= $flash['type'] ?>" id="flashMsg">
    <i class="fas fa-<?= $flash['type'] === 'succes' ? 'check-circle' : ($flash['type'] === 'erreur' ? 'times-circle' : 'info-circle') ?>"></i>
    <?= htmlspecialchars($flash['message']) ?>
    <button onclick="this.parentElement.remove()">×</button>
</div>
<?php endif; ?>

<nav class="navbar" id="navbar">
    <div class="nav-container">
        <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
            <span class="logo-icon">🏡</span>
            <span class="logo-text">Planète<span class="logo-accent">Immo</span></span>
        </a>

        <div class="nav-links" id="navLinks">
            <a href="<?= SITE_URL ?>/index.php" class="<?= $current_page === 'index' ? 'active' : '' ?>">Accueil</a>
            <a href="<?= SITE_URL ?>/maisons.php" class="<?= $current_page === 'maisons' ? 'active' : '' ?>">Annonces</a>
            <a href="<?= SITE_URL ?>/maisons.php?type=villa" class="<?= '' ?>">Villas</a>
            <a href="<?= SITE_URL ?>/maisons.php?type=appartement" class="">Appartements</a>
            <a href="<?= SITE_URL ?>/contact.php" class="<?= $current_page === 'contact' ? 'active' : '' ?>">Contact</a>
        </div>

        <div class="nav-actions">
            <?php if (isLoggedIn()): ?>
                <?php if (isAdmin()): ?>
                    <a href="<?= SITE_URL ?>/admin/dashboard.php" class="btn-nav btn-dashboard">
                        <i class="fas fa-tachometer-alt"></i> Admin
                    </a>
                <?php elseif (isAgent()): ?>
                    <a href="<?= SITE_URL ?>/agent/dashboard.php" class="btn-nav btn-dashboard">
                        <i class="fas fa-tachometer-alt"></i> Espace Agent
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/locataire/dashboard.php" class="btn-nav btn-dashboard">
                        <i class="fas fa-tachometer-alt"></i> Mon Espace
                    </a>
                <?php endif; ?>
                
                <?php if ($notifs > 0 || $msgs > 0): ?>
                <a href="<?= SITE_URL ?>/<?= getRole() ?>/messages.php" class="notif-bell">
                    <i class="fas fa-bell"></i>
                    <span class="notif-badge"><?= $notifs + $msgs ?></span>
                </a>
                <?php endif; ?>

                <div class="user-dropdown">
                    <button class="user-btn">
                        <img src="<?= SITE_URL ?>/uploads/avatars/<?= $_SESSION['avatar'] ?? 'default.png' ?>" alt="Avatar" class="user-avatar-sm">
                        <span><?= htmlspecialchars($_SESSION['prenom'] ?? 'Mon compte') ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?= SITE_URL ?>/profil.php"><i class="fas fa-user"></i> Mon Profil</a>
                        <a href="<?= SITE_URL ?>/locataire/favoris.php"><i class="fas fa-heart"></i> Mes Favoris</a>
                        <a href="<?= SITE_URL ?>/logout.php" class="logout-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="btn-nav btn-outline">Connexion</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn-nav btn-primary">S'inscrire</a>
            <?php endif; ?>
        </div>

        <button class="nav-toggle" id="navToggle" onclick="toggleNav()">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>
