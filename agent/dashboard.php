<?php
require_once '../includes/config.php';
requireAgent();
$db = getDB();
$uid = $_SESSION['user_id'];

$stats = $db->prepare("
    SELECT
        (SELECT COUNT(*) FROM maisons WHERE agent_id=?) as mes_annonces,
        (SELECT COUNT(*) FROM maisons WHERE agent_id=? AND disponibilite='disponible' AND statut='actif') as dispo,
        (SELECT COUNT(*) FROM maisons WHERE agent_id=? AND disponibilite='louee') as louees,
        (SELECT COUNT(*) FROM demandes_location WHERE agent_id=? AND statut='en_attente') as dem_attente,
        (SELECT COUNT(*) FROM contrats WHERE agent_id=? AND statut='actif') as contrats_actifs,
        (SELECT COALESCE(SUM(p.montant),0) FROM paiements p JOIN contrats c ON p.contrat_id=c.id WHERE c.agent_id=? AND p.statut='valide' AND MONTH(p.date_paiement)=MONTH(NOW())) as revenus_mois
");
$stats->execute([$uid,$uid,$uid,$uid,$uid,$uid]);
$stats = $stats->fetch();

$mes_maisons = $db->prepare("SELECT m.*, v.nom as ville_nom FROM maisons m JOIN villes v ON m.ville_id=v.id WHERE m.agent_id=? ORDER BY m.date_creation DESC LIMIT 5");
$mes_maisons->execute([$uid]);
$mes_maisons = $mes_maisons->fetchAll();

$demandes = $db->prepare("
    SELECT d.*, m.titre as maison_titre, u.prenom as loc_prenom, u.nom as loc_nom, u.telephone as loc_tel
    FROM demandes_location d JOIN maisons m ON d.maison_id=m.id JOIN users u ON d.locataire_id=u.id
    WHERE d.agent_id=? AND d.statut='en_attente' ORDER BY d.date_demande DESC LIMIT 5
");
$demandes->execute([$uid]);
$demandes = $demandes->fetchAll();

$page_title = 'Espace Agent';
include 'includes/sidebar_agent.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar">
        <div>
            <div class="topbar-title">Bonjour, <?= htmlspecialchars($_SESSION['prenom']) ?> 👋</div>
            <div style="font-size:12px;color:var(--text-muted)"><?= date('d F Y') ?></div>
        </div>
        <div class="topbar-actions">
            <a href="<?= SITE_URL ?>/agent/ajouter_maison.php" class="btn btn-gold btn-sm"><i class="fas fa-plus"></i> Nouvelle annonce</a>
        </div>
    </div>

    <div class="dashboard-content">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon gold"><i class="fas fa-home"></i></div>
                <div class="stat-info"><div class="stat-label">Mes annonces</div><div class="stat-value"><?= $stats['mes_annonces'] ?></div><div class="stat-change up"><?= $stats['dispo'] ?> disponibles</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red"><i class="fas fa-key"></i></div>
                <div class="stat-info"><div class="stat-label">Propriétés louées</div><div class="stat-value"><?= $stats['louees'] ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                <div class="stat-info"><div class="stat-label">Demandes en attente</div><div class="stat-value"><?= $stats['dem_attente'] ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-file-contract"></i></div>
                <div class="stat-info"><div class="stat-label">Contrats actifs</div><div class="stat-value"><?= $stats['contrats_actifs'] ?></div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-coins"></i></div>
                <div class="stat-info"><div class="stat-label">Revenus ce mois</div><div class="stat-value" style="font-size:18px"><?= number_format($stats['revenus_mois'],0,',',' ') ?></div><div class="stat-change" style="color:var(--text-muted)">FCFA</div></div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
            <!-- Demandes urgentes -->
            <div class="table-card">
                <div class="table-header">
                    <span class="table-title">⚡ Demandes à traiter</span>
                    <a href="<?= SITE_URL ?>/agent/demandes.php" class="btn btn-gold btn-sm">Voir tout</a>
                </div>
                <?php if (empty($demandes)): ?>
                <div style="padding:32px;text-align:center;color:var(--text-muted)"><i class="fas fa-check-circle fa-2x" style="color:var(--green);margin-bottom:10px;display:block"></i>Aucune demande en attente</div>
                <?php else: ?>
                <div style="padding:12px">
                    <?php foreach ($demandes as $d): ?>
                    <div style="border:1px solid #E5E7EB;border-radius:12px;padding:14px;margin-bottom:10px;background:var(--cream)">
                        <div style="font-weight:600;font-size:14px;margin-bottom:4px"><?= htmlspecialchars($d['loc_prenom'] . ' ' . $d['loc_nom']) ?></div>
                        <div style="font-size:12px;color:var(--text-muted);margin-bottom:8px">Souhaite louer : <strong><?= htmlspecialchars($d['maison_titre']) ?></strong></div>
                        <div style="display:flex;gap:8px">
                            <a href="<?= SITE_URL ?>/agent/demandes.php?action=accepter&id=<?= $d['id'] ?>" class="btn btn-sm" style="background:#f0fdf4;color:var(--green);padding:5px 12px">✓ Accepter</a>
                            <a href="<?= SITE_URL ?>/agent/demandes.php?action=refuser&id=<?= $d['id'] ?>" class="btn btn-sm" style="background:#fef2f2;color:var(--red);padding:5px 12px">✗ Refuser</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Mes annonces -->
            <div class="table-card">
                <div class="table-header">
                    <span class="table-title">Mes dernières annonces</span>
                    <a href="<?= SITE_URL ?>/agent/mes_maisons.php" class="btn btn-gold btn-sm">Voir tout</a>
                </div>
                <table class="data-table">
                    <thead><tr><th>Titre</th><th>Prix</th><th>Statut</th></tr></thead>
                    <tbody>
                        <?php foreach ($mes_maisons as $ma): ?>
                        <tr>
                            <td style="font-size:13px;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($ma['titre']) ?></td>
                            <td style="font-size:13px;color:var(--gold);font-weight:600"><?= number_format($ma['prix_mensuel'],0,',',' ') ?></td>
                            <td><span class="status-badge status-<?= $ma['disponibilite'] ?>"><?= $ma['disponibilite'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($mes_maisons)): ?>
                        <tr><td colspan="3" style="text-align:center;padding:20px;color:var(--text-muted)">Aucune annonce</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
