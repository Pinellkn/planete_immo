<?php
$current = basename($_SERVER['PHP_SELF'], '.php');
$db = getDB();
$nb_dem_att = $db->query("SELECT COUNT(*) FROM demandes_location WHERE statut='en_attente'")->fetchColumn();
$msgs = countMessagesNonLus($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | ' : '' ?>Admin - PlanèteImmo</title>
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
        <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold-light));display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:18px;border:2px solid var(--gold)">
            <?= strtoupper(substr($_SESSION['prenom'] ?? 'A', 0, 1)) ?>
        </div>
        <div>
            <div class="sidebar-user-name"><?= htmlspecialchars(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? '')) ?></div>
            <span class="sidebar-user-role">Administrateur</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="sidebar-section-title">Principal</div>
        <a href="<?= SITE_URL ?>/admin/dashboard.php" class="sidebar-link <?= $current==='dashboard'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
        <div class="sidebar-section-title" style="margin-top:12px">Gestion</div>
        <a href="<?= SITE_URL ?>/admin/maisons.php" class="sidebar-link <?= $current==='maisons'?'active':'' ?>"><i class="fas fa-home"></i> Annonces</a>
        <a href="<?= SITE_URL ?>/admin/utilisateurs.php" class="sidebar-link <?= $current==='utilisateurs'?'active':'' ?>"><i class="fas fa-users"></i> Utilisateurs</a>
        <a href="<?= SITE_URL ?>/admin/demandes.php" class="sidebar-link <?= $current==='demandes'?'active':'' ?>">
            <i class="fas fa-file-alt"></i> Demandes
            <?php if ($nb_dem_att > 0): ?><span class="sidebar-badge"><?= $nb_dem_att ?></span><?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/admin/contrats.php" class="sidebar-link <?= $current==='contrats'?'active':'' ?>"><i class="fas fa-file-contract"></i> Contrats</a>
        <a href="<?= SITE_URL ?>/admin/paiements.php" class="sidebar-link <?= $current==='paiements'?'active':'' ?>"><i class="fas fa-credit-card"></i> Paiements</a>
        <a href="<?= SITE_URL ?>/admin/messages.php" class="sidebar-link <?= $current==='messages'?'active':'' ?>">
            <i class="fas fa-envelope"></i> Messages
            <?php if ($msgs > 0): ?><span class="sidebar-badge"><?= $msgs ?></span><?php endif; ?>
        </a>
        <div class="sidebar-section-title" style="margin-top:12px">Config</div>
        <a href="<?= SITE_URL ?>/admin/parametres.php" class="sidebar-link <?= $current==='parametres'?'active':'' ?>"><i class="fas fa-cog"></i> Paramètres</a>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= SITE_URL ?>/index.php" target="_blank"><i class="fas fa-globe"></i> Voir le site</a>
        <a href="<?= SITE_URL ?>/logout.php" style="color:#f87171;margin-top:8px"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>
