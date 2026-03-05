<?php
require_once '../includes/config.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];

$favoris = $db->prepare("
    SELECT m.*, v.nom as ville_nom, t.libelle as type_libelle
    FROM favoris f JOIN maisons m ON f.maison_id=m.id JOIN villes v ON m.ville_id=v.id JOIN types_maison t ON m.type_id=t.id
    WHERE f.user_id=? ORDER BY f.date_ajout DESC
");
$favoris->execute([$uid]);
$favoris = $favoris->fetchAll();

$page_title = 'Mes Favoris';
include 'includes/sidebar_locataire.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar">
        <div class="topbar-title">Mes Favoris (<?= count($favoris) ?>)</div>
    </div>
    <div class="dashboard-content">
        <?php if (empty($favoris)): ?>
        <div style="text-align:center;padding:80px;background:var(--white);border-radius:20px">
            <i class="fas fa-heart fa-4x" style="color:var(--gold);opacity:0.3;margin-bottom:20px;display:block"></i>
            <h3 style="margin-bottom:10px">Aucun favori</h3>
            <p style="color:var(--text-muted);margin-bottom:20px">Ajoutez des annonces à vos favoris en cliquant sur ❤️</p>
            <a href="<?= SITE_URL ?>/maisons.php" class="btn btn-gold"><i class="fas fa-search"></i> Explorer les annonces</a>
        </div>
        <?php else: ?>
        <div class="maisons-grid">
            <?php foreach ($favoris as $m): ?>
            <div class="maison-card">
                <div class="card-image">
                    <?php if ($m['photo_principale']): ?>
                    <img src="<?= SITE_URL ?>/uploads/maisons/<?= $m['photo_principale'] ?>" alt=""
                         onerror="this.parentElement.innerHTML='<div class=\'card-image-placeholder\'><i class=\'fas fa-home\'></i></div>'">
                    <?php else: ?>
                    <div class="card-image-placeholder"><i class="fas fa-home"></i></div>
                    <?php endif; ?>
                    <span class="card-badge badge-<?= $m['disponibilite'] ?>"><?= $m['disponibilite'] === 'disponible' ? '✓ Disponible' : '✗ Loué' ?></span>
                    <button class="card-fav toggle-fav active" data-id="<?= $m['id'] ?>"><i class="fas fa-heart"></i></button>
                </div>
                <div class="card-body">
                    <span class="card-type"><?= htmlspecialchars($m['type_libelle']) ?></span>
                    <h3 class="card-title"><?= htmlspecialchars($m['titre']) ?></h3>
                    <div class="card-location"><i class="fas fa-map-marker-alt" style="color:var(--gold)"></i> <?= htmlspecialchars($m['quartier'] ?? '') ?>, <?= htmlspecialchars($m['ville_nom']) ?></div>
                    <div class="card-features">
                        <?php if ($m['nb_chambres'] > 0): ?>
                        <div class="card-feature"><i class="fas fa-bed"></i><span><?= $m['nb_chambres'] ?> ch.</span></div>
                        <?php endif; ?>
                        <div class="card-feature"><i class="fas fa-bath"></i><span><?= $m['nb_salles_bain'] ?> sdb</span></div>
                        <?php if ($m['surface']): ?>
                        <div class="card-feature"><i class="fas fa-expand-arrows-alt"></i><span><?= $m['surface'] ?> m²</span></div>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <div class="card-prix"><?= formatPrix($m['prix_mensuel']) ?><span>/mois</span></div>
                        <a href="<?= SITE_URL ?>/detail.php?id=<?= $m['id'] ?>" class="card-btn">Voir plus</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
