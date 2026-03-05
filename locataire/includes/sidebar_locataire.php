<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
$db = getDB();
$msgs = countMessagesNonLus($_SESSION['user_id']);
$nb_dem = $db->prepare("SELECT COUNT(*) FROM demandes_location WHERE locataire_id=?");
$nb_dem->execute([$_SESSION['user_id']]);
$nb_dem_total = $nb_dem->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | ' : '' ?>Mon Espace - PlanèteImmo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div class="dashboard-layout">
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">🏡 Planète<span>Immo</span></div>
    <div class="sidebar-user">
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#22C55E,#4ADE80);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:18px">
            <?= strtoupper(substr($_SESSION['prenom'] ?? 'L', 0, 1)) ?>
        </div>
        <div>
            <div class="sidebar-user-name"><?= htmlspecialchars(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? '')) ?></div>
            <span class="sidebar-user-role" style="background:rgba(34,197,94,0.2);color:#4ADE80">Locataire</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-title">Principal</div>
        <a href="<?= SITE_URL ?>/locataire/dashboard.php" class="sidebar-link <?= $current==='dashboard'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
        <div class="sidebar-section-title" style="margin-top:12px">Recherche</div>
        <a href="<?= SITE_URL ?>/maisons.php" class="sidebar-link"><i class="fas fa-search"></i> Chercher une maison</a>
        <a href="<?= SITE_URL ?>/locataire/favoris.php" class="sidebar-link <?= $current==='favoris'?'active':'' ?>"><i class="fas fa-heart"></i> Mes favoris</a>
        <div class="sidebar-section-title" style="margin-top:12px">Mes locations</div>
        <a href="<?= SITE_URL ?>/locataire/demandes.php" class="sidebar-link <?= $current==='demandes'?'active':'' ?>">
            <i class="fas fa-file-alt"></i> Mes demandes
            <?php if ($nb_dem_total > 0): ?><span class="sidebar-badge" style="background:var(--green)"><?= $nb_dem_total ?></span><?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/locataire/contrats.php" class="sidebar-link <?= $current==='contrats'?'active':'' ?>"><i class="fas fa-file-contract"></i> Mes contrats</a>
        <a href="<?= SITE_URL ?>/locataire/paiements.php" class="sidebar-link <?= $current==='paiements'?'active':'' ?>"><i class="fas fa-credit-card"></i> Mes paiements</a>
        <a href="<?= SITE_URL ?>/locataire/messages.php" class="sidebar-link <?= $current==='messages'?'active':'' ?>">
            <i class="fas fa-envelope"></i> Messages
            <?php if ($msgs > 0): ?><span class="sidebar-badge"><?= $msgs ?></span><?php endif; ?>
        </a>
        <div class="sidebar-section-title" style="margin-top:12px">Compte</div>
        <a href="<?= SITE_URL ?>/profil.php" class="sidebar-link"><i class="fas fa-user-edit"></i> Mon profil</a>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= SITE_URL ?>/index.php" target="_blank"><i class="fas fa-globe"></i> Voir le site</a>
        <a href="<?= SITE_URL ?>/logout.php" style="color:#f87171;margin-top:8px"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>
