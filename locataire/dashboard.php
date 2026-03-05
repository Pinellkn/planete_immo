<?php
require_once '../includes/config.php';
requireLogin();
if (getRole() !== 'locataire') redirect(SITE_URL . '/' . getRole() . '/dashboard.php');
$db = getDB();
$uid = $_SESSION['user_id'];

$stats = $db->prepare("
    SELECT
        (SELECT COUNT(*) FROM demandes_location WHERE locataire_id=?) as mes_demandes,
        (SELECT COUNT(*) FROM demandes_location WHERE locataire_id=? AND statut='en_attente') as dem_attente,
        (SELECT COUNT(*) FROM demandes_location WHERE locataire_id=? AND statut='acceptee') as dem_acceptees,
        (SELECT COUNT(*) FROM contrats WHERE locataire_id=? AND statut='actif') as contrats_actifs,
        (SELECT COUNT(*) FROM favoris WHERE user_id=?) as favoris,
        (SELECT COALESCE(SUM(montant),0) FROM paiements WHERE locataire_id=? AND statut='valide') as total_paye
");
$stats->execute([$uid,$uid,$uid,$uid,$uid,$uid]);
$stats = $stats->fetch();

$demandes = $db->prepare("
    SELECT d.*, m.titre as maison_titre, m.prix_mensuel, m.quartier, v.nom as ville_nom
    FROM demandes_location d JOIN maisons m ON d.maison_id=m.id JOIN villes v ON m.ville_id=v.id
    WHERE d.locataire_id=? ORDER BY d.date_demande DESC LIMIT 5
");
$demandes->execute([$uid]);
$demandes = $demandes->fetchAll();

$paiements = $db->prepare("
    SELECT p.*, c.numero_contrat FROM paiements p JOIN contrats c ON p.contrat_id=c.id
    WHERE p.locataire_id=? ORDER BY p.date_paiement DESC LIMIT 5
");
$paiements->execute([$uid]);
$paiements = $paiements->fetchAll();

$notifs = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY date_creation DESC LIMIT 5");
$notifs->execute([$uid]);
$notifs = $notifs->fetchAll();
$db->prepare("UPDATE notifications SET lu=1 WHERE user_id=?")->execute([$uid]);

$page_title = 'Mon Espace';
include 'includes/sidebar_locataire.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar">
        <div>
            <div class="topbar-title">Bonjour, <?= htmlspecialchars($_SESSION['prenom']) ?> 👋</div>
            <div style="font-size:12px;color:var(--text-muted)"><?= date('d F Y') ?> • Locataire</div>
        </div>
        <div class="topbar-actions">
            <a href="<?= SITE_URL ?>/maisons.php" class="btn btn-gold btn-sm"><i class="fas fa-search"></i> Chercher une maison</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon gold"><i class="fas fa-paper-plane"></i></div>
                <div class="stat-info"><div class="stat-label">Mes demandes</div><div class="stat-value"><?= $stats['mes_demandes'] ?></div><div class="stat-change" style="color:var(--orange)"><?= $stats['dem_attente'] ?> en attente</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info"><div class="stat-label">Demandes acceptées</div><div class="stat-value"><?= $stats['dem_acceptees'] ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-file-contract"></i></div>
                <div class="stat-info"><div class="stat-label">Contrats actifs</div><div class="stat-value"><?= $stats['contrats_actifs'] ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-heart"></i></div>
                <div class="stat-info"><div class="stat-label">Favoris</div><div class="stat-value"><?= $stats['favoris'] ?></div></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
            <!-- Mes demandes -->
            <div class="table-card">
                <div class="table-header">
                    <span class="table-title">Mes dernières demandes</span>
                    <a href="<?= SITE_URL ?>/locataire/demandes.php" class="btn btn-gold btn-sm">Voir tout</a>
                </div>
                <?php if (empty($demandes)): ?>
                <div style="padding:32px;text-align:center;color:var(--text-muted)">
                    <i class="fas fa-home fa-2x" style="opacity:0.2;margin-bottom:10px;display:block"></i>
                    <p>Pas encore de demandes.</p>
                    <a href="<?= SITE_URL ?>/maisons.php" class="btn btn-gold btn-sm" style="margin-top:10px">Trouver une maison</a>
                </div>
                <?php else: ?>
                <div style="padding:12px">
                    <?php foreach ($demandes as $d): ?>
                    <div style="padding:12px;border-bottom:1px solid var(--cream-2);display:flex;justify-content:space-between;align-items:center">
                        <div>
                            <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($d['maison_titre']) ?></div>
                            <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($d['quartier'] ?? '') ?>, <?= htmlspecialchars($d['ville_nom']) ?> • <?= timeAgo($d['date_demande']) ?></div>
                        </div>
                        <span class="status-badge status-<?= $d['statut'] ?>" style="font-size:10px"><?= str_replace('_',' ',$d['statut']) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Notifications -->
            <div class="table-card">
                <div class="table-header">
                    <span class="table-title">🔔 Notifications</span>
                </div>
                <?php if (empty($notifs)): ?>
                <div style="padding:32px;text-align:center;color:var(--text-muted)">
                    <i class="fas fa-bell-slash fa-2x" style="opacity:0.2;margin-bottom:10px;display:block"></i>
                    <p>Aucune notification.</p>
                </div>
                <?php else: ?>
                <div style="padding:12px">
                    <?php foreach ($notifs as $n): ?>
                    <div style="padding:12px;border-bottom:1px solid var(--cream-2);display:flex;gap:12px;align-items:flex-start">
                        <div style="width:32px;height:32px;border-radius:50%;background:<?= $n['type']==='succes'?'#f0fdf4':($n['type']==='alerte'?'#fffbeb':'#eff6ff') ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fas fa-<?= $n['type']==='succes'?'check':'info' ?>-circle" style="color:<?= $n['type']==='succes'?'var(--green)':($n['type']==='alerte'?'var(--orange)':'var(--blue)') ?>;font-size:14px"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($n['titre']) ?></div>
                            <div style="font-size:12px;color:var(--text-light);"><?= htmlspecialchars($n['message']) ?></div>
                            <div style="font-size:11px;color:var(--text-muted);margin-top:3px"><?= timeAgo($n['date_creation']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Derniers paiements -->
        <?php if (!empty($paiements)): ?>
        <div class="table-card">
            <div class="table-header">
                <span class="table-title">Historique des paiements</span>
                <a href="<?= SITE_URL ?>/locataire/paiements.php" class="btn btn-gold btn-sm">Voir tout</a>
            </div>
            <table class="data-table">
                <thead><tr><th>Contrat</th><th>Montant</th><th>Méthode</th><th>Mois concerné</th><th>Statut</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($paiements as $p): ?>
                    <tr>
                        <td style="font-size:13px;font-weight:600"><?= htmlspecialchars($p['numero_contrat']) ?></td>
                        <td style="font-weight:700;color:var(--gold)"><?= formatPrix($p['montant']) ?></td>
                        <td style="font-size:13px"><?= ucfirst(str_replace('_',' ',$p['methode_paiement'])) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($p['mois_concerne'] ?? '-') ?></td>
                        <td><span class="status-badge status-<?= $p['statut'] ?>"><?= $p['statut'] ?></span></td>
                        <td style="font-size:12px;color:var(--text-muted)"><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- CTA chercher maison -->
        <?php if ($stats['contrats_actifs'] == 0): ?>
        <div style="background:linear-gradient(135deg,var(--dark),var(--dark-2));border-radius:20px;padding:40px;text-align:center;margin-top:24px">
            <h3 style="color:var(--white);font-family:'Playfair Display',serif;font-size:24px;margin-bottom:12px">Trouvez votre prochain logement</h3>
            <p style="color:rgba(255,255,255,0.6);margin-bottom:24px">Des centaines d'annonces disponibles à Cotonou et partout au Bénin</p>
            <a href="<?= SITE_URL ?>/maisons.php" class="btn btn-gold btn-lg"><i class="fas fa-search"></i> Explorer les annonces</a>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
