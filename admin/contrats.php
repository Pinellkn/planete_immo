<?php
require_once '../includes/config.php';
requireAdmin();
$db = getDB();

$contrats = $db->query("
    SELECT c.*, m.titre as maison_titre, v.nom as ville_nom,
           u1.prenom as loc_prenom, u1.nom as loc_nom,
           u2.prenom as agent_prenom, u2.nom as agent_nom
    FROM contrats c JOIN maisons m ON c.maison_id=m.id JOIN villes v ON m.ville_id=v.id
    JOIN users u1 ON c.locataire_id=u1.id JOIN users u2 ON c.agent_id=u2.id
    ORDER BY c.date_creation DESC
")->fetchAll();

$page_title = 'Gestion des Contrats';
include 'includes/sidebar_admin.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar"><div class="topbar-title">Contrats de Location (<?= count($contrats) ?>)</div></div>
    <div class="dashboard-content">
        <div class="table-card">
            <table class="data-table">
                <thead><tr><th>N° Contrat</th><th>Maison</th><th>Locataire</th><th>Agent</th><th>Loyer/mois</th><th>Début</th><th>Fin</th><th>Statut</th></tr></thead>
                <tbody>
                    <?php if (empty($contrats)): ?>
                    <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--text-muted)">Aucun contrat</td></tr>
                    <?php else: ?>
                    <?php foreach ($contrats as $c): ?>
                    <tr>
                        <td style="font-weight:700;color:var(--gold);font-size:13px"><?= htmlspecialchars($c['numero_contrat']) ?></td>
                        <td style="font-size:13px;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($c['maison_titre']) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($c['loc_prenom'].' '.$c['loc_nom']) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($c['agent_prenom'].' '.$c['agent_nom']) ?></td>
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
