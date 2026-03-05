<?php
require_once '../includes/config.php';
requireAgent();
$db = getDB();
$uid = $_SESSION['user_id'];

$contrats = $db->prepare("
    SELECT c.*, m.titre as maison_titre, v.nom as ville_nom,
           u.prenom as loc_prenom, u.nom as loc_nom, u.telephone as loc_tel
    FROM contrats c JOIN maisons m ON c.maison_id=m.id JOIN villes v ON m.ville_id=v.id
    JOIN users u ON c.locataire_id=u.id
    WHERE c.agent_id=? ORDER BY c.date_creation DESC
");
$contrats->execute([$uid]);
$contrats = $contrats->fetchAll();

$page_title = 'Mes Contrats';
include 'includes/sidebar_agent.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar"><div class="topbar-title">Mes Contrats (<?= count($contrats) ?>)</div></div>
    <div class="dashboard-content">
        <div class="table-card">
            <table class="data-table">
                <thead><tr><th>N° Contrat</th><th>Maison</th><th>Locataire</th><th>Loyer/mois</th><th>Début</th><th>Fin</th><th>Statut</th></tr></thead>
                <tbody>
                    <?php if (empty($contrats)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Aucun contrat</td></tr>
                    <?php else: ?>
                    <?php foreach ($contrats as $c): ?>
                    <tr>
                        <td style="font-weight:700;color:var(--gold);font-size:13px"><?= htmlspecialchars($c['numero_contrat']) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($c['maison_titre']) ?></td>
                        <td>
                            <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($c['loc_prenom'].' '.$c['loc_nom']) ?></div>
                            <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($c['loc_tel']) ?></div>
                        </td>
                        <td style="font-weight:700;color:var(--gold)"><?= formatPrix($c['loyer_mensuel']) ?></td>
                        <td style="font-size:13px"><?= date('d/m/Y', strtotime($c['date_debut'])) ?></td>
                        <td style="font-size:13px"><?= date('d/m/Y', strtotime($c['date_fin'])) ?></td>
                        <td><span class="status-badge status-<?= $c['statut'] ?>"><?= $c['statut'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<script>const SITE_URL = '<?= SITE_URL ?>';</script>
<script src="<?= SITE_URL ?>/js/main.js"></script>
</body></html>
