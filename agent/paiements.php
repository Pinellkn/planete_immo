<?php
require_once '../includes/config.php';
requireAgent();
$db = getDB();
$uid = $_SESSION['user_id'];

$paiements = $db->prepare("
    SELECT p.*, c.numero_contrat, u.prenom as loc_prenom, u.nom as loc_nom
    FROM paiements p JOIN contrats c ON p.contrat_id=c.id JOIN users u ON p.locataire_id=u.id
    WHERE c.agent_id=? ORDER BY p.date_paiement DESC
");
$paiements->execute([$uid]);
$paiements = $paiements->fetchAll();

$page_title = 'Paiements';
include 'includes/sidebar_agent.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar"><div class="topbar-title">Paiements (<?= count($paiements) ?>)</div></div>
    <div class="dashboard-content">
        <div class="table-card">
            <table class="data-table">
                <thead><tr><th>Locataire</th><th>Contrat</th><th>Montant</th><th>Méthode</th><th>Statut</th><th>Date</th></tr></thead>
                <tbody>
                    <?php if (empty($paiements)): ?>
                    <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted)">Aucun paiement</td></tr>
                    <?php else: ?>
                    <?php foreach ($paiements as $p): ?>
                    <tr>
                        <td style="font-weight:600;font-size:13px"><?= htmlspecialchars($p['loc_prenom'].' '.$p['loc_nom']) ?></td>
                        <td style="font-size:12px;color:var(--gold);font-weight:600"><?= htmlspecialchars($p['numero_contrat']) ?></td>
                        <td style="font-weight:700;color:var(--dark)"><?= formatPrix($p['montant']) ?></td>
                        <td style="font-size:13px"><?= ucfirst(str_replace('_',' ',$p['methode_paiement'])) ?></td>
                        <td><span class="status-badge status-<?= $p['statut'] ?>"><?= $p['statut'] ?></span></td>
                        <td style="font-size:12px;color:var(--text-muted)"><?= date('d/m/Y',strtotime($p['date_paiement'])) ?></td>
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
