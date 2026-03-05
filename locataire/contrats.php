<?php
require_once '../includes/config.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];
$contrats = $db->prepare("SELECT c.*, m.titre as maison_titre, v.nom as ville_nom, u.prenom as agent_prenom, u.nom as agent_nom, u.telephone as agent_tel FROM contrats c JOIN maisons m ON c.maison_id=m.id JOIN villes v ON m.ville_id=v.id JOIN users u ON c.agent_id=u.id WHERE c.locataire_id=? ORDER BY c.date_creation DESC");
$contrats->execute([$uid]);
$contrats = $contrats->fetchAll();
$page_title = 'Mes Contrats';
include 'includes/sidebar_locataire.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar"><div class="topbar-title">Mes Contrats (<?= count($contrats) ?>)</div></div>
    <div class="dashboard-content">
        <?php if (empty($contrats)): ?>
        <div style="text-align:center;padding:80px;background:var(--white);border-radius:20px;color:var(--text-muted)"><i class="fas fa-file-contract fa-3x" style="opacity:0.2;display:block;margin-bottom:16px"></i><p>Aucun contrat pour le moment.</p></div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:16px">
            <?php foreach ($contrats as $c): ?>
            <div style="background:var(--white);border-radius:16px;padding:24px;border:1px solid #F1F5F9;box-shadow:var(--shadow-sm)">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px">
                    <div>
                        <div style="font-size:18px;font-weight:700;color:var(--gold);font-family:'Playfair Display',serif;margin-bottom:6px"><?= htmlspecialchars($c['numero_contrat']) ?></div>
                        <div style="font-size:15px;font-weight:600;margin-bottom:8px"><?= htmlspecialchars($c['maison_titre']) ?></div>
                        <div style="display:flex;gap:20px;font-size:13px;color:var(--text-light);flex-wrap:wrap">
                            <span><i class="fas fa-map-marker-alt" style="color:var(--gold)"></i> <?= htmlspecialchars($c['ville_nom']) ?></span>
                            <span><i class="fas fa-calendar" style="color:var(--gold)"></i> Du <?= date('d/m/Y',strtotime($c['date_debut'])) ?> au <?= date('d/m/Y',strtotime($c['date_fin'])) ?></span>
                            <span><i class="fas fa-coins" style="color:var(--gold)"></i> <?= formatPrix($c['loyer_mensuel']) ?>/mois</span>
                            <span><i class="fas fa-user-tie" style="color:var(--gold)"></i> <?= htmlspecialchars($c['agent_prenom'].' '.$c['agent_nom']) ?> — <?= htmlspecialchars($c['agent_tel']) ?></span>
                        </div>
                    </div>
                    <span class="status-badge status-<?= $c['statut'] ?>"><?= $c['statut'] ?></span>
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
