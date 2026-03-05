<?php
require_once '../includes/config.php';
requireLogin();
$db = getDB();
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contrat_id = (int)$_POST['contrat_id'];
    $montant = (float)$_POST['montant'];
    $methode = sanitize($_POST['methode']);
    $reference = sanitize($_POST['reference'] ?? '');
    $mois = sanitize($_POST['mois'] ?? '');
    if ($contrat_id && $montant) {
        $db->prepare("INSERT INTO paiements (contrat_id,locataire_id,montant,methode_paiement,reference_paiement,mois_concerne) VALUES (?,?,?,?,?,?)")->execute([$contrat_id,$uid,$montant,$methode,$reference,$mois]);
        flashMessage('succes', 'Paiement enregistré et en attente de validation.');
        redirect(SITE_URL . '/locataire/paiements.php');
    }
}

$paiements = $db->prepare("SELECT p.*, c.numero_contrat FROM paiements p JOIN contrats c ON p.contrat_id=c.id WHERE p.locataire_id=? ORDER BY p.date_paiement DESC");
$paiements->execute([$uid]);
$paiements = $paiements->fetchAll();
$contrats = $db->prepare("SELECT * FROM contrats WHERE locataire_id=? AND statut='actif'");
$contrats->execute([$uid]);
$contrats = $contrats->fetchAll();

$page_title = 'Mes Paiements';
include 'includes/sidebar_locataire.php';
?>
<div class="dashboard-main">
    <div class="dashboard-topbar"><div class="topbar-title">Mes Paiements</div></div>
    <div class="dashboard-content">
        <?php $flash = getFlash(); if ($flash): ?><div style="background:#f0fdf4;border-left:4px solid var(--green);padding:14px;border-radius:10px;margin-bottom:20px;color:#166534;display:flex;gap:10px"><i class="fas fa-check-circle"></i> <?= $flash['message'] ?></div><?php endif; ?>
        
        <?php if (!empty($contrats)): ?>
        <div style="background:var(--white);border-radius:16px;padding:28px;margin-bottom:24px;border:1px solid #F1F5F9">
            <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:20px">Enregistrer un paiement</h3>
            <form method="POST" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px">
                <div class="form-group"><label>Contrat *</label><select name="contrat_id" class="form-control" required><option value="">Choisir...</option><?php foreach ($contrats as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['numero_contrat']) ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Montant (FCFA) *</label><input type="number" name="montant" class="form-control" min="1" required></div>
                <div class="form-group"><label>Méthode *</label><select name="methode" class="form-control"><option value="mobile_money">Mobile Money</option><option value="especes">Espèces</option><option value="virement">Virement</option><option value="cheque">Chèque</option></select></div>
                <div class="form-group"><label>Mois concerné</label><input type="text" name="mois" class="form-control" placeholder="Ex: Janvier 2025"></div>
                <div class="form-group"><label>Référence transaction</label><input type="text" name="reference" class="form-control" placeholder="N° transaction"></div>
                <div class="form-group" style="display:flex;align-items:flex-end"><button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;border-radius:10px"><i class="fas fa-plus"></i> Enregistrer</button></div>
            </form>
        </div>
        <?php endif; ?>

        <div class="table-card">
            <div class="table-header"><span class="table-title">Historique des paiements</span></div>
            <table class="data-table">
                <thead><tr><th>Contrat</th><th>Montant</th><th>Méthode</th><th>Mois</th><th>Référence</th><th>Statut</th><th>Date</th></tr></thead>
                <tbody>
                    <?php if (empty($paiements)): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">Aucun paiement</td></tr>
                    <?php else: ?>
                    <?php foreach ($paiements as $p): ?>
                    <tr>
                        <td style="font-weight:600;color:var(--gold);font-size:13px"><?= htmlspecialchars($p['numero_contrat']) ?></td>
                        <td style="font-weight:700"><?= formatPrix($p['montant']) ?></td>
                        <td style="font-size:13px"><?= ucfirst(str_replace('_',' ',$p['methode_paiement'])) ?></td>
                        <td style="font-size:13px"><?= htmlspecialchars($p['mois_concerne'] ?? '-') ?></td>
                        <td style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($p['reference_paiement'] ?? '-') ?></td>
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
