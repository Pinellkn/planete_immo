<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
$db = getDB();
$nb_dem = $db->prepare("SELECT COUNT(*) FROM demandes_location WHERE agent_id=? AND statut='en_attente'");
$nb_dem->execute([$_SESSION['user_id']]);
$nb_dem_att = $nb_dem->fetchColumn();
$msgs = countMessagesNonLus($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | ' : '' ?>Agent - PlanèteImmo</title>
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
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#3B82F6,#60A5FA);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:18px">
            <?= strtoupper(substr($_SESSION['prenom'] ?? 'A', 0, 1)) ?>
        </div>
        <div>
            <div class="sidebar-user-name"><?= htmlspecialchars(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? '')) ?></div>
            <span class="sidebar-user-role" style="background:rgba(59,130,246,0.2);color:#60A5FA">Agent</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-title">Principal</div>
        <a href="<?= SITE_URL ?>/agent/dashboard.php" class="sidebar-link <?= $current==='dashboard'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
        <div class="sidebar-section-title" style="margin-top:12px">Annonces</div>
        <a href="<?= SITE_URL ?>/agent/mes_maisons.php" class="sidebar-link <?= $current==='mes_maisons'?'active':'' ?>"><i class="fas fa-home"></i> Mes annonces</a>
        <a href="<?= SITE_URL ?>/agent/ajouter_maison.php" class="sidebar-link <?= $current==='ajouter_maison'?'active':'' ?>"><i class="fas fa-plus-circle"></i> Nouvelle annonce</a>
        <div class="sidebar-section-title" style="margin-top:12px">Gestion</div>
        <a href="<?= SITE_URL ?>/agent/demandes.php" class="sidebar-link <?= $current==='demandes'?'active':'' ?>">
            <i class="fas fa-file-alt"></i> Demandes <?php if ($nb_dem_att > 0): ?><span class="sidebar-badge"><?= $nb_dem_att ?></span><?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/agent/contrats.php" class="sidebar-link <?= $current==='contrats'?'active':'' ?>"><i class="fas fa-file-contract"></i> Contrats</a>
        <a href="<?= SITE_URL ?>/agent/paiements.php" class="sidebar-link <?= $current==='paiements'?'active':'' ?>"><i class="fas fa-credit-card"></i> Paiements</a>
        <a href="<?= SITE_URL ?>/agent/messages.php" class="sidebar-link <?= $current==='messages'?'active':'' ?>">
            <i class="fas fa-envelope"></i> Messages <?php if ($msgs > 0): ?><span class="sidebar-badge"><?= $msgs ?></span><?php endif; ?>
        </a>
        <div class="sidebar-section-title" style="margin-top:12px">Compte</div>
        <a href="<?= SITE_URL ?>/profil.php" class="sidebar-link"><i class="fas fa-user"></i> Mon profil</a>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= SITE_URL ?>/index.php" target="_blank"><i class="fas fa-globe"></i> Voir le site</a>
        <a href="<?= SITE_URL ?>/logout.php" style="color:#f87171;margin-top:8px"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>
